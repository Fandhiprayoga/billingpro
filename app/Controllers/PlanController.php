<?php

namespace App\Controllers;

use App\Models\PlanModel;
use App\Libraries\DataTableHandler;

class PlanController extends BaseController
{
    protected PlanModel $planModel;

    public function __construct()
    {
        $this->planModel = new PlanModel();
    }

    public function index()
    {
        $data = [
            'title'      => 'Manajemen Paket',
            'page_title' => 'Daftar Paket Lisensi',
        ];

        return $this->renderView('plans/index', $data);
    }

    /**
     * AJAX DataTables endpoint untuk plans.
     */
    public function ajax()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('plans')
            ->select('plans.*');

        // Filter: status aktif
        $isActive = $this->request->getGet('is_active');
        if ($isActive !== null && $isActive !== '') {
            $builder->where('plans.is_active', (int) $isActive);
        }

        $countBuilder = clone $builder;

        $handler = new DataTableHandler($this->request);
        $result = $handler->setBuilder($builder)
            ->setCountBuilder($countBuilder)
            ->setColumnMap([
                0 => 'plans.id',
                1 => 'plans.name',
                2 => 'plans.price',
                3 => 'plans.duration_days',
                4 => 'plans.is_active',
                5 => '', // actions
            ])
            ->process();

        return $this->response->setJSON($result);
    }

    public function create()
    {
        $data = [
            'title'      => 'Tambah Paket',
            'page_title' => 'Tambah Paket Baru',
        ];

        return $this->renderView('plans/create', $data);
    }

    public function store()
    {
        $rules = [
            'name'          => 'required|min_length[3]|max_length[100]',
            'price'         => 'required|numeric|greater_than_equal_to[0]',
            'duration_days' => 'required|integer|greater_than[0]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $name = $this->request->getPost('name');
        $slug = url_title($name, '-', true);

        // Ensure unique slug
        $existingSlug = $this->planModel->where('slug', $slug)->first();
        if ($existingSlug) {
            $slug .= '-' . time();
        }

        $features = $this->request->getPost('features');
        $featuresArray = [];
        if (! empty($features)) {
            $featuresArray = array_filter(array_map('trim', explode("\n", $features)));
        }

        $this->planModel->insert([
            'name'          => $name,
            'slug'          => $slug,
            'description'   => $this->request->getPost('description'),
            'price'         => $this->request->getPost('price'),
            'duration_days' => $this->request->getPost('duration_days'),
            'features'      => json_encode($featuresArray),
            'is_active'     => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to('/admin/plans')->with('success', 'Paket berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $plan = $this->planModel->find($id);
        if (! $plan) {
            return redirect()->to('/admin/plans')->with('error', 'Paket tidak ditemukan.');
        }

        $data = [
            'title'      => 'Edit Paket',
            'page_title' => 'Edit Paket',
            'plan'       => $plan,
        ];

        return $this->renderView('plans/edit', $data);
    }

    public function update(int $id)
    {
        $plan = $this->planModel->find($id);
        if (! $plan) {
            return redirect()->to('/admin/plans')->with('error', 'Paket tidak ditemukan.');
        }

        $rules = [
            'name'          => 'required|min_length[3]|max_length[100]',
            'price'         => 'required|numeric|greater_than_equal_to[0]',
            'duration_days' => 'required|integer|greater_than[0]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $features = $this->request->getPost('features');
        $featuresArray = [];
        if (! empty($features)) {
            $featuresArray = array_filter(array_map('trim', explode("\n", $features)));
        }

        $this->planModel->update($id, [
            'name'          => $this->request->getPost('name'),
            'description'   => $this->request->getPost('description'),
            'price'         => $this->request->getPost('price'),
            'duration_days' => $this->request->getPost('duration_days'),
            'features'      => json_encode($featuresArray),
            'is_active'     => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to('/admin/plans')->with('success', 'Paket berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $plan = $this->planModel->find($id);
        if (! $plan) {
            return redirect()->to('/admin/plans')->with('error', 'Paket tidak ditemukan.');
        }

        $this->planModel->delete($id);

        return redirect()->to('/admin/plans')->with('success', 'Paket berhasil dihapus.');
    }
}
