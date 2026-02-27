<?php

namespace App\Controllers;

use App\Models\LicenseModel;

class LicenseController extends BaseController
{
    protected LicenseModel $licenseModel;

    public function __construct()
    {
        $this->licenseModel = new LicenseModel();
    }

    public function index()
    {
        $data = [
            'title'      => 'Manajemen Lisensi',
            'page_title' => 'Daftar Lisensi',
            'licenses'   => $this->licenseModel->getLicensesWithDetails(),
        ];

        return $this->renderView('licenses/index', $data);
    }

    public function view(int $id)
    {
        $license = $this->licenseModel->select('licenses.*, plans.name as plan_name, plans.duration_days, users.username, users.email, orders.order_number')
            ->join('plans', 'plans.id = licenses.plan_id', 'left')
            ->join('users', 'users.id = licenses.user_id', 'left')
            ->join('orders', 'orders.id = licenses.order_id', 'left')
            ->where('licenses.id', $id)
            ->first();

        if (! $license) {
            return redirect()->to('/admin/licenses')->with('error', 'Lisensi tidak ditemukan.');
        }

        $data = [
            'title'      => 'Detail Lisensi',
            'page_title' => 'Detail Lisensi',
            'license'    => $license,
        ];

        return $this->renderView('licenses/view', $data);
    }

    public function revoke(int $id)
    {
        $license = $this->licenseModel->find($id);
        if (! $license) {
            return redirect()->to('/admin/licenses')->with('error', 'Lisensi tidak ditemukan.');
        }

        $this->licenseModel->update($id, [
            'status' => 'revoked',
        ]);

        return redirect()->to('/admin/licenses')->with('success', 'Lisensi berhasil dicabut.');
    }
}
