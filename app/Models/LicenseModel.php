<?php

namespace App\Models;

use CodeIgniter\Model;

class LicenseModel extends Model
{
    protected $table         = 'licenses';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'uuid', 'order_id', 'user_id', 'plan_id', 'license_key',
        'device_id', 'activated_at', 'expires_at', 'status',
        'is_trial', 'trial_duration_days', 'trial_notes', 'created_by',
    ];
    protected $useTimestamps = true;
    protected $returnType    = 'object';

    protected $beforeInsert = ['generateUuid'];

    /**
     * Auto-generate UUID v4 before insert.
     */
    protected function generateUuid(array $data): array
    {
        if (empty($data['data']['uuid'])) {
            $data['data']['uuid'] = self::createUuid();
        }
        return $data;
    }

    /**
     * Generate UUID v4.
     */
    public static function createUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Find license by UUID.
     */
    public function findByUuid(string $uuid): ?object
    {
        return $this->where('uuid', $uuid)->first();
    }

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
