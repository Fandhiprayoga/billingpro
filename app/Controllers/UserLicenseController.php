<?php

namespace App\Controllers;

use App\Models\LicenseModel;
use App\Libraries\DataTableHandler;

/**
 * UserLicenseController
 *
 * Controller khusus untuk user biasa (non-admin).
 * User hanya bisa melihat lisensi miliknya sendiri.
 */
class UserLicenseController extends BaseController
{
    protected LicenseModel $licenseModel;

    public function __construct()
    {
        $this->licenseModel = new LicenseModel();
    }

    /**
     * Daftar lisensi milik user yang login.
     */
    public function index()
    {
        $data = [
            'title'      => 'Lisensi Saya',
            'page_title' => 'Lisensi Saya',
        ];

        return $this->renderView('user_billing/licenses', $data);
    }

    /**
     * AJAX DataTables endpoint untuk lisensi milik user.
     */
    public function ajax()
    {
        $userId = auth()->id();
        $db = \Config\Database::connect();

        $builder = $db->table('licenses')
            ->select('licenses.*, plans.name as plan_name, orders.order_number')
            ->join('plans', 'plans.id = licenses.plan_id', 'left')
            ->join('orders', 'orders.id = licenses.order_id', 'left')
            ->where('licenses.user_id', $userId);

        // Filter: status
        $status = $this->request->getGet('status');
        if (!empty($status)) {
            $builder->where('licenses.status', $status);
        }

        $countBuilder = clone $builder;

        $handler = new DataTableHandler($this->request);
        $result = $handler->setBuilder($builder)
            ->setCountBuilder($countBuilder)
            ->setColumnMap([
                0 => 'licenses.id',
                1 => 'licenses.license_key',
                2 => 'plans.name',
                3 => 'orders.order_number',
                4 => 'licenses.device_id',
                5 => 'licenses.status',
                6 => 'licenses.expires_at',
                7 => '', // actions
            ])
            ->process();

        return $this->response->setJSON($result);
    }

    /**
     * Detail lisensi (hanya milik sendiri).
     */
    public function view(int $id)
    {
        $license = $this->licenseModel
            ->select('licenses.*, plans.name as plan_name, plans.duration_days, orders.order_number')
            ->join('plans', 'plans.id = licenses.plan_id', 'left')
            ->join('orders', 'orders.id = licenses.order_id', 'left')
            ->where('licenses.id', $id)
            ->first();

        if (! $license || $license->user_id != auth()->id()) {
            return redirect()->to('/my-licenses')->with('error', 'Lisensi tidak ditemukan.');
        }

        $data = [
            'title'      => 'Detail Lisensi',
            'page_title' => 'Detail Lisensi',
            'license'    => $license,
        ];

        return $this->renderView('user_billing/license_view', $data);
    }
}
