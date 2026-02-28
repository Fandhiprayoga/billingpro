<?php

namespace App\Controllers;

use App\Models\LicenseModel;
use App\Libraries\DataTableHandler;
use CodeIgniter\I18n\Time;

class TrialLicenseController extends BaseController
{
    protected LicenseModel $licenseModel;

    public function __construct()
    {
        $this->licenseModel = new LicenseModel();
    }

    /**
     * Daftar lisensi trial.
     */
    public function index()
    {
        $data = [
            'title'      => 'Lisensi Trial',
            'page_title' => 'Daftar Lisensi Trial',
        ];

        return $this->renderView('trial_licenses/index', $data);
    }

    /**
     * AJAX DataTables endpoint untuk trial licenses.
     */
    public function ajax()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('licenses')
            ->select('licenses.*, licenses.uuid, users.username, 
                      creator.username as created_by_name')
            ->join('users', 'users.id = licenses.user_id', 'left')
            ->join('users as creator', 'creator.id = licenses.created_by', 'left')
            ->where('licenses.is_trial', 1);

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
                2 => 'users.username',
                3 => 'licenses.trial_duration_days',
                4 => 'licenses.expires_at',
                5 => 'licenses.device_id',
                6 => 'licenses.status',
                7 => 'creator.username',
                8 => 'licenses.created_at',
                9 => '', // actions
            ])
            ->process();

        return $this->response->setJSON($result);
    }

    /**
     * Form buat lisensi trial baru.
     */
    public function create()
    {
        // Load all users for select dropdown
        $userModel = auth()->getProvider();
        $users = $userModel->findAll();

        $data = [
            'title'      => 'Buat Lisensi Trial',
            'page_title' => 'Buat Lisensi Trial Baru',
            'users'      => $users,
        ];

        return $this->renderView('trial_licenses/create', $data);
    }

    /**
     * Simpan lisensi trial baru.
     */
    public function store()
    {
        $rules = [
            'user_id'       => 'required|integer',
            'duration_days' => 'required|integer|greater_than[0]|less_than_equal_to[365]',
            'notes'         => 'permit_empty|string|max_length[1000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId      = (int) $this->request->getPost('user_id');
        $durationDays = (int) $this->request->getPost('duration_days');
        $notes        = $this->request->getPost('notes');

        // Cek apakah user sudah punya trial license yang masih aktif
        $existingTrial = $this->licenseModel
            ->where('user_id', $userId)
            ->where('is_trial', 1)
            ->where('status', 'active')
            ->first();

        if ($existingTrial) {
            return redirect()->back()->withInput()
                ->with('error', 'User ini sudah memiliki lisensi trial yang masih aktif.');
        }

        // Generate license key & save
        $licenseKey = $this->licenseModel->generateLicenseKey();
        $now        = Time::now();
        $expiresAt  = $now->addDays($durationDays);

        $this->licenseModel->insert([
            'user_id'             => $userId,
            'order_id'            => null,
            'plan_id'             => null,
            'license_key'         => $licenseKey,
            'expires_at'          => $expiresAt->toDateTimeString(),
            'status'              => 'active',
            'is_trial'            => 1,
            'trial_duration_days' => $durationDays,
            'trial_notes'         => $notes,
            'created_by'          => auth()->id(),
        ]);

        return redirect()->to('/admin/trial-licenses')
            ->with('success', "Lisensi trial berhasil dibuat! Key: {$licenseKey}");
    }

    /**
     * Detail lisensi trial.
     */
    public function view(string $uuid)
    {
        $license = $this->licenseModel
            ->select('licenses.*, users.username, 
                      auth_identities.secret as email,
                      creator.username as created_by_name')
            ->join('users', 'users.id = licenses.user_id', 'left')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = \'email_password\'', 'left')
            ->join('users as creator', 'creator.id = licenses.created_by', 'left')
            ->where('licenses.uuid', $uuid)
            ->where('licenses.is_trial', 1)
            ->first();

        if (! $license) {
            return redirect()->to('/admin/trial-licenses')->with('error', 'Lisensi trial tidak ditemukan.');
        }

        $data = [
            'title'      => 'Detail Lisensi Trial',
            'page_title' => 'Detail Lisensi Trial',
            'license'    => $license,
        ];

        return $this->renderView('trial_licenses/view', $data);
    }

    /**
     * Cabut lisensi trial.
     */
    public function revoke(string $uuid)
    {
        $license = $this->licenseModel
            ->where('uuid', $uuid)
            ->where('is_trial', 1)
            ->first();

        if (! $license) {
            return redirect()->to('/admin/trial-licenses')->with('error', 'Lisensi trial tidak ditemukan.');
        }

        if ($license->status !== 'active') {
            return redirect()->to('/admin/trial-licenses')->with('error', 'Hanya lisensi aktif yang bisa dicabut.');
        }

        $this->licenseModel->update($license->id, [
            'status' => 'revoked',
        ]);

        return redirect()->to('/admin/trial-licenses')->with('success', 'Lisensi trial berhasil dicabut.');
    }
}
