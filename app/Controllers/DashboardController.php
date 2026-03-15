<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\LicenseModel;
use App\Models\PlanModel;
use App\Models\ManagerCustomerModel;
use App\Models\ManagerActivityLogModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $user   = auth()->user();
        $userId = auth()->id();
        $isAdmin   = activeGroupCan('admin.access');
        $isManager = activeGroupCan('canvassing.dashboard');

        $orderModel   = new OrderModel();
        $licenseModel = new LicenseModel();
        $planModel    = new PlanModel();

        // ---- Statistik Order ----
        if ($isManager && !activeGroupCan('orders.list')) {
            // Manager: statistik customer canvassing
            $mcModel      = new ManagerCustomerModel();
            $customerIds  = $mcModel->getCustomerIds($userId);

            if (empty($customerIds)) {
                $orderStats       = ['pending' => 0, 'awaiting_confirmation' => 0, 'paid' => 0, 'cancelled' => 0, 'expired' => 0, 'total' => 0];
                $activeLicenses   = 0;
                $expiringLicenses = [];
                $totalCustomers   = 0;
                $activeTrials     = 0;
            } else {
                $orderStats = [
                    'pending'               => $orderModel->whereIn('orders.user_id', $customerIds)->where('orders.status', 'pending')->countAllResults(),
                    'awaiting_confirmation' => $orderModel->whereIn('orders.user_id', $customerIds)->where('orders.status', 'awaiting_confirmation')->countAllResults(),
                    'paid'                  => $orderModel->whereIn('orders.user_id', $customerIds)->where('orders.status', 'paid')->countAllResults(),
                    'cancelled'             => $orderModel->whereIn('orders.user_id', $customerIds)->where('orders.status', 'cancelled')->countAllResults(),
                    'expired'               => $orderModel->whereIn('orders.user_id', $customerIds)->where('orders.status', 'expired')->countAllResults(),
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

                $totalCustomers = count($customerIds);

                $activeTrials = $licenseModel
                    ->whereIn('licenses.user_id', $customerIds)
                    ->where('licenses.status', 'active')
                    ->where('licenses.is_trial', 1)
                    ->where('licenses.expires_at >=', date('Y-m-d H:i:s'))
                    ->countAllResults();
            }

            $logModel       = new ManagerActivityLogModel();
            $recentActivity = $logModel->getLogsByManager($userId, 5);
            $pendingOrders  = [];
        } elseif ($isAdmin) {
            // Admin: semua order
            $orderStats = [
                'pending'               => $orderModel->where('orders.status', 'pending')->countAllResults(),
                'awaiting_confirmation' => $orderModel->where('orders.status', 'awaiting_confirmation')->countAllResults(),
                'paid'                  => $orderModel->where('orders.status', 'paid')->countAllResults(),
                'cancelled'             => $orderModel->where('orders.status', 'cancelled')->countAllResults(),
                'expired'               => $orderModel->where('orders.status', 'expired')->countAllResults(),
                'total'                 => $orderModel->countAllResults(),
            ];

            // Admin: total lisensi aktif (tanpa trial)
            $activeLicenses = $licenseModel->where('licenses.status', 'active')
                ->where('licenses.expires_at >=', date('Y-m-d H:i:s'))
                ->where('licenses.is_trial', 0)
                ->countAllResults();

            // Admin: lisensi yang akan expired dalam 7 hari (tanpa trial)
            $expiringLicenses = $licenseModel
                ->select('licenses.*, licenses.uuid, plans.name as plan_name, users.username, orders.order_number')
                ->join('plans', 'plans.id = licenses.plan_id', 'left')
                ->join('users', 'users.id = licenses.user_id', 'left')
                ->join('orders', 'orders.id = licenses.order_id', 'left')
                ->where('licenses.status', 'active')
                ->where('licenses.is_trial', 0)
                ->where('licenses.expires_at >=', date('Y-m-d H:i:s'))
                ->where('licenses.expires_at <=', date('Y-m-d H:i:s', strtotime('+7 days')))
                ->orderBy('licenses.expires_at', 'ASC')
                ->findAll();

            // Admin: order terbaru menunggu review
            $pendingOrders = $orderModel
                ->select('orders.*, plans.name as plan_name, users.username')
                ->join('plans', 'plans.id = orders.plan_id', 'left')
                ->join('users', 'users.id = orders.user_id', 'left')
                ->whereIn('orders.status', ['pending', 'awaiting_confirmation'])
                ->orderBy('orders.created_at', 'DESC')
                ->limit(5)
                ->findAll();
        } else {
            // User biasa: hanya order miliknya
            $orderStats = [
                'pending'               => $orderModel->where('orders.user_id', $userId)->where('orders.status', 'pending')->countAllResults(),
                'awaiting_confirmation' => $orderModel->where('orders.user_id', $userId)->where('orders.status', 'awaiting_confirmation')->countAllResults(),
                'paid'                  => $orderModel->where('orders.user_id', $userId)->where('orders.status', 'paid')->countAllResults(),
                'cancelled'             => $orderModel->where('orders.user_id', $userId)->where('orders.status', 'cancelled')->countAllResults(),
                'expired'               => $orderModel->where('orders.user_id', $userId)->where('orders.status', 'expired')->countAllResults(),
                'total'                 => $orderModel->where('orders.user_id', $userId)->countAllResults(),
            ];

            $activeLicenses = $licenseModel->where('licenses.user_id', $userId)
                ->where('licenses.status', 'active')
                ->where('licenses.expires_at >=', date('Y-m-d H:i:s'))
                ->where('licenses.is_trial', 0)
                ->countAllResults();

            // User: lisensi yang akan expired dalam 14 hari (tanpa trial)
            $expiringLicenses = $licenseModel
                ->select('licenses.*, plans.name as plan_name, orders.order_number')
                ->join('plans', 'plans.id = licenses.plan_id', 'left')
                ->join('orders', 'orders.id = licenses.order_id', 'left')
                ->where('licenses.user_id', $userId)
                ->where('licenses.status', 'active')
                ->where('licenses.is_trial', 0)
                ->where('licenses.expires_at >=', date('Y-m-d H:i:s'))
                ->where('licenses.expires_at <=', date('Y-m-d H:i:s', strtotime('+14 days')))
                ->orderBy('licenses.expires_at', 'ASC')
                ->findAll();

            $pendingOrders = [];
        }

        // Paket aktif (untuk user biasa)
        $plans = $planModel->getActivePlans();

        // Info rekening tujuan transfer
        $bankInfo = [
            'bank_name'       => setting('App.bankName') ?? '',
            'account_number'  => setting('App.bankAccountNumber') ?? '',
            'account_name'    => setting('App.bankAccountName') ?? '',
        ];

        $data = [
            'title'            => 'Dashboard',
            'page_title'       => 'Dashboard',
            'user'             => $user,
            'userGroups'       => $user->getGroups(),
            'orderStats'       => $orderStats,
            'activeLicenses'   => $activeLicenses,
            'expiringLicenses' => $expiringLicenses,
            'pendingOrders'    => $pendingOrders ?? [],
            'plans'            => $plans,
            'bankInfo'         => $bankInfo,
            'isManager'        => $isManager && !activeGroupCan('orders.list'),
            'totalCustomers'   => $totalCustomers ?? 0,
            'activeTrials'     => $activeTrials ?? 0,
            'recentActivity'   => $recentActivity ?? [],
        ];

        return $this->renderView('dashboard/index', $data);
    }
}
