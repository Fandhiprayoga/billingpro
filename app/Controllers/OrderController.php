<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\PlanModel;
use App\Models\PaymentConfirmationModel;
use App\Libraries\Payment\PaymentService;

class OrderController extends BaseController
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

    public function index()
    {
        $data = [
            'title'      => 'Manajemen Order',
            'page_title' => 'Daftar Order',
            'orders'     => $this->orderModel->getOrdersWithDetails(),
        ];

        return $this->renderView('orders/index', $data);
    }

    public function create()
    {
        $data = [
            'title'      => 'Buat Order',
            'page_title' => 'Buat Order Baru',
            'plans'      => $this->planModel->getActivePlans(),
        ];

        return $this->renderView('orders/create', $data);
    }

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

        return redirect()->to('/admin/orders')->with('success', $result['message'] . ' Nomor Order: ' . $result['data']['order_number']);
    }

    public function view(string $orderNumber)
    {
        $order = $this->orderModel->getOrderWithDetailsByNumber($orderNumber);
        if (! $order) {
            return redirect()->to('/admin/orders')->with('error', 'Order tidak ditemukan.');
        }

        $confirmationModel = new PaymentConfirmationModel();
        $confirmations = $confirmationModel
            ->where('order_id', $order->id)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $licenseModel = new \App\Models\LicenseModel();
        $license = $licenseModel->where('order_id', $order->id)->first();

        $data = [
            'title'         => 'Detail Order',
            'page_title'    => 'Detail Order #' . $order->order_number,
            'order'         => $order,
            'confirmations' => $confirmations,
            'license'       => $license,
        ];

        return $this->renderView('orders/view', $data);
    }

    public function uploadConfirmation(string $orderNumber)
    {
        $order = $this->orderModel->findByOrderNumber($orderNumber);
        if (! $order) {
            return redirect()->to('/admin/orders')->with('error', 'Order tidak ditemukan.');
        }

        $data = [
            'title'      => 'Upload Bukti Bayar',
            'page_title' => 'Upload Bukti Pembayaran',
            'order'      => $order,
        ];

        return $this->renderView('orders/upload_confirmation', $data);
    }

    public function submitConfirmation(string $orderNumber)
    {
        $order = $this->orderModel->findByOrderNumber($orderNumber);
        if (! $order) {
            return redirect()->to('/admin/orders')->with('error', 'Order tidak ditemukan.');
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

        return redirect()->to('/admin/orders/view/' . $orderNumber)->with('success', $result['message']);
    }

    public function approve(string $orderNumber)
    {
        $order = $this->orderModel->findByOrderNumber($orderNumber);
        if (! $order) {
            return redirect()->to('/admin/orders')->with('error', 'Order tidak ditemukan.');
        }

        $adminId    = auth()->id();
        $adminNotes = $this->request->getPost('admin_notes');

        $result = $this->paymentService->approveOrder($order->id, $adminId, $adminNotes);

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        $message = $result['message'] . ' License Key: ' . $result['data']['license_key'];
        return redirect()->to('/admin/orders/view/' . $orderNumber)->with('success', $message);
    }

    public function reject(string $orderNumber)
    {
        $order = $this->orderModel->findByOrderNumber($orderNumber);
        if (! $order) {
            return redirect()->to('/admin/orders')->with('error', 'Order tidak ditemukan.');
        }

        $adminId = auth()->id();
        $reason  = $this->request->getPost('reason');

        $result = $this->paymentService->rejectOrder($order->id, $adminId, $reason);

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->to('/admin/orders')->with('success', $result['message']);
    }
}
