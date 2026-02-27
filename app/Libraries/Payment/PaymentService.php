<?php

namespace App\Libraries\Payment;

use App\Models\OrderModel;
use App\Models\LicenseModel;

/**
 * Payment Service.
 *
 * Service utama yang mengelola alur pembayaran.
 * Menggunakan Strategy Pattern sehingga mudah
 * menambah payment gateway baru tanpa mengubah
 * struktur tabel orders.
 *
 * Cara menambah Payment Gateway baru:
 * 1. Buat class baru yang implements PaymentHandlerInterface
 *    (contoh: MidtransPaymentHandler, XenditPaymentHandler)
 * 2. Daftarkan di method registerHandler() atau constructor
 * 3. Panggil setHandler('midtrans') sebelum processPayment()
 */
class PaymentService
{
    protected array $handlers = [];
    protected ?PaymentHandlerInterface $activeHandler = null;
    protected OrderModel $orderModel;
    protected LicenseModel $licenseModel;

    public function __construct()
    {
        $this->orderModel   = new OrderModel();
        $this->licenseModel = new LicenseModel();

        // Register default handlers
        $this->registerHandler(new ManualPaymentHandler());

        // Future: tambahkan handler baru di sini
        // $this->registerHandler(new MidtransPaymentHandler());
        // $this->registerHandler(new XenditPaymentHandler());
    }

    /**
     * Register a payment handler.
     */
    public function registerHandler(PaymentHandlerInterface $handler): self
    {
        $this->handlers[$handler->getMethod()] = $handler;
        return $this;
    }

    /**
     * Set the active payment handler.
     */
    public function setHandler(string $method): self
    {
        if (! isset($this->handlers[$method])) {
            throw new \RuntimeException("Payment handler '{$method}' belum terdaftar.");
        }

        $this->activeHandler = $this->handlers[$method];
        return $this;
    }

    /**
     * Get the active handler (default: manual).
     */
    protected function getHandler(): PaymentHandlerInterface
    {
        if ($this->activeHandler === null) {
            $this->setHandler('manual');
        }

        return $this->activeHandler;
    }

    /**
     * Create a new order.
     */
    public function createOrder(int $userId, int $planId, string $paymentMethod = 'manual', ?string $notes = null): array
    {
        $planModel = new \App\Models\PlanModel();
        $plan = $planModel->find($planId);

        if (! $plan) {
            return ['success' => false, 'message' => 'Paket tidak ditemukan.'];
        }

        $orderData = [
            'order_number'   => $this->orderModel->generateOrderNumber(),
            'user_id'        => $userId,
            'plan_id'        => $planId,
            'amount'         => $plan->price,
            'status'         => 'pending',
            'payment_method' => $paymentMethod,
            'notes'          => $notes,
        ];

        $this->orderModel->insert($orderData);
        $orderId = $this->orderModel->getInsertID();

        return [
            'success' => true,
            'message' => 'Order berhasil dibuat.',
            'data'    => [
                'order_id'     => $orderId,
                'order_number' => $orderData['order_number'],
            ],
        ];
    }

    /**
     * Submit payment confirmation for an order.
     */
    public function submitPayment(int $orderId, array $paymentData): array
    {
        $order = $this->orderModel->find($orderId);

        if (! $order) {
            return ['success' => false, 'message' => 'Order tidak ditemukan.'];
        }

        if (! in_array($order->status, ['pending', 'awaiting_confirmation'])) {
            return ['success' => false, 'message' => 'Order tidak dalam status yang bisa dibayar.'];
        }

        $this->setHandler($order->payment_method);
        $result = $this->getHandler()->processPayment($order, $paymentData);

        if ($result['success']) {
            $this->orderModel->update($orderId, ['status' => 'awaiting_confirmation']);
        }

        return $result;
    }

    /**
     * Approve an order and generate license.
     */
    public function approveOrder(int $orderId, int $adminId, ?string $adminNotes = null): array
    {
        $order = $this->orderModel->getOrderWithDetails($orderId);

        if (! $order) {
            return ['success' => false, 'message' => 'Order tidak ditemukan.'];
        }

        if ($order->status === 'paid') {
            return ['success' => false, 'message' => 'Order sudah disetujui sebelumnya.'];
        }

        // Update order status to paid
        $this->orderModel->update($orderId, [
            'status'  => 'paid',
            'paid_at' => date('Y-m-d H:i:s'),
        ]);

        // Approve the payment confirmation if manual
        if ($order->payment_method === 'manual') {
            $confirmationModel = new \App\Models\PaymentConfirmationModel();
            $confirmation = $confirmationModel
                ->where('order_id', $orderId)
                ->where('status', 'pending')
                ->first();

            if ($confirmation) {
                $this->setHandler('manual');
                $this->getHandler()->verifyPayment($order, [
                    'confirmation_id' => $confirmation->id,
                    'action'          => 'approve',
                    'reviewed_by'     => $adminId,
                    'admin_notes'     => $adminNotes,
                ]);
            }
        }

        // Generate license
        $licenseKey = $this->licenseModel->generateLicenseKey();
        $expiresAt  = date('Y-m-d H:i:s', strtotime("+{$order->duration_days} days"));

        $this->licenseModel->insert([
            'order_id'    => $orderId,
            'user_id'     => $order->user_id,
            'plan_id'     => $order->plan_id,
            'license_key' => $licenseKey,
            'expires_at'  => $expiresAt,
            'status'      => 'active',
        ]);

        return [
            'success' => true,
            'message' => 'Order disetujui. Lisensi berhasil di-generate.',
            'data'    => [
                'license_key' => $licenseKey,
                'expires_at'  => $expiresAt,
            ],
        ];
    }

    /**
     * Reject an order.
     */
    public function rejectOrder(int $orderId, int $adminId, ?string $reason = null): array
    {
        $order = $this->orderModel->find($orderId);

        if (! $order) {
            return ['success' => false, 'message' => 'Order tidak ditemukan.'];
        }

        $this->orderModel->update($orderId, [
            'status' => 'cancelled',
            'notes'  => $reason,
        ]);

        // Reject payment confirmation if exists
        if ($order->payment_method === 'manual') {
            $confirmationModel = new \App\Models\PaymentConfirmationModel();
            $confirmation = $confirmationModel
                ->where('order_id', $orderId)
                ->where('status', 'pending')
                ->first();

            if ($confirmation) {
                $this->setHandler('manual');
                $this->getHandler()->verifyPayment($order, [
                    'confirmation_id' => $confirmation->id,
                    'action'          => 'reject',
                    'reviewed_by'     => $adminId,
                    'admin_notes'     => $reason,
                ]);
            }
        }

        return [
            'success' => true,
            'message' => 'Order ditolak.',
        ];
    }
}
