<?php

namespace App\Controllers;

use App\Models\PlanModel;

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
            'plans'      => $this->planModel->orderBy('price', 'ASC')->findAll(),
        ];

        return $this->renderView('plans/index', $data);
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
