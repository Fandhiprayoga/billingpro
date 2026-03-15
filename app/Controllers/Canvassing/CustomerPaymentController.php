<?php

namespace App\Controllers\Canvassing;

use App\Controllers\BaseController;
use App\Models\ManagerCustomerModel;
use App\Models\ManagerActivityLogModel;
use App\Models\OrderModel;
use App\Libraries\Payment\PaymentService;

class CustomerPaymentController extends BaseController
{
    protected ManagerCustomerModel $mcModel;
    protected ManagerActivityLogModel $logModel;
    protected OrderModel $orderModel;
    protected PaymentService $paymentService;

    public function __construct()
    {
        $this->mcModel        = new ManagerCustomerModel();
        $this->logModel       = new ManagerActivityLogModel();
        $this->orderModel     = new OrderModel();
        $this->paymentService = new PaymentService();
    }

    /**
     * Form upload bukti bayar untuk customer.
     */
    public function uploadForm(string $orderNumber)
    {
        $managerId   = auth()->id();
        $customerIds = $this->mcModel->getCustomerIds($managerId);

        $order = $this->orderModel->findByOrderNumber($orderNumber);

        if (! $order || ! in_array($order->user_id, $customerIds)) {
            return redirect()->to('/canvassing/customer-orders')->with('error', 'Order tidak ditemukan.');
        }

        if (! in_array($order->status, ['pending'])) {
            return redirect()->to('/canvassing/customer-orders/view/' . $orderNumber)
                ->with('error', 'Order tidak dalam status yang bisa diupload bukti bayar.');
        }

        $bankInfo = [
            'bank_name'       => setting('App.bankName') ?? '',
            'account_number'  => setting('App.bankAccountNumber') ?? '',
            'account_name'    => setting('App.bankAccountName') ?? '',
        ];

        $data = [
            'title'      => 'Upload Bukti Bayar',
            'page_title' => 'Upload Bukti Bayar Order #' . $order->order_number,
            'order'      => $order,
            'bankInfo'   => $bankInfo,
        ];

        return $this->renderView('canvassing/orders/upload_proof', $data);
    }

    /**
     * Submit bukti bayar untuk customer.
     */
    public function submitProof(string $orderNumber)
    {
        $managerId   = auth()->id();
        $customerIds = $this->mcModel->getCustomerIds($managerId);

        $order = $this->orderModel->findByOrderNumber($orderNumber);

        if (! $order || ! in_array($order->user_id, $customerIds)) {
            return redirect()->to('/canvassing/customer-orders')->with('error', 'Order tidak ditemukan.');
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

        // Mark the payment as uploaded by manager
        $confirmationModel = new \App\Models\PaymentConfirmationModel();
        $latestConfirmation = $confirmationModel
            ->where('order_id', $order->id)
            ->orderBy('id', 'DESC')
            ->first();

        if ($latestConfirmation) {
            $confirmationModel->update($latestConfirmation->id, [
                'uploaded_by_manager_id' => $managerId,
            ]);
        }

        // Log activity
        $this->logModel->logAction(
            $managerId, (int) $order->user_id, 'upload_payment',
            $order->id, 'order',
            'Upload bukti bayar untuk order ' . $order->order_number
        );

        return redirect()->to('/canvassing/customer-orders/view/' . $orderNumber)
            ->with('success', $result['message']);
    }
}
