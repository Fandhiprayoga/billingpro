<?php

namespace App\Controllers\Canvassing;

use App\Controllers\BaseController;
use App\Models\ManagerCustomerModel;
use App\Models\ManagerActivityLogModel;
use App\Libraries\DataTableHandler;

class AssignController extends BaseController
{
    protected ManagerCustomerModel $mcModel;

    public function __construct()
    {
        $this->mcModel = new ManagerCustomerModel();
    }

    public function index()
    {
        $managers        = $this->mcModel->getAvailableManagers();
        $unassignedUsers = $this->mcModel->getUnassignedUsers();

        $data = [
            'title'           => 'Assign Customer',
            'page_title'      => 'Assign Customer ke Manager',
            'managers'        => $managers,
            'unassignedUsers' => $unassignedUsers,
        ];

        return $this->renderView('canvassing/assign/index', $data);
    }

    public function ajax()
    {
        $db = \Config\Database::connect();

        $builder = $db->table('manager_customers')
            ->select('manager_customers.id, manager_customers.assigned_at, manager_customers.status,
                       m.username as manager_username, c.username as customer_username,
                       ai.secret as customer_email')
            ->join('users m', 'm.id = manager_customers.manager_id')
            ->join('users c', 'c.id = manager_customers.customer_id')
            ->join('auth_identities ai', 'ai.user_id = c.id AND ai.type = \'email_password\'', 'left');

        $filterManager = $this->request->getGet('manager_id');
        if (! empty($filterManager)) {
            $builder->where('manager_customers.manager_id', (int) $filterManager);
        }

        $filterStatus = $this->request->getGet('status');
        if (! empty($filterStatus)) {
            $builder->where('manager_customers.status', $filterStatus);
        } else {
            $builder->where('manager_customers.status', 'active');
        }

        $countBuilder = clone $builder;

        $handler = new DataTableHandler($this->request);
        $result = $handler->setBuilder($builder)
            ->setCountBuilder($countBuilder)
            ->setColumnMap([
                0 => 'manager_customers.id',
                1 => 'm.username',
                2 => 'c.username',
                3 => 'ai.secret',
                4 => 'manager_customers.assigned_at',
                5 => 'manager_customers.status',
                6 => '', // actions
            ])
            ->process();

        return $this->response->setJSON($result);
    }

    public function store()
    {
        $rules = [
            'manager_id'  => 'required|integer',
            'customer_id' => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $managerId  = (int) $this->request->getPost('manager_id');
        $customerId = (int) $this->request->getPost('customer_id');

        // Check if already assigned
        $existing = $this->mcModel
            ->where('manager_id', $managerId)
            ->where('customer_id', $customerId)
            ->first();

        if ($existing) {
            if ($existing->status === 'active') {
                return redirect()->back()->with('error', 'Customer sudah di-assign ke manager ini.');
            }
            // Reactivate
            $this->mcModel->update($existing->id, [
                'status'      => 'active',
                'assigned_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $this->mcModel->insert([
                'manager_id'  => $managerId,
                'customer_id' => $customerId,
                'assigned_at' => date('Y-m-d H:i:s'),
                'status'      => 'active',
            ]);
        }

        // Log activity
        $logModel = new ManagerActivityLogModel();
        $logModel->logAction($managerId, $customerId, 'assign_customer', null, null, 'Customer di-assign oleh admin');

        return redirect()->to('/admin/canvassing-assign')->with('success', 'Customer berhasil di-assign ke manager.');
    }

    public function remove(int $id)
    {
        $record = $this->mcModel->find($id);

        if (! $record) {
            return redirect()->to('/admin/canvassing-assign')->with('error', 'Data tidak ditemukan.');
        }

        $this->mcModel->update($id, ['status' => 'inactive']);

        $logModel = new ManagerActivityLogModel();
        $logModel->logAction(
            (int) $record->manager_id, (int) $record->customer_id,
            'unassign_customer', null, null, 'Customer di-unassign oleh admin'
        );

        return redirect()->to('/admin/canvassing-assign')->with('success', 'Customer berhasil di-unassign.');
    }
}
