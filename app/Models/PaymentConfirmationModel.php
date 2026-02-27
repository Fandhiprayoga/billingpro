<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentConfirmationModel extends Model
{
    protected $table         = 'payment_confirmations';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'order_id', 'user_id', 'bank_name', 'account_name',
        'account_number', 'transfer_amount', 'transfer_date',
        'proof_image', 'status', 'admin_notes', 'reviewed_by', 'reviewed_at',
    ];
    protected $useTimestamps = true;
    protected $returnType    = 'object';

    /**
     * Get confirmations with order details.
     */
    public function getConfirmationsWithDetails(): array
    {
        return $this->select('payment_confirmations.*, orders.order_number, orders.amount as order_amount, users.username')
            ->join('orders', 'orders.id = payment_confirmations.order_id', 'left')
            ->join('users', 'users.id = payment_confirmations.user_id', 'left')
            ->orderBy('payment_confirmations.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get pending confirmations count.
     */
    public function getPendingCount(): int
    {
        return $this->where('status', 'pending')->countAllResults();
    }
}
