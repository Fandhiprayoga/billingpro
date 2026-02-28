<?php

namespace App\Controllers;

use App\Models\LicenseModel;
use App\Models\OrderModel;
use App\Models\PlanModel;
use App\Libraries\DataTableHandler;
use App\Libraries\Payment\PaymentService;

/**
 * UserLicenseController
 *
 * Controller khusus untuk user biasa (non-admin).
 * User hanya bisa melihat lisensi miliknya sendiri.
 */
class UserLicenseController extends BaseController
{
    protected LicenseModel $licenseModel;

    public function __construct()
    {
        $this->licenseModel = new LicenseModel();
    }

    /**
     * Daftar lisensi milik user yang login.
     */
    public function index()
    {
        $data = [
            'title'      => 'Lisensi Saya',
            'page_title' => 'Lisensi Saya',
        ];

        return $this->renderView('user_billing/licenses', $data);
    }

    /**
     * AJAX DataTables endpoint untuk lisensi milik user.
     */
    public function ajax()
    {
        $userId = auth()->id();
        $db = \Config\Database::connect();

        $builder = $db->table('licenses')
            ->select('licenses.*, licenses.uuid, plans.name as plan_name, orders.order_number')
            ->join('plans', 'plans.id = licenses.plan_id', 'left')
            ->join('orders', 'orders.id = licenses.order_id', 'left')
            ->where('licenses.user_id', $userId);

        // Filter: status
        $status = $this->request->getGet('status');
        if (!empty($status)) {
            $builder->where('licenses.status', $status);
        }

        $countBuilder = clone $builder;

        $handler = new DataTableHandler($this->request);
        $result = $handler->setBuilder($builder)
            ->setCountBuilder($countBuilder)
            ->setColumnMap([
                0 => 'licenses.id',
                1 => 'licenses.license_key',
                2 => 'plans.name',
                3 => 'orders.order_number',
                4 => 'licenses.device_id',
                5 => 'licenses.status',
                6 => 'licenses.expires_at',
                7 => '', // actions
            ])
            ->process();

        return $this->response->setJSON($result);
    }

    /**
     * Detail lisensi (hanya milik sendiri).
     */
    public function view(string $uuid)
    {
        $license = $this->licenseModel
            ->select('licenses.*, plans.name as plan_name, plans.duration_days, orders.order_number')
            ->join('plans', 'plans.id = licenses.plan_id', 'left')
            ->join('orders', 'orders.id = licenses.order_id', 'left')
            ->where('licenses.uuid', $uuid)
            ->first();

        if (! $license || $license->user_id != auth()->id()) {
            return redirect()->to('/my-licenses')->with('error', 'Lisensi tidak ditemukan.');
        }

        $data = [
            'title'      => 'Detail Lisensi',
            'page_title' => 'Detail Lisensi',
            'license'    => $license,
        ];

        return $this->renderView('user_billing/license_view', $data);
    }

    /**
     * GET /my-licenses/renew/{uuid}
     * Form perpanjangan / topup masa aktif lisensi.
     */
    public function renew(string $uuid)
    {
        $license = $this->licenseModel
            ->select('licenses.*, plans.name as plan_name, plans.duration_days')
            ->join('plans', 'plans.id = licenses.plan_id', 'left')
            ->where('licenses.uuid', $uuid)
            ->first();

        if (! $license || $license->user_id != auth()->id()) {
            return redirect()->to('/my-licenses')->with('error', 'Lisensi tidak ditemukan.');
        }

        if (in_array($license->status, ['revoked', 'suspended'])) {
            return redirect()->to('/my-licenses')->with('error', 'Lisensi yang dicabut atau ditangguhkan tidak dapat diperpanjang.');
        }

        if ($license->is_trial) {
            return redirect()->to('/my-licenses')->with('error', 'Lisensi trial tidak dapat diperpanjang. Silakan beli lisensi baru.');
        }

        // Check for existing pending renewal order
        $orderModel = new OrderModel();
        $existingRenewal = $orderModel
            ->where('license_id', $license->id)
            ->where('type', 'renewal')
            ->whereIn('status', ['pending', 'awaiting_confirmation'])
            ->first();

        if ($existingRenewal) {
            return redirect()->to('/my-orders/view/' . $existingRenewal->order_number)
                ->with('info', 'Sudah ada order perpanjangan yang sedang diproses.');
        }

        $planModel = new PlanModel();

        $data = [
            'title'      => 'Perpanjang Lisensi',
            'page_title' => 'Perpanjang / Topup Lisensi',
            'license'    => $license,
            'plans'      => $planModel->getActivePlans(),
        ];

        return $this->renderView('user_billing/license_renew', $data);
    }

    /**
     * POST /my-licenses/store-renewal/{uuid}
     * Proses pembuatan order perpanjangan.
     */
    public function storeRenewal(string $uuid)
    {
        $license = $this->licenseModel->findByUuid($uuid);

        if (! $license || $license->user_id != auth()->id()) {
            return redirect()->to('/my-licenses')->with('error', 'Lisensi tidak ditemukan.');
        }

        if (in_array($license->status, ['revoked', 'suspended']) || $license->is_trial) {
            return redirect()->to('/my-licenses')->with('error', 'Lisensi ini tidak dapat diperpanjang.');
        }

        $rules = ['plan_id' => 'required|integer'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $planId = (int) $this->request->getPost('plan_id');
        $notes  = $this->request->getPost('notes');

        $paymentService = new PaymentService();
        $result = $paymentService->createOrder(
            auth()->id(),
            $planId,
            'manual',
            $notes,
            'renewal',
            $license->id
        );

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->to('/my-orders/view/' . $result['data']['order_number'])
            ->with('success', 'Order perpanjangan berhasil dibuat. Silakan lakukan pembayaran.');
    }

    /**
     * GET /my-licenses/history/{uuid}
     * Riwayat pembayaran terkait lisensi ini.
     */
    public function history(string $uuid)
    {
        $license = $this->licenseModel
            ->select('licenses.*, plans.name as plan_name')
            ->join('plans', 'plans.id = licenses.plan_id', 'left')
            ->where('licenses.uuid', $uuid)
            ->first();

        if (! $license || $license->user_id != auth()->id()) {
            return redirect()->to('/my-licenses')->with('error', 'Lisensi tidak ditemukan.');
        }

        $orderModel = new OrderModel();
        $builder = $orderModel
            ->select('orders.*, plans.name as plan_name, plans.duration_days')
            ->join('plans', 'plans.id = orders.plan_id', 'left');

        if ($license->order_id) {
            $builder->groupStart()
                ->where('orders.id', $license->order_id)
                ->orWhere('orders.license_id', $license->id)
            ->groupEnd();
        } else {
            $builder->where('orders.license_id', $license->id);
        }

        $orders = $builder->orderBy('orders.created_at', 'DESC')->findAll();

        $data = [
            'title'      => 'Riwayat Pembayaran',
            'page_title' => 'Riwayat Pembayaran Lisensi',
            'license'    => $license,
            'orders'     => $orders,
        ];

        return $this->renderView('user_billing/license_history', $data);
    }
}
