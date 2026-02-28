<?php

namespace App\Controllers;

use App\Models\LicenseModel;
use App\Libraries\DataTableHandler;

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
        ];

        return $this->renderView('licenses/index', $data);
    }

    /**
     * AJAX DataTables endpoint untuk licenses (admin).
     */
    public function ajax()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('licenses')
            ->select('licenses.*, plans.name as plan_name, users.username, orders.order_number')
            ->join('plans', 'plans.id = licenses.plan_id', 'left')
            ->join('users', 'users.id = licenses.user_id', 'left')
            ->join('orders', 'orders.id = licenses.order_id', 'left')
            ->where('licenses.is_trial', 0);

        // Filter: status (default = active)
        $status = $this->request->getGet('status');
        if (!empty($status)) {
            $builder->where('licenses.status', $status);
        }

        // Filter: device locked
        $device = $this->request->getGet('device');
        if ($device === 'locked') {
            $builder->where('licenses.device_id IS NOT NULL');
        } elseif ($device === 'unlocked') {
            $builder->where('licenses.device_id IS NULL');
        }

        $countBuilder = clone $builder;

        $handler = new DataTableHandler($this->request);
        $result = $handler->setBuilder($builder)
            ->setCountBuilder($countBuilder)
            ->setColumnMap([
                0 => 'licenses.id',
                1 => 'licenses.license_key',
                2 => 'users.username',
                3 => 'plans.name',
                4 => 'orders.order_number',
                5 => 'licenses.device_id',
                6 => 'licenses.expires_at',
                7 => 'licenses.status',
                8 => '', // actions
            ])
            ->process();

        return $this->response->setJSON($result);
    }

    public function view(int $id)
    {
        $license = $this->licenseModel->select('licenses.*, plans.name as plan_name, plans.duration_days, users.username, auth_identities.secret as email, orders.order_number')
            ->join('plans', 'plans.id = licenses.plan_id', 'left')
            ->join('users', 'users.id = licenses.user_id', 'left')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = \'email_password\'', 'left')
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
