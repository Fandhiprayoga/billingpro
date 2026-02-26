<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Maintenance Filter
 *
 * Mengecek apakah mode pemeliharaan aktif.
 * Jika aktif, semua user kecuali Super Admin akan diarahkan ke halaman maintenance.
 * Super Admin tetap bisa mengakses seluruh sistem agar bisa mematikan mode maintenance.
 */
class MaintenanceFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Cek apakah maintenance mode aktif
        $isMaintenanceOn = setting('App.maintenanceMode') === '1';

        if (! $isMaintenanceOn) {
            return;
        }

        // Izinkan akses ke halaman maintenance itu sendiri (hindari redirect loop)
        $currentPath = uri_string();
        if ($currentPath === 'maintenance') {
            return;
        }

        // Izinkan akses ke halaman login/logout/auth agar user bisa login
        $allowedPaths = ['login', 'logout', 'register'];
        foreach ($allowedPaths as $path) {
            if (str_starts_with($currentPath, $path)) {
                return;
            }
        }

        // Jika user sudah login, cek apakah superadmin
        if (auth()->loggedIn()) {
            $user = auth()->user();
            if ($user->inGroup('superadmin')) {
                // Superadmin tetap bisa akses seluruh sistem
                return;
            }
        }

        // Redirect user lain ke halaman maintenance
        return redirect()->to('/maintenance');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
