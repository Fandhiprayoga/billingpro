<?php

namespace App\Controllers;

use App\Models\LicenseModel;

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
        $userId = auth()->id();

        $licenses = $this->licenseModel
            ->select('licenses.*, plans.name as plan_name, orders.order_number')
            ->join('plans', 'plans.id = licenses.plan_id', 'left')
            ->join('orders', 'orders.id = licenses.order_id', 'left')
            ->where('licenses.user_id', $userId)
            ->orderBy('licenses.created_at', 'DESC')
            ->findAll();

        $data = [
            'title'      => 'Lisensi Saya',
            'page_title' => 'Lisensi Saya',
            'licenses'   => $licenses,
        ];

        return $this->renderView('user_billing/licenses', $data);
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
