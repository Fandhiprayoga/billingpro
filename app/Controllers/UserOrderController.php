<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\PlanModel;
use App\Models\PaymentConfirmationModel;
use App\Models\LicenseModel;
use App\Libraries\Payment\PaymentService;

use App\Libraries\DataTableHandler;

/**
 * UserOrderController
 *
 * Controller khusus untuk user biasa (non-admin).
 * User hanya bisa melihat & mengelola order miliknya sendiri.
 */
class UserOrderController extends BaseController
{
    protected OrderModel $orderModel;
    protected PlanModel $planModel;
    protected PaymentService $paymentService;

    public function __construct()
    {
        $this->orderModel     = new OrderModel();
        $this->planModel      = new PlanModel();
        $this->paymentService = new PaymentService();
    }

    /**
     * Halaman daftar paket untuk user.
     */
    public function plans()
    {
        $data = [
            'title'      => 'Pilih Paket',
            'page_title' => 'Paket Lisensi Tersedia',
            'plans'      => $this->planModel->getActivePlans(),
        ];

        return $this->renderView('user_billing/plans', $data);
    }

    /**
     * Daftar order milik user yang login.
     */
    public function index()
    {
        $data = [
            'title'      => 'Order Saya',
            'page_title' => 'Order Saya',
        ];

        return $this->renderView('user_billing/orders', $data);
    }

    /**
     * AJAX DataTables endpoint untuk orders milik user.
     */
    public function ajax()
    {
        $userId = auth()->id();
        $db = \Config\Database::connect();

        $builder = $db->table('orders')
            ->select('orders.*, plans.name as plan_name')
            ->join('plans', 'plans.id = orders.plan_id', 'left')
            ->where('orders.user_id', $userId);

        // Filter: status
        $status = $this->request->getGet('status');
        if (!empty($status)) {
            if (str_contains($status, ',')) {
                $builder->whereIn('orders.status', explode(',', $status));
            } else {
                $builder->where('orders.status', $status);
            }
        }

        $countBuilder = clone $builder;

        $handler = new DataTableHandler($this->request);
        $result = $handler->setBuilder($builder)
            ->setCountBuilder($countBuilder)
            ->setColumnMap([
                0 => 'orders.id',
                1 => 'orders.order_number',
                2 => 'plans.name',
                3 => 'orders.amount',
                4 => 'orders.status',
                5 => 'orders.created_at',
                6 => '', // actions
            ])
            ->process();

        return $this->response->setJSON($result);
    }

    /**
     * Form buat order baru.
     */
    public function create()
    {
        $data = [
            'title'      => 'Buat Order',
            'page_title' => 'Buat Order Baru',
            'plans'      => $this->planModel->getActivePlans(),
        ];

        return $this->renderView('user_billing/order_create', $data);
    }

    /**
     * Simpan order baru.
     */
    public function store()
    {
        $rules = [
            'plan_id' => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId = auth()->id();
        $planId = (int) $this->request->getPost('plan_id');
        $notes  = $this->request->getPost('notes');

        $result = $this->paymentService->createOrder($userId, $planId, 'manual', $notes);

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->to('/my-orders')->with('success', $result['message'] . ' Nomor Order: ' . $result['data']['order_number']);
    }

    /**
     * Detail order (hanya milik sendiri).
     */
    public function view(string $orderNumber)
    {
        $order = $this->orderModel->getOrderWithDetailsByNumber($orderNumber);

        if (! $order || $order->user_id != auth()->id()) {
            return redirect()->to('/my-orders')->with('error', 'Order tidak ditemukan.');
        }

        $confirmationModel = new PaymentConfirmationModel();
        $confirmations = $confirmationModel
            ->where('order_id', $order->id)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $licenseModel = new LicenseModel();
        $license = $licenseModel->where('order_id', $order->id)->first();

        $data = [
            'title'         => 'Detail Order',
            'page_title'    => 'Detail Order #' . $order->order_number,
            'order'         => $order,
            'confirmations' => $confirmations,
            'license'       => $license,
        ];

        return $this->renderView('user_billing/order_view', $data);
    }

    /**
     * Form upload bukti bayar (hanya milik sendiri).
     */
    public function uploadConfirmation(string $orderNumber)
    {
        $order = $this->orderModel->findByOrderNumber($orderNumber);

        if (! $order || $order->user_id != auth()->id()) {
            return redirect()->to('/my-orders')->with('error', 'Order tidak ditemukan.');
        }

        if (! in_array($order->status, ['pending'])) {
            return redirect()->to('/my-orders/view/' . $orderNumber)->with('error', 'Order tidak dalam status yang bisa diupload bukti bayar.');
        }

        $data = [
            'title'      => 'Upload Bukti Bayar',
            'page_title' => 'Upload Bukti Pembayaran',
            'order'      => $order,
        ];

        return $this->renderView('user_billing/upload_confirmation', $data);
    }

    /**
     * Submit bukti bayar (hanya milik sendiri).
     */
    public function submitConfirmation(string $orderNumber)
    {
        $order = $this->orderModel->findByOrderNumber($orderNumber);

        if (! $order || $order->user_id != auth()->id()) {
            return redirect()->to('/my-orders')->with('error', 'Order tidak ditemukan.');
        }

        $rules = [
            'bank_name'       => 'required|max_length[100]',
            'account_name'    => 'required|max_length[150]',
            'account_number'  => 'required|max_length[50]',
            'transfer_amount' => 'required|numeric',
            'transfer_date'   => 'required|valid_date',
            'proof_image'     => 'uploaded[proof_image]|max_size[proof_image,2048]|is_image[proof_image]|mime_in[proof_image,image/jpg,image/jpeg,image/png]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Upload file
        $file = $this->request->getFile('proof_image');
        $fileName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads/payment_proofs', $fileName);

        $paymentData = [
            'bank_name'       => $this->request->getPost('bank_name'),
            'account_name'    => $this->request->getPost('account_name'),
            'account_number'  => $this->request->getPost('account_number'),
            'transfer_amount' => $this->request->getPost('transfer_amount'),
            'transfer_date'   => $this->request->getPost('transfer_date'),
            'proof_image'     => 'payment_proofs/' . $fileName,
        ];

        $result = $this->paymentService->submitPayment($order->id, $paymentData);

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->to('/my-orders/view/' . $orderNumber)->with('success', $result['message']);
    }
}
