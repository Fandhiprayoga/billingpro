<?php

namespace App\Controllers\Canvassing;

use App\Controllers\BaseController;
use App\Models\ManagerCustomerModel;
use App\Models\ManagerActivityLogModel;
use App\Models\OrderModel;
use App\Models\PlanModel;
use App\Models\PaymentConfirmationModel;
use App\Models\LicenseModel;
use App\Libraries\Payment\PaymentService;
use App\Libraries\DataTableHandler;

class CustomerOrderController extends BaseController
{
    protected ManagerCustomerModel $mcModel;
    protected ManagerActivityLogModel $logModel;
    protected OrderModel $orderModel;
    protected PlanModel $planModel;
    protected PaymentService $paymentService;

    public function __construct()
    {
        $this->mcModel        = new ManagerCustomerModel();
        $this->logModel       = new ManagerActivityLogModel();
        $this->orderModel     = new OrderModel();
        $this->planModel      = new PlanModel();
        $this->paymentService = new PaymentService();
    }

    /**
     * List all orders from manager's customers.
     */
    public function index()
    {
        $data = [
            'title'      => 'Order Customer',
            'page_title' => 'Order Customer Saya',
        ];

        return $this->renderView('canvassing/orders/index', $data);
    }

    /**
     * AJAX DataTables for customer orders.
     */
    public function ajax()
    {
        $managerId   = auth()->id();
        $customerIds = $this->mcModel->getCustomerIds($managerId);

        if (empty($customerIds)) {
            return $this->response->setJSON([
                'draw' => (int) $this->request->getGet('draw'),
                'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [],
            ]);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('orders')
            ->select('orders.*, plans.name as plan_name, users.username')
            ->join('plans', 'plans.id = orders.plan_id', 'left')
            ->join('users', 'users.id = orders.user_id', 'left')
            ->whereIn('orders.user_id', $customerIds);

        $status = $this->request->getGet('status');
        if (! empty($status)) {
            if (str_contains($status, ',')) {
                $builder->whereIn('orders.status', explode(',', $status));
            } else {
                $builder->where('orders.status', $status);
            }
        }

        $countBuilder = clone $builder;

        $handler = new DataTableHandler($this->request);
        $result = $handler->setBuilder($builder)
            ->setCountBuilder($countBuilder)
            ->setColumnMap([
                0 => 'orders.id',
                1 => 'orders.order_number',
                2 => 'users.username',
                3 => 'plans.name',
                4 => 'orders.amount',
                5 => 'orders.unique_code',
                6 => 'orders.status',
                7 => 'orders.created_at',
                8 => '', // actions
            ])
            ->process();

        return $this->response->setJSON($result);
    }

    /**
     * Form to create an order for a customer.
     */
    public function create(int $customerId)
    {
        $managerId = auth()->id();

        if (! $this->mcModel->isCustomerOf($managerId, $customerId)) {
            return redirect()->to('/canvassing/my-customers')->with('error', 'Customer tidak ditemukan.');
        }

        $userModel = new \CodeIgniter\Shield\Models\UserModel();
        $customer  = $userModel->findById($customerId);

        if (! $customer) {
            return redirect()->to('/canvassing/my-customers')->with('error', 'Customer tidak ditemukan.');
        }

        $data = [
            'title'      => 'Buat Order Customer',
            'page_title' => 'Buat Order untuk ' . $customer->username,
            'customer'   => $customer,
            'plans'      => $this->planModel->getActivePlans(),
        ];

        return $this->renderView('canvassing/orders/create', $data);
    }

    /**
     * Store a new order for a customer.
     */
    public function store(int $customerId)
    {
        $managerId = auth()->id();

        if (! $this->mcModel->isCustomerOf($managerId, $customerId)) {
            return redirect()->to('/canvassing/my-customers')->with('error', 'Customer tidak ditemukan.');
        }

        $rules = [
            'plan_id' => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $planId = (int) $this->request->getPost('plan_id');
        $notes  = $this->request->getPost('notes');

        $result = $this->paymentService->createOrder($customerId, $planId, 'manual', $notes);

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        // Mark the order as created by manager
        $this->orderModel->update($result['data']['order_id'], [
            'created_by_manager_id' => $managerId,
        ]);

        // Log activity
        $this->logModel->logAction(
            $managerId, $customerId, 'create_order',
            $result['data']['order_id'], 'order',
            'Membuat order ' . $result['data']['order_number'] . ' untuk customer'
        );

        return redirect()->to('/canvassing/customer-orders/view/' . $result['data']['order_number'])
            ->with('success', 'Order berhasil dibuat. Nomor Order: ' . $result['data']['order_number']);
    }

    /**
     * View order detail (only if belongs to manager's customer).
     */
    public function view(string $orderNumber)
    {
        $managerId   = auth()->id();
        $customerIds = $this->mcModel->getCustomerIds($managerId);

        $order = $this->orderModel->getOrderWithDetailsByNumber($orderNumber);

        if (! $order || ! in_array($order->user_id, $customerIds)) {
            return redirect()->to('/canvassing/customer-orders')->with('error', 'Order tidak ditemukan.');
        }

        $confirmationModel = new PaymentConfirmationModel();
        $confirmations = $confirmationModel
            ->where('order_id', $order->id)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $licenseModel = new LicenseModel();
        $license = $licenseModel->where('order_id', $order->id)->first();

        $bankInfo = [
            'bank_name'       => setting('App.bankName') ?? '',
            'account_number'  => setting('App.bankAccountNumber') ?? '',
            'account_name'    => setting('App.bankAccountName') ?? '',
        ];

        $data = [
            'title'         => 'Detail Order Customer',
            'page_title'    => 'Detail Order #' . $order->order_number,
            'order'         => $order,
            'confirmations' => $confirmations,
            'license'       => $license,
            'bankInfo'      => $bankInfo,
        ];

        return $this->renderView('canvassing/orders/view', $data);
    }

    /**
     * Approve a customer order (verify payment).
     */
    public function approve(string $orderNumber)
    {
        $managerId   = auth()->id();
        $customerIds = $this->mcModel->getCustomerIds($managerId);

        $order = $this->orderModel->findByOrderNumber($orderNumber);

        if (! $order || ! in_array($order->user_id, $customerIds)) {
            return redirect()->to('/canvassing/customer-orders')->with('error', 'Order tidak ditemukan.');
        }

        $adminNotes = $this->request->getPost('admin_notes');

        $result = $this->paymentService->approveOrder($order->id, $managerId, $adminNotes);

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        // Log activity
        $this->logModel->logAction(
            $managerId, (int) $order->user_id, 'approve_order',
            $order->id, 'order',
            'Menyetujui order ' . $order->order_number . '. License Key: ' . $result['data']['license_key']
        );

        return redirect()->to('/canvassing/customer-orders/view/' . $orderNumber)
            ->with('success', $result['message'] . ' License Key: ' . $result['data']['license_key']);
    }

    /**
     * Reject a customer order.
     */
    public function reject(string $orderNumber)
    {
        $managerId   = auth()->id();
        $customerIds = $this->mcModel->getCustomerIds($managerId);

        $order = $this->orderModel->findByOrderNumber($orderNumber);

        if (! $order || ! in_array($order->user_id, $customerIds)) {
            return redirect()->to('/canvassing/customer-orders')->with('error', 'Order tidak ditemukan.');
        }

        $reason = $this->request->getPost('reason');

        $result = $this->paymentService->rejectOrder($order->id, $managerId, $reason);

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        // Log activity
        $this->logModel->logAction(
            $managerId, (int) $order->user_id, 'reject_order',
            $order->id, 'order',
            'Menolak order ' . $order->order_number . '. Alasan: ' . ($reason ?? '-')
        );

        return redirect()->to('/canvassing/customer-orders/view/' . $orderNumber)
            ->with('success', $result['message']);
    }
}
