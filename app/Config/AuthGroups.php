<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    /**
     * --------------------------------------------------------------------
     * Default Group
     * --------------------------------------------------------------------
     */
    public string $defaultGroup = 'user';

    /**
     * --------------------------------------------------------------------
     * Groups
     * --------------------------------------------------------------------
     */
    public array $groups = [
        'superadmin' => [
            'title'       => 'Super Admin',
            'description' => 'Kontrol penuh terhadap seluruh sistem.',
        ],
        'admin' => [
            'title'       => 'Admin',
            'description' => 'Administrator harian sistem.',
        ],
        'manager' => [
            'title'       => 'Manager',
            'description' => 'Manajer yang dapat melihat laporan dan mengelola data.',
        ],
        'user' => [
            'title'       => 'User',
            'description' => 'Pengguna umum dengan akses terbatas.',
        ],
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions
     * --------------------------------------------------------------------
     */
    public array $permissions = [
        // Admin area
        'admin.access'        => 'Dapat mengakses area admin',
        'admin.settings'      => 'Dapat mengakses pengaturan sistem',

        // User management
        'users.list'          => 'Dapat melihat daftar pengguna',
        'users.create'        => 'Dapat membuat pengguna baru',
        'users.edit'          => 'Dapat mengedit pengguna',
        'users.delete'        => 'Dapat menghapus pengguna',
        'users.manage-roles'  => 'Dapat mengatur role pengguna',

        // Role management
        'roles.list'          => 'Dapat melihat daftar role',
        'roles.create'        => 'Dapat membuat role baru',
        'roles.edit'          => 'Dapat mengedit role',
        'roles.delete'        => 'Dapat menghapus role',

        // Dashboard
        'dashboard.access'    => 'Dapat mengakses dashboard',
        'dashboard.stats'     => 'Dapat melihat statistik',

        // Reports
        'reports.view'        => 'Dapat melihat laporan',
        'reports.export'      => 'Dapat mengekspor laporan',

        // Plans
        'plans.list'          => 'Dapat melihat daftar paket',
        'plans.create'        => 'Dapat membuat paket baru',
        'plans.edit'          => 'Dapat mengedit paket',
        'plans.delete'        => 'Dapat menghapus paket',

        // Orders
        'orders.list'         => 'Dapat melihat daftar order',
        'orders.create'       => 'Dapat membuat order baru',
        'orders.view'         => 'Dapat melihat detail order',
        'orders.approve'      => 'Dapat menyetujui order',
        'orders.reject'       => 'Dapat menolak order',

        // Licenses
        'licenses.list'       => 'Dapat melihat daftar lisensi',
        'licenses.view'       => 'Dapat melihat detail lisensi',
        'licenses.revoke'     => 'Dapat mencabut lisensi',

        // Payment Confirmations
        'payments.list'       => 'Dapat melihat konfirmasi pembayaran',
        'payments.review'     => 'Dapat mereview konfirmasi pembayaran',

        // Trial Licenses
        'trial-licenses.list'   => 'Dapat melihat daftar lisensi trial',
        'trial-licenses.create' => 'Dapat membuat lisensi trial',
        'trial-licenses.view'   => 'Dapat melihat detail lisensi trial',
        'trial-licenses.revoke' => 'Dapat mencabut lisensi trial',
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions Matrix
     * --------------------------------------------------------------------
     */
    public array $matrix = [
        'superadmin' => [
            'admin.*',
            'users.*',
            'roles.*',
            'dashboard.*',
            'reports.*',
            'plans.*',
            'orders.*',
            'licenses.*',
            'payments.*',
            'trial-licenses.*',
        ],
        'admin' => [
            'admin.access',
            'users.list',
            'users.create',
            'users.edit',
            'users.delete',
            'dashboard.*',
            'reports.*',
            'plans.*',
            'orders.*',
            'licenses.*',
            'payments.*',
            'trial-licenses.*',
        ],
        'manager' => [
            'admin.access',
            'users.list',
            'dashboard.*',
            'reports.*',
            'plans.list',
            'orders.list',
            'orders.view',
            'licenses.list',
            'licenses.view',
            'payments.list',
            'trial-licenses.list',
            'trial-licenses.view',
        ],
        'user' => [
            'dashboard.access',
            'orders.create',
            'orders.list',
            'orders.view',
            'licenses.list',
            'licenses.view',
            'plans.list',
        ],
    ];
}
