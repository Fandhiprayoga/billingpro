<?php

namespace App\Controllers\Canvassing;

use App\Controllers\BaseController;
use App\Models\ManagerCustomerModel;
use App\Models\ManagerActivityLogModel;
use App\Models\CustomerProfileModel;
use App\Models\OrderModel;
use App\Models\LicenseModel;
use App\Libraries\DataTableHandler;
use CodeIgniter\Shield\Models\UserModel;

class CustomerController extends BaseController
{
    protected ManagerCustomerModel $mcModel;
    protected ManagerActivityLogModel $logModel;

    public function __construct()
    {
        $this->mcModel  = new ManagerCustomerModel();
        $this->logModel = new ManagerActivityLogModel();
    }

    public function index()
    {
        $data = [
            'title'      => 'Customer Saya',
            'page_title' => 'Daftar Customer Saya',
        ];

        return $this->renderView('canvassing/customers/index', $data);
    }

    public function ajax()
    {
        $managerId = auth()->id();
        $db = \Config\Database::connect();

        $builder = $db->table('manager_customers')
            ->select('manager_customers.id as mc_id, manager_customers.customer_id, manager_customers.assigned_at, 
                       users.username, users.active, auth_identities.secret as email')
            ->join('users', 'users.id = manager_customers.customer_id')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = \'email_password\'', 'left')
            ->where('manager_customers.manager_id', $managerId)
            ->where('manager_customers.status', 'active');

        $countBuilder = clone $builder;

        $handler = new DataTableHandler($this->request);
        $result = $handler->setBuilder($builder)
            ->setCountBuilder($countBuilder)
            ->setColumnMap([
                0 => 'users.id',
                1 => 'users.username',
                2 => 'auth_identities.secret',
                3 => '', // nama_usaha
                4 => '', // no_telp
                5 => '', // licenses count
                6 => '', // orders count
                7 => '', // actions
            ])
            ->process();

        // Enrich with profile, license count, order count
        $profileModel = new CustomerProfileModel();
        $orderModel   = new OrderModel();
        $licenseModel = new LicenseModel();

        foreach ($result['data'] as &$row) {
            $custId = (int) $row->customer_id;
            $profile = $profileModel->getByUserId($custId);
            $row->nama_usaha = $profile->nama_usaha ?? $profile['nama_usaha'] ?? '';
            $row->no_telp    = $profile->no_telp ?? $profile['no_telp'] ?? '';

            $row->active_licenses = $licenseModel
                ->where('user_id', $custId)
                ->where('status', 'active')
                ->where('expires_at >=', date('Y-m-d H:i:s'))
                ->countAllResults();

            $row->pending_orders = $orderModel
                ->where('user_id', $custId)
                ->whereIn('status', ['pending', 'awaiting_confirmation'])
                ->countAllResults();
        }

        return $this->response->setJSON($result);
    }

    public function detail(int $customerId)
    {
        $managerId = auth()->id();

        if (! $this->mcModel->isCustomerOf($managerId, $customerId)) {
            return redirect()->to('/canvassing/my-customers')->with('error', 'Customer tidak ditemukan.');
        }

        $userModel    = new UserModel();
        $profileModel = new CustomerProfileModel();
        $orderModel   = new OrderModel();
        $licenseModel = new LicenseModel();

        $customer = $userModel->findById($customerId);
        if (! $customer) {
            return redirect()->to('/canvassing/my-customers')->with('error', 'Customer tidak ditemukan.');
        }

        $profile = $profileModel->getByUserId($customerId);

        $recentOrders = $orderModel
            ->select('orders.*, plans.name as plan_name')
            ->join('plans', 'plans.id = orders.plan_id', 'left')
            ->where('orders.user_id', $customerId)
            ->orderBy('orders.created_at', 'DESC')
            ->limit(10)
            ->findAll();

        $licenses = $licenseModel
            ->select('licenses.*, plans.name as plan_name')
            ->join('plans', 'plans.id = licenses.plan_id', 'left')
            ->where('licenses.user_id', $customerId)
            ->orderBy('licenses.created_at', 'DESC')
            ->findAll();

        // Log view
        $this->logModel->logAction($managerId, $customerId, 'view_profile', null, null, 'Melihat profil customer');

        $data = [
            'title'        => 'Detail Customer',
            'page_title'   => 'Detail Customer: ' . $customer->username,
            'customer'     => $customer,
            'profile'      => $profile,
            'recentOrders' => $recentOrders,
            'licenses'     => $licenses,
        ];

        return $this->renderView('canvassing/customers/detail', $data);
    }
}
