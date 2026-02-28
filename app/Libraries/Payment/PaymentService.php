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
    public function createOrder(int $userId, int $planId, string $paymentMethod = 'manual', ?string $notes = null, string $type = 'new', ?int $licenseId = null): array
    {
        $planModel = new \App\Models\PlanModel();
        $plan = $planModel->find($planId);

        if (! $plan) {
            return ['success' => false, 'message' => 'Paket tidak ditemukan.'];
        }

        $orderData = [
            'order_number'   => $this->orderModel->generateOrderNumber(),
            'type'           => $type,
            'user_id'        => $userId,
            'plan_id'        => $planId,
            'license_id'     => $licenseId,
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

        // Hanya order pending/awaiting_confirmation yang bisa di-approve
        if (! in_array($order->status, ['pending', 'awaiting_confirmation'])) {
            $msg = match($order->status) {
                'paid'      => 'Order sudah disetujui sebelumnya.',
                'cancelled' => 'Order sudah dibatalkan/ditolak. Tidak bisa disetujui.',
                'expired'   => 'Order sudah expired.',
                default     => 'Order tidak dalam status yang bisa disetujui.',
            };
            return ['success' => false, 'message' => $msg];
        }

        // Update order status to paid
        $this->orderModel->update($orderId, [
            'status'      => 'paid',
            'paid_at'     => date('Y-m-d H:i:s'),
            'admin_notes' => $adminNotes,
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
        // Handle renewal — extend existing license instead of creating new one
        if (($order->type ?? 'new') === 'renewal' && ! empty($order->license_id)) {
            $existingLicense = $this->licenseModel->find($order->license_id);
            if ($existingLicense) {
                // If license still active, add duration on top of current expires_at
                // If expired, start counting from now
                $baseTime = ($existingLicense->status === 'active' && strtotime($existingLicense->expires_at) > time())
                    ? strtotime($existingLicense->expires_at)
                    : time();

                $newExpiresAt = date('Y-m-d H:i:s', $baseTime + ($order->duration_days * 86400));

                $this->licenseModel->update($existingLicense->id, [
                    'expires_at' => $newExpiresAt,
                    'status'     => 'active',
                ]);

                return [
                    'success' => true,
                    'message' => 'Order perpanjangan disetujui. Masa aktif lisensi diperpanjang.',
                    'data'    => [
                        'license_key' => $existingLicense->license_key,
                        'expires_at'  => $newExpiresAt,
                    ],
                ];
            }
        }

        // New license — generate key and create
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

        // Hanya order yang belum cancelled/expired yang bisa ditolak
        if (in_array($order->status, ['cancelled', 'expired'])) {
            return ['success' => false, 'message' => 'Order sudah dibatalkan/expired. Tidak bisa ditolak lagi.'];
        }

        // Jika order sudah paid, revoke lisensi terkait
        if ($order->status === 'paid') {
            $existingLicense = $this->licenseModel->where('order_id', $orderId)->first();
            if ($existingLicense) {
                $this->licenseModel->update($existingLicense->id, [
                    'status' => 'revoked',
                ]);
            }
        }

        // Update order status ke cancelled, simpan reason di admin_notes (BUKAN overwrite notes user)
        $this->orderModel->update($orderId, [
            'status'      => 'cancelled',
            'admin_notes' => $reason,
            'rejected_at' => date('Y-m-d H:i:s'),
            'paid_at'     => null, // reset paid_at jika sebelumnya sudah paid
        ]);

        // Reject semua payment confirmation yang masih pending
        if ($order->payment_method === 'manual') {
            $confirmationModel = new \App\Models\PaymentConfirmationModel();
            $confirmations = $confirmationModel
                ->where('order_id', $orderId)
                ->where('status', 'pending')
                ->findAll();

            foreach ($confirmations as $confirmation) {
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
            'message' => $order->status === 'paid'
                ? 'Order ditolak dan lisensi terkait telah dicabut.'
                : 'Order ditolak.',
        ];
    }
}
