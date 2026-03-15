<?php

namespace App\Controllers\Canvassing;

use App\Controllers\BaseController;
use App\Models\ManagerCustomerModel;
use App\Models\ManagerActivityLogModel;
use App\Models\LicenseModel;
use App\Libraries\DataTableHandler;
use CodeIgniter\I18n\Time;

class CustomerTrialController extends BaseController
{
    protected ManagerCustomerModel $mcModel;
    protected ManagerActivityLogModel $logModel;
    protected LicenseModel $licenseModel;

    public function __construct()
    {
        $this->mcModel      = new ManagerCustomerModel();
        $this->logModel     = new ManagerActivityLogModel();
        $this->licenseModel = new LicenseModel();
    }

    /**
     * Daftar trial lisensi customer.
     */
    public function index()
    {
        $data = [
            'title'      => 'Trial Lisensi Customer',
            'page_title' => 'Trial Lisensi Customer Saya',
        ];

        return $this->renderView('canvassing/trials/index', $data);
    }

    /**
     * AJAX DataTables endpoint.
     */
    public function ajax()
    {
        $managerId   = auth()->id();
        $customerIds = $this->mcModel->getCustomerIds($managerId);

        if (empty($customerIds)) {
            return $this->response->setJSON([
                'draw'            => (int) $this->request->getGet('draw'),
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
            ]);
        }

        $db      = \Config\Database::connect();
        $builder = $db->table('licenses')
            ->select('licenses.*, licenses.uuid, users.username, creator.username as created_by_name')
            ->join('users', 'users.id = licenses.user_id', 'left')
            ->join('users as creator', 'creator.id = licenses.created_by', 'left')
            ->where('licenses.is_trial', 1)
            ->whereIn('licenses.user_id', $customerIds);

        $status = $this->request->getGet('status');
        if (! empty($status)) {
            $builder->where('licenses.status', $status);
        }

        $countBuilder = clone $builder;

        $handler = new DataTableHandler($this->request);
        $result  = $handler->setBuilder($builder)
            ->setCountBuilder($countBuilder)
            ->setColumnMap([
                0 => 'licenses.id',
                1 => 'users.username',
                2 => 'licenses.license_key',
                3 => 'licenses.trial_duration_days',
                4 => 'licenses.status',
                5 => 'licenses.expires_at',
                6 => 'licenses.created_at',
                7 => '', // actions
            ])
            ->process();

        return $this->response->setJSON($result);
    }

    /**
     * Form buat trial lisensi untuk customer tertentu.
     */
    public function create(int $customerId)
    {
        $managerId   = auth()->id();
        $customerIds = $this->mcModel->getCustomerIds($managerId);

        if (! in_array($customerId, $customerIds)) {
            return redirect()->to('/canvassing/customer-trials')->with('error', 'Customer tidak ditemukan.');
        }

        // Load customer info
        $userModel = auth()->getProvider();
        $customer  = $userModel->findById($customerId);

        if (! $customer) {
            return redirect()->to('/canvassing/customer-trials')->with('error', 'Customer tidak ditemukan.');
        }

        $data = [
            'title'      => 'Buat Trial Lisensi',
            'page_title' => 'Buat Trial Lisensi untuk Customer',
            'customer'   => $customer,
        ];

        return $this->renderView('canvassing/trials/create', $data);
    }

    /**
     * Simpan trial lisensi untuk customer.
     */
    public function store(int $customerId)
    {
        $managerId   = auth()->id();
        $customerIds = $this->mcModel->getCustomerIds($managerId);

        if (! in_array($customerId, $customerIds)) {
            return redirect()->to('/canvassing/customer-trials')->with('error', 'Customer tidak ditemukan.');
        }

        $rules = [
            'duration_days' => 'required|integer|greater_than[0]|less_than_equal_to[365]',
            'notes'         => 'permit_empty|string|max_length[1000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $durationDays = (int) $this->request->getPost('duration_days');
        $notes        = $this->request->getPost('notes');

        // Cek apakah customer sudah punya trial license aktif
        $existingTrial = $this->licenseModel
            ->where('user_id', $customerId)
            ->where('is_trial', 1)
            ->where('status', 'active')
            ->first();

        if ($existingTrial) {
            return redirect()->back()->withInput()
                ->with('error', 'Customer ini sudah memiliki lisensi trial yang masih aktif.');
        }

        $licenseKey = $this->licenseModel->generateLicenseKey();
        $now        = Time::now();
        $expiresAt  = $now->addDays($durationDays);

        $this->licenseModel->insert([
            'user_id'             => $customerId,
            'order_id'            => null,
            'plan_id'             => null,
            'license_key'         => $licenseKey,
            'expires_at'          => $expiresAt->toDateTimeString(),
            'status'              => 'active',
            'is_trial'            => 1,
            'trial_duration_days' => $durationDays,
            'trial_notes'         => $notes,
            'created_by'          => $managerId,
        ]);

        $licenseId = $this->licenseModel->getInsertID();

        // Log activity
        $this->logModel->logAction(
            $managerId,
            $customerId,
            'create_trial',
            $licenseId,
            'license',
            'Membuat trial lisensi ' . $licenseKey . ' (' . $durationDays . ' hari)'
        );

        return redirect()->to('/canvassing/customer-trials')
            ->with('success', "Trial lisensi berhasil dibuat! Key: {$licenseKey}");
    }

    /**
     * Detail trial lisensi customer.
     */
    public function view(string $uuid)
    {
        $managerId   = auth()->id();
        $customerIds = $this->mcModel->getCustomerIds($managerId);

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

        if (! $license || ! in_array($license->user_id, $customerIds)) {
            return redirect()->to('/canvassing/customer-trials')->with('error', 'Trial lisensi tidak ditemukan.');
        }

        // Log view activity
        $this->logModel->logAction(
            $managerId,
            (int) $license->user_id,
            'view_profile',
            (int) $license->id,
            'license',
            'Melihat detail trial lisensi ' . $license->license_key
        );

        $data = [
            'title'      => 'Detail Trial Lisensi',
            'page_title' => 'Detail Trial Lisensi Customer',
            'license'    => $license,
        ];

        return $this->renderView('canvassing/trials/view', $data);
    }
}
