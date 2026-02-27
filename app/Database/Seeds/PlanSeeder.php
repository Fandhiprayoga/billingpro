<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run()
    {
        $plans = [
            [
                'name'          => 'Starter',
                'slug'          => 'starter',
                'description'   => 'Cocok untuk toko kecil yang baru memulai.',
                'price'         => 99000,
                'duration_days' => 30,
                'features'      => json_encode([
                    '1 outlet',
                    'Maks 500 transaksi/bulan',
                    'Laporan dasar',
                    'Email support',
                ]),
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'          => 'Professional',
                'slug'          => 'professional',
                'description'   => 'Untuk bisnis yang sedang berkembang.',
                'price'         => 249000,
                'duration_days' => 30,
                'features'      => json_encode([
                    '3 outlet',
                    'Unlimited transaksi',
                    'Laporan lengkap',
                    'Priority support',
                    'Multi-user',
                ]),
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'          => 'Enterprise',
                'slug'          => 'enterprise',
                'description'   => 'Untuk perusahaan dengan banyak cabang.',
                'price'         => 499000,
                'duration_days' => 30,
                'features'      => json_encode([
                    'Unlimited outlet',
                    'Unlimited transaksi',
                    'Laporan advanced & analytics',
                    'Dedicated support 24/7',
                    'Multi-user & multi-role',
                    'API access',
                    'Custom branding',
                ]),
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'          => 'Enterprise Yearly',
                'slug'          => 'enterprise-yearly',
                'description'   => 'Paket Enterprise dengan diskon tahunan.',
                'price'         => 4990000,
                'duration_days' => 365,
                'features'      => json_encode([
                    'Semua fitur Enterprise',
                    'Hemat 2 bulan',
                    'Onboarding gratis',
                    'SLA 99.9%',
                ]),
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($plans as $plan) {
            $this->db->table('plans')->insert($plan);
            echo "Plan '{$plan['name']}' created.\n";
        }

        echo "\n=== Plans seeded successfully ===\n";
    }
}
