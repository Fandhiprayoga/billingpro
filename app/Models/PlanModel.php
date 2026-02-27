<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanModel extends Model
{
    protected $table         = 'plans';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['name', 'slug', 'description', 'price', 'duration_days', 'features', 'is_active'];
    protected $useTimestamps = true;
    protected $returnType    = 'object';

    /**
     * Get only active plans.
     */
    public function getActivePlans(): array
    {
        return $this->where('is_active', 1)->findAll();
    }
}
