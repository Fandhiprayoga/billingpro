<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerProfileModel extends Model
{
    protected $table         = 'customer_profiles';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['user_id', 'nama_usaha', 'no_telp', 'propinsi', 'kabupaten'];
    protected $useTimestamps = true;
    protected $returnType    = 'object';

    /**
     * Get profile by user ID
     */
    public function getByUserId(int $userId): ?object
    {
        return $this->where('user_id', $userId)->first();
    }

    /**
     * Create or update profile for a user
     */
    public function saveProfile(int $userId, array $data): bool
    {
        $existing = $this->where('user_id', $userId)->first();

        $data['user_id'] = $userId;

        if ($existing) {
            return $this->update($existing->id, $data);
        }

        return $this->insert($data) !== false;
    }
}
