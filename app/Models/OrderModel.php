<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table         = 'orders';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'order_number', 'type', 'user_id', 'plan_id', 'license_id', 'amount',
        'status', 'payment_method', 'payment_reference',
        'paid_at', 'rejected_at', 'notes', 'admin_notes',
    ];
    protected $useTimestamps = true;
    protected $returnType    = 'object';

    /**
     * Generate a unique order number.
     */
    public function generateOrderNumber(): string
    {
        $date   = date('Ymd');
        $random = strtoupper(bin2hex(random_bytes(3))); // 6 chars
        $number = "ORD-{$date}-{$random}";

        // Ensure uniqueness
        while ($this->where('order_number', $number)->first()) {
            $random = strtoupper(bin2hex(random_bytes(3)));
            $number = "ORD-{$date}-{$random}";
        }

        return $number;
    }

    /**
     * Get orders with plan and user info.
     */
    public function getOrdersWithDetails(): array
    {
        return $this->select('orders.*, plans.name as plan_name, users.username')
            ->join('plans', 'plans.id = orders.plan_id', 'left')
            ->join('users', 'users.id = orders.user_id', 'left')
            ->orderBy('orders.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get single order with details by ID.
     */
    public function getOrderWithDetails(int $id): ?object
    {
        return $this->select('orders.*, plans.name as plan_name, plans.duration_days, users.username, users.id as uid')
            ->join('plans', 'plans.id = orders.plan_id', 'left')
            ->join('users', 'users.id = orders.user_id', 'left')
            ->where('orders.id', $id)
            ->first();
    }

    /**
     * Get single order with details by order_number.
     */
    public function getOrderWithDetailsByNumber(string $orderNumber): ?object
    {
        return $this->select('orders.*, plans.name as plan_name, plans.duration_days, users.username, users.id as uid')
            ->join('plans', 'plans.id = orders.plan_id', 'left')
            ->join('users', 'users.id = orders.user_id', 'left')
            ->where('orders.order_number', $orderNumber)
            ->first();
    }

    /**
     * Find order by order_number.
     */
    public function findByOrderNumber(string $orderNumber): ?object
    {
        return $this->where('order_number', $orderNumber)->first();
    }
}
