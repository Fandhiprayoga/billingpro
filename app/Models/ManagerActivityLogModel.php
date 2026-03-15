<?php

namespace App\Models;

use CodeIgniter\Model;

class ManagerActivityLogModel extends Model
{
    protected $table         = 'manager_activity_logs';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['manager_id', 'customer_id', 'action_type', 'reference_id', 'reference_type', 'description', 'ip_address', 'created_at'];
    protected $useTimestamps = false;
    protected $returnType    = 'object';

    /**
     * Log a manager action.
     */
    public function logAction(int $managerId, int $customerId, string $actionType, ?int $referenceId = null, ?string $referenceType = null, ?string $description = null): void
    {
        $this->insert([
            'manager_id'     => $managerId,
            'customer_id'    => $customerId,
            'action_type'    => $actionType,
            'reference_id'   => $referenceId,
            'reference_type' => $referenceType,
            'description'    => $description,
            'ip_address'     => service('request')->getIPAddress(),
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get activity logs for a manager with customer info.
     */
    public function getLogsByManager(int $managerId, int $limit = 50): array
    {
        return $this->select('manager_activity_logs.*, users.username as customer_username')
            ->join('users', 'users.id = manager_activity_logs.customer_id')
            ->where('manager_activity_logs.manager_id', $managerId)
            ->orderBy('manager_activity_logs.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
