<?php

namespace App\Controllers\Canvassing;

use App\Controllers\BaseController;
use App\Models\ManagerCustomerModel;
use App\Models\ManagerActivityLogModel;
use App\Models\LicenseModel;
use App\Models\OrderModel;
use App\Models\PlanModel;
use App\Libraries\Payment\PaymentService;
use App\Libraries\DataTableHandler;

class CustomerLicenseController extends BaseController
{
    protected ManagerCustomerModel $mcModel;
    protected ManagerActivityLogModel $logModel;
    protected LicenseModel $licenseModel;

    public function __construct()
    {
        $this->mcModel      = new ManagerCustomerModel();
        $this->logModel     = new ManagerActivityLogModel();
        $this->licenseModel = new LicenseModel();
    }

    public function index()
    {
        $data = [
            'title'      => 'Lisensi Customer',
            'page_title' => 'Lisensi Customer Saya',
        ];

        return $this->renderView('canvassing/licenses/index', $data);
    }

    public function ajax()
    {
        $managerId   = auth()->id();
        $customerIds = $this->mcModel->getCustomerIds($managerId);

        if (empty($customerIds)) {
            return $this->response->setJSON([
                'draw' => (int) $this->request->getGet('draw'),
                'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [],
            ]);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('licenses')
            ->select('licenses.*, licenses.uuid, plans.name as plan_name, users.username, orders.order_number')
            ->join('plans', 'plans.id = licenses.plan_id', 'left')
            ->join('users', 'users.id = licenses.user_id', 'left')
            ->join('orders', 'orders.id = licenses.order_id', 'left')
            ->whereIn('licenses.user_id', $customerIds);

        $status = $this->request->getGet('status');
        if (! empty($status)) {
            $builder->where('licenses.status', $status);
        }

        $countBuilder = clone $builder;

        $handler = new DataTableHandler($this->request);
        $result = $handler->setBuilder($builder)
            ->setCountBuilder($countBuilder)
            ->setColumnMap([
                0 => 'licenses.id',
                1 => 'users.username',
                2 => 'licenses.license_key',
                3 => 'plans.name',
                4 => 'licenses.status',
                5 => 'licenses.expires_at',
                6 => '', // actions
            ])
            ->process();

        return $this->response->setJSON($result);
    }

    public function detail(string $uuid)
    {
        $managerId   = auth()->id();
        $customerIds = $this->mcModel->getCustomerIds($managerId);

        $license = $this->licenseModel
            ->select('licenses.*, plans.name as plan_name, plans.duration_days, users.username, orders.order_number')
            ->join('plans', 'plans.id = licenses.plan_id', 'left')
            ->join('users', 'users.id = licenses.user_id', 'left')
            ->join('orders', 'orders.id = licenses.order_id', 'left')
            ->where('licenses.uuid', $uuid)
            ->first();

        if (! $license || ! in_array($license->user_id, $customerIds)) {
            return redirect()->to('/canvassing/customer-licenses')->with('error', 'Lisensi tidak ditemukan.');
        }

        $data = [
            'title'      => 'Detail Lisensi',
            'page_title' => 'Detail Lisensi Customer',
            'license'    => $license,
        ];

        return $this->renderView('canvassing/licenses/detail', $data);
    }

    public function history(string $uuid)
    {
        $managerId   = auth()->id();
        $customerIds = $this->mcModel->getCustomerIds($managerId);

        $license = $this->licenseModel
            ->select('licenses.*, plans.name as plan_name, users.username')
            ->join('plans', 'plans.id = licenses.plan_id', 'left')
            ->join('users', 'users.id = licenses.user_id', 'left')
            ->where('licenses.uuid', $uuid)
            ->first();

        if (! $license || ! in_array($license->user_id, $customerIds)) {
            return redirect()->to('/canvassing/customer-licenses')->with('error', 'Lisensi tidak ditemukan.');
        }

        $db = \Config\Database::connect();

        // Get all orders related to this license
        $orders = $db->table('orders')
            ->select('orders.*, plans.name as plan_name')
            ->join('plans', 'plans.id = orders.plan_id', 'left')
            ->groupStart()
                ->where('orders.id', $license->order_id)
                ->orWhere('orders.license_id', $license->id)
            ->groupEnd()
            ->orderBy('orders.created_at', 'DESC')
            ->get()->getResult();

        // Get payment confirmations for those orders
        $orderIds = array_map(fn($o) => $o->id, $orders);
        $payments = [];
        if (! empty($orderIds)) {
            $payments = $db->table('payment_confirmations')
                ->whereIn('order_id', $orderIds)
                ->orderBy('created_at', 'DESC')
                ->get()->getResult();
        }

        // Get activity logs related to this license
        $activities = $db->table('manager_activity_logs')
            ->where('customer_id', $license->user_id)
            ->where('reference_type', 'order')
            ->whereIn('reference_id', ! empty($orderIds) ? $orderIds : [0])
            ->orderBy('created_at', 'DESC')
            ->get()->getResult();

        $data = [
            'title'      => 'History Transaksi Lisensi',
            'page_title' => 'History Transaksi Lisensi',
            'license'    => $license,
            'orders'     => $orders,
            'payments'   => $payments,
            'activities' => $activities,
        ];

        return $this->renderView('canvassing/licenses/history', $data);
    }

    public function renew(string $uuid)
    {
        $managerId   = auth()->id();
        $customerIds = $this->mcModel->getCustomerIds($managerId);

        $license = $this->licenseModel
            ->select('licenses.*, plans.name as plan_name, plans.duration_days')
            ->join('plans', 'plans.id = licenses.plan_id', 'left')
            ->where('licenses.uuid', $uuid)
            ->first();

        if (! $license || ! in_array($license->user_id, $customerIds)) {
            return redirect()->to('/canvassing/customer-licenses')->with('error', 'Lisensi tidak ditemukan.');
        }

        if (in_array($license->status, ['revoked', 'suspended'])) {
            return redirect()->to('/canvassing/customer-licenses')->with('error', 'Lisensi yang dicabut/ditangguhkan tidak dapat diperpanjang.');
        }

        if ($license->is_trial) {
            return redirect()->to('/canvassing/customer-licenses')->with('error', 'Lisensi trial tidak dapat diperpanjang.');
        }

        // Check existing pending renewal
        $orderModel = new OrderModel();
        $existingRenewal = $orderModel
            ->where('license_id', $license->id)
            ->where('type', 'renewal')
            ->whereIn('status', ['pending', 'awaiting_confirmation'])
            ->first();

        if ($existingRenewal) {
            return redirect()->to('/canvassing/customer-orders/view/' . $existingRenewal->order_number)
                ->with('info', 'Sudah ada order perpanjangan yang sedang diproses.');
        }

        $planModel = new PlanModel();

        $data = [
            'title'      => 'Perpanjang Lisensi Customer',
            'page_title' => 'Perpanjang Lisensi Customer',
            'license'    => $license,
            'plans'      => $planModel->getActivePlans(),
        ];

        return $this->renderView('canvassing/licenses/renew', $data);
    }

    public function storeRenewal(string $uuid)
    {
        $managerId   = auth()->id();
        $customerIds = $this->mcModel->getCustomerIds($managerId);

        $license = $this->licenseModel->findByUuid($uuid);

        if (! $license || ! in_array($license->user_id, $customerIds)) {
            return redirect()->to('/canvassing/customer-licenses')->with('error', 'Lisensi tidak ditemukan.');
        }

        if (in_array($license->status, ['revoked', 'suspended']) || $license->is_trial) {
            return redirect()->to('/canvassing/customer-licenses')->with('error', 'Lisensi ini tidak dapat diperpanjang.');
        }

        $rules = ['plan_id' => 'required|integer'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $planId = (int) $this->request->getPost('plan_id');
        $notes  = $this->request->getPost('notes');

        $paymentService = new PaymentService();
        $result = $paymentService->createOrder(
            (int) $license->user_id,
            $planId,
            'manual',
            $notes,
            'renewal',
            (int) $license->id
        );

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        // Mark as created by manager
        $orderModel = new OrderModel();
        $orderModel->update($result['data']['order_id'], [
            'created_by_manager_id' => $managerId,
        ]);

        // Log activity
        $this->logModel->logAction(
            $managerId, (int) $license->user_id, 'manage_license',
            $result['data']['order_id'], 'order',
            'Membuat order perpanjangan lisensi ' . $license->license_key
        );

        return redirect()->to('/canvassing/customer-orders/view/' . $result['data']['order_number'])
            ->with('success', 'Order perpanjangan berhasil dibuat.');
    }
}
