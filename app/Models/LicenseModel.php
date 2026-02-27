<?php

namespace App\Models;

use CodeIgniter\Model;

class LicenseModel extends Model
{
    protected $table         = 'licenses';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'order_id', 'user_id', 'plan_id', 'license_key',
        'device_id', 'activated_at', 'expires_at', 'status',
    ];
    protected $useTimestamps = true;
    protected $returnType    = 'object';

    /**
     * Generate a unique 20-character license key.
     */
    public function generateLicenseKey(): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $key        = '';

        do {
            $key = '';
            for ($i = 0; $i < 20; $i++) {
                $key .= $characters[random_int(0, strlen($characters) - 1)];
            }
            // Format: XXXXX-XXXXX-XXXXX-XXXXX
        } while ($this->where('license_key', $key)->first());

        return $key;
    }

    /**
     * Get licenses with related info.
     */
    public function getLicensesWithDetails(): array
    {
        return $this->select('licenses.*, plans.name as plan_name, users.username, orders.order_number')
            ->join('plans', 'plans.id = licenses.plan_id', 'left')
            ->join('users', 'users.id = licenses.user_id', 'left')
            ->join('orders', 'orders.id = licenses.order_id', 'left')
            ->orderBy('licenses.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Find license by key.
     */
    public function findByKey(string $licenseKey): ?object
    {
        return $this->select('licenses.*, plans.name as plan_name, plans.duration_days')
            ->join('plans', 'plans.id = licenses.plan_id', 'left')
            ->where('licenses.license_key', $licenseKey)
            ->first();
    }

    /**
     * Check if a license is valid (active & not expired).
     */
    public function isValid(object $license): bool
    {
        if ($license->status !== 'active') {
            return false;
        }

        if (strtotime($license->expires_at) < time()) {
            // Auto-update expired status
            $this->update($license->id, ['status' => 'expired']);
            return false;
        }

        return true;
    }
}
