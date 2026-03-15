<?php

namespace App\Controllers\Canvassing;

use App\Controllers\BaseController;
use App\Models\ManagerCustomerModel;
use App\Models\ManagerActivityLogModel;
use App\Models\OrderModel;
use App\Models\LicenseModel;
use App\Libraries\DataTableHandler;

class CanvassingDashboardController extends BaseController
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
        $managerId   = auth()->id();
        $customerIds = $this->mcModel->getCustomerIds($managerId);

        $orderModel   = new OrderModel();
        $licenseModel = new LicenseModel();

        $totalCustomers = count($customerIds);

        if (empty($customerIds)) {
            $orderStats = ['pending' => 0, 'awaiting_confirmation' => 0, 'paid' => 0, 'total' => 0];
            $activeLicenses   = 0;
            $expiringLicenses = [];
        } else {
            $orderStats = [
                'pending'               => $orderModel->whereIn('orders.user_id', $customerIds)->where('orders.status', 'pending')->countAllResults(),
                'awaiting_confirmation' => $orderModel->whereIn('orders.user_id', $customerIds)->where('orders.status', 'awaiting_confirmation')->countAllResults(),
                'paid'                  => $orderModel->whereIn('orders.user_id', $customerIds)->where('orders.status', 'paid')->countAllResults(),
                'total'                 => $orderModel->whereIn('orders.user_id', $customerIds)->countAllResults(),
            ];

            $activeLicenses = $licenseModel
                ->whereIn('licenses.user_id', $customerIds)
                ->where('licenses.status', 'active')
                ->where('licenses.expires_at >=', date('Y-m-d H:i:s'))
                ->where('licenses.is_trial', 0)
                ->countAllResults();

            $expiringLicenses = $licenseModel
                ->select('licenses.*, licenses.uuid, plans.name as plan_name, users.username')
                ->join('plans', 'plans.id = licenses.plan_id', 'left')
                ->join('users', 'users.id = licenses.user_id', 'left')
                ->whereIn('licenses.user_id', $customerIds)
                ->where('licenses.status', 'active')
                ->where('licenses.is_trial', 0)
                ->where('licenses.expires_at >=', date('Y-m-d H:i:s'))
                ->where('licenses.expires_at <=', date('Y-m-d H:i:s', strtotime('+14 days')))
                ->orderBy('licenses.expires_at', 'ASC')
                ->findAll();
        }

        $recentActivity = $this->logModel->getLogsByManager($managerId, 10);

        $data = [
            'title'            => 'Canvassing',
            'page_title'       => 'Dashboard Canvassing',
            'totalCustomers'   => $totalCustomers,
            'orderStats'       => $orderStats,
            'activeLicenses'   => $activeLicenses,
            'expiringLicenses' => $expiringLicenses,
            'recentActivity'   => $recentActivity,
        ];

        return $this->renderView('canvassing/dashboard', $data);
    }

    public function activityLog()
    {
        $managerId = auth()->id();

        $data = [
            'title'      => 'Log Aktivitas',
            'page_title' => 'Log Aktivitas Canvassing',
            'customers'  => $this->mcModel->getCustomersByManager($managerId),
        ];

        return $this->renderView('canvassing/activity_log', $data);
    }

    public function activityLogAjax()
    {
        $managerId = auth()->id();
        $db = \Config\Database::connect();

        $builder = $db->table('manager_activity_logs')
            ->select('manager_activity_logs.*, users.username as customer_username')
            ->join('users', 'users.id = manager_activity_logs.customer_id')
            ->where('manager_activity_logs.manager_id', $managerId);

        // Filter: customer
        $customerId = $this->request->getGet('customer_id');
        if (! empty($customerId)) {
            $builder->where('manager_activity_logs.customer_id', (int) $customerId);
        }

        // Filter: action type
        $actionType = $this->request->getGet('action_type');
        if (! empty($actionType)) {
            $builder->where('manager_activity_logs.action_type', $actionType);
        }

        // Filter: date range
        $dateFrom = $this->request->getGet('date_from');
        $dateTo   = $this->request->getGet('date_to');
        if (! empty($dateFrom)) {
            $builder->where('manager_activity_logs.created_at >=', $dateFrom);
        }
        if (! empty($dateTo)) {
            $builder->where('manager_activity_logs.created_at <=', $dateTo);
        }

        $countBuilder = clone $builder;

        $handler = new DataTableHandler($this->request);
        $result = $handler->setBuilder($builder)
            ->setCountBuilder($countBuilder)
            ->setColumnMap([
                0 => 'manager_activity_logs.id',
                1 => 'manager_activity_logs.created_at',
                2 => 'users.username',
                3 => 'manager_activity_logs.action_type',
                4 => 'manager_activity_logs.description',
            ])
            ->process();

        return $this->response->setJSON($result);
    }
}
