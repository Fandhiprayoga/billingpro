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

        // API Documentation
        'api-docs.view'         => 'Dapat melihat dokumentasi API',

        // Canvassing (Manager → Customer)
        'canvassing.dashboard'       => 'Dapat mengakses dashboard canvassing',
        'canvassing.customers.list'  => 'Dapat melihat daftar customer',
        'canvassing.customers.view'  => 'Dapat melihat detail customer',
        'canvassing.orders.list'     => 'Dapat melihat order customer',
        'canvassing.orders.create'   => 'Dapat membuat order untuk customer',
        'canvassing.orders.approve'  => 'Dapat menyetujui order customer',
        'canvassing.orders.reject'   => 'Dapat menolak order customer',
        'canvassing.payments.upload' => 'Dapat upload bukti bayar untuk customer',
        'canvassing.licenses.list'   => 'Dapat melihat lisensi customer',
        'canvassing.licenses.renew'  => 'Dapat request perpanjangan lisensi customer',
        'canvassing.trials.list'     => 'Dapat melihat trial lisensi customer',
        'canvassing.trials.create'   => 'Dapat membuat trial lisensi untuk customer',
        'canvassing.trials.view'     => 'Dapat melihat detail trial lisensi customer',
        'canvassing.activity.view'   => 'Dapat melihat log aktivitas canvassing',
        'canvassing.assign'          => 'Dapat assign customer ke manager',
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
            'api-docs.*',
            'canvassing.*',
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
            'api-docs.*',
            'canvassing.assign',
            'canvassing.dashboard',
            'canvassing.customers.list',
            'canvassing.customers.view',
        ],
        'manager' => [
            'admin.access',
            'dashboard.access',
            'plans.list',
            'canvassing.dashboard',
            'canvassing.customers.list',
            'canvassing.customers.view',
            'canvassing.orders.list',
            'canvassing.orders.create',
            'canvassing.orders.approve',
            'canvassing.orders.reject',
            'canvassing.payments.upload',
            'canvassing.licenses.list',
            'canvassing.licenses.renew',
            'canvassing.trials.list',
            'canvassing.trials.create',
            'canvassing.trials.view',
            'canvassing.activity.view',
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
