<?php

namespace App\Libraries\Payment;

/**
 * Payment Handler Interface.
 *
 * Semua payment handler (manual, midtrans, xendit, dll)
 * harus mengimplementasikan interface ini.
 */
interface PaymentHandlerInterface
{
    /**
     * Get the payment method identifier.
     */
    public function getMethod(): string;

    /**
     * Process a new payment for an order.
     *
     * @param object $order The order object
     * @param array  $data  Additional payment data
     * @return array Result with 'success' boolean and 'data'/'message'
     */
    public function processPayment(object $order, array $data = []): array;

    /**
     * Verify/confirm a payment.
     *
     * @param object $order The order object
     * @param array  $data  Verification data (e.g., callback data)
     * @return array Result with 'success' boolean and 'data'/'message'
     */
    public function verifyPayment(object $order, array $data = []): array;

    /**
     * Get payment status from the provider.
     *
     * @param object $order The order object
     * @return string Payment status
     */
    public function getPaymentStatus(object $order): string;
}
