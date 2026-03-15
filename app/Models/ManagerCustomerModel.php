<?php

namespace App\Models;

use CodeIgniter\Model;

class ManagerCustomerModel extends Model
{
    protected $table         = 'manager_customers';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['manager_id', 'customer_id', 'assigned_at', 'status', 'notes'];
    protected $useTimestamps = true;
    protected $returnType    = 'object';

    /**
     * Get all active customers for a manager.
     */
    public function getCustomersByManager(int $managerId): array
    {
        return $this->select('manager_customers.*, users.username, users.active, auth_identities.secret as email')
            ->join('users', 'users.id = manager_customers.customer_id')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = \'email_password\'', 'left')
            ->where('manager_customers.manager_id', $managerId)
            ->where('manager_customers.status', 'active')
            ->findAll();
    }

    /**
     * Check if a customer belongs to a manager.
     */
    public function isCustomerOf(int $managerId, int $customerId): bool
    {
        return $this->where('manager_id', $managerId)
            ->where('customer_id', $customerId)
            ->where('status', 'active')
            ->countAllResults() > 0;
    }

    /**
     * Get customer IDs for a manager.
     */
    public function getCustomerIds(int $managerId): array
    {
        $results = $this->select('customer_id')
            ->where('manager_id', $managerId)
            ->where('status', 'active')
            ->findAll();

        return array_map(fn($r) => (int) $r->customer_id, $results);
    }

    /**
     * Get manager for a customer.
     */
    public function getManagerOf(int $customerId): ?object
    {
        return $this->select('manager_customers.*, users.username as manager_username')
            ->join('users', 'users.id = manager_customers.manager_id')
            ->where('manager_customers.customer_id', $customerId)
            ->where('manager_customers.status', 'active')
            ->first();
    }

    /**
     * Get all managers (users with manager role) for assignment dropdown.
     */
    public function getAvailableManagers(): array
    {
        $db = \Config\Database::connect();
        return $db->table('users')
            ->select('users.id, users.username')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->where('auth_groups_users.group', 'manager')
            ->where('users.active', 1)
            ->orderBy('users.username', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * Get unassigned users (users not assigned to any manager).
     */
    public function getUnassignedUsers(): array
    {
        $db = \Config\Database::connect();
        return $db->table('users')
            ->select('users.id, users.username, auth_identities.secret as email')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = \'email_password\'', 'left')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->join('manager_customers', 'manager_customers.customer_id = users.id AND manager_customers.status = \'active\'', 'left')
            ->where('auth_groups_users.group', 'user')
            ->where('users.active', 1)
            ->where('manager_customers.id IS NULL')
            ->orderBy('users.username', 'ASC')
            ->get()
            ->getResult();
    }
}
