<?php

namespace App\Libraries\Payment;

use App\Models\PaymentConfirmationModel;

/**
 * Manual Payment Handler.
 *
 * Menangani pembayaran manual via transfer bank.
 * User mengunggah bukti bayar, admin mereview.
 */
class ManualPaymentHandler implements PaymentHandlerInterface
{
    protected PaymentConfirmationModel $confirmationModel;

    public function __construct()
    {
        $this->confirmationModel = new PaymentConfirmationModel();
    }

    public function getMethod(): string
    {
        return 'manual';
    }

    /**
     * Process manual payment = simpan data konfirmasi pembayaran.
     */
    public function processPayment(object $order, array $data = []): array
    {
        try {
            $confirmationData = [
                'order_id'        => $order->id,
                'user_id'         => $order->user_id,
                'bank_name'       => $data['bank_name'] ?? '',
                'account_name'    => $data['account_name'] ?? '',
                'account_number'  => $data['account_number'] ?? '',
                'transfer_amount' => $data['transfer_amount'] ?? $order->amount,
                'transfer_date'   => $data['transfer_date'] ?? date('Y-m-d'),
                'proof_image'     => $data['proof_image'] ?? '',
                'status'          => 'pending',
            ];

            $this->confirmationModel->insert($confirmationData);

            return [
                'success' => true,
                'message' => 'Konfirmasi pembayaran berhasil dikirim. Menunggu review admin.',
                'data'    => [
                    'confirmation_id' => $this->confirmationModel->getInsertID(),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengirim konfirmasi: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify manual payment = admin approve/reject.
     */
    public function verifyPayment(object $order, array $data = []): array
    {
        $confirmationId = $data['confirmation_id'] ?? null;
        $action         = $data['action'] ?? 'approve'; // approve or reject

        if (! $confirmationId) {
            return ['success' => false, 'message' => 'Confirmation ID diperlukan.'];
        }

        $confirmation = $this->confirmationModel->find($confirmationId);
        if (! $confirmation) {
            return ['success' => false, 'message' => 'Konfirmasi tidak ditemukan.'];
        }

        $updateData = [
            'status'      => $action === 'approve' ? 'approved' : 'rejected',
            'admin_notes' => $data['admin_notes'] ?? null,
            'reviewed_by' => $data['reviewed_by'] ?? null,
            'reviewed_at' => date('Y-m-d H:i:s'),
        ];

        $this->confirmationModel->update($confirmationId, $updateData);

        return [
            'success' => $action === 'approve',
            'message' => $action === 'approve'
                ? 'Pembayaran disetujui.'
                : 'Pembayaran ditolak.',
            'data' => [
                'status' => $updateData['status'],
            ],
        ];
    }

    /**
     * Get payment status for manual payment.
     */
    public function getPaymentStatus(object $order): string
    {
        $confirmation = $this->confirmationModel
            ->where('order_id', $order->id)
            ->orderBy('created_at', 'DESC')
            ->first();

        if (! $confirmation) {
            return 'no_confirmation';
        }

        return $confirmation->status;
    }
}
