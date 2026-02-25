<?php

namespace App\Controllers;

use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Entities\User;

class UserController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Daftar semua users
     */
    public function index()
    {
        $users = $this->userModel->findAll();

        // Tambahkan info group untuk setiap user
        foreach ($users as $user) {
            $user->groups = $user->getGroups();
        }

        $data = [
            'title'      => 'Manajemen User',
            'page_title' => 'Daftar User',
            'users'      => $users,
        ];

        return $this->renderView('users/index', $data);
    }

    /**
     * Form tambah user baru
     */
    public function create()
    {
        $authGroups = config('AuthGroups');

        $data = [
            'title'      => 'Tambah User',
            'page_title' => 'Tambah User Baru',
            'groups'     => $authGroups->groups,
        ];

        return $this->renderView('users/create', $data);
    }

    /**
     * Simpan user baru
     */
    public function store()
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username]',
            'email'    => 'required|valid_email|is_unique[auth_identities.secret]',
            'password' => 'required|min_length[8]',
            'groups'   => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $users = auth()->getProvider();

        $user = new User([
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'active'   => 1,
        ]);

        $users->save($user);
        $user = $users->findById($users->getInsertID());

        // Assign groups/roles (multi-group support)
        $groups = $this->request->getPost('groups');
        if (is_array($groups)) {
            foreach ($groups as $group) {
                $user->addGroup($group);
            }
        } else {
            $user->addGroup($groups);
        }

        return redirect()->to('/admin/users')->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Form edit user
     */
    public function edit(int $id)
    {
        $user = $this->userModel->findById($id);

        if (! $user) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan.');
        }

        $authGroups = config('AuthGroups');

        $data = [
            'title'      => 'Edit User',
            'page_title' => 'Edit User',
            'user_edit'  => $user,
            'groups'     => $authGroups->groups,
            'userGroups' => $user->getGroups(),
        ];

        return $this->renderView('users/edit', $data);
    }

    /**
     * Update user
     */
    public function update(int $id)
    {
        $user = $this->userModel->findById($id);

        if (! $user) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan.');
        }

        $rules = [
            'username' => "required|min_length[3]|max_length[30]",
            'email'    => "required|valid_email",
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $user->username = $this->request->getPost('username');
        $user->email    = $this->request->getPost('email');

        // Update password jika diisi
        $password = $this->request->getPost('password');
        if (! empty($password)) {
            $user->password = $password;
        }

        $this->userModel->save($user);

        // Update groups jika ada (multi-group support)
        $groups = $this->request->getPost('groups');
        if (! empty($groups)) {
            // Hapus semua group lama
            foreach ($user->getGroups() as $oldGroup) {
                $user->removeGroup($oldGroup);
            }
            // Assign semua group baru
            if (is_array($groups)) {
                foreach ($groups as $group) {
                    $user->addGroup($group);
                }
            } else {
                $user->addGroup($groups);
            }
        }

        return redirect()->to('/admin/users')->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Hapus user
     */
    public function delete(int $id)
    {
        $user = $this->userModel->findById($id);

        if (! $user) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan.');
        }

        // Jangan bisa hapus diri sendiri
        if ($user->id === auth()->id()) {
            return redirect()->to('/admin/users')->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        $this->userModel->delete($id, true);

        return redirect()->to('/admin/users')->with('success', 'User berhasil dihapus.');
    }

    /**
     * Assign role ke user
     */
    public function assignRole(int $id)
    {
        $user = $this->userModel->findById($id);

        if (! $user) {
            return redirect()->to('/admin/users')->with('error', 'User tidak ditemukan.');
        }

        $groups = $this->request->getPost('groups');

        // Hapus semua group lama
        foreach ($user->getGroups() as $oldGroup) {
            $user->removeGroup($oldGroup);
        }

        // Assign groups baru (multi-group support)
        if (is_array($groups)) {
            foreach ($groups as $group) {
                $user->addGroup($group);
            }
        } else {
            $user->addGroup($groups);
        }

        return redirect()->to('/admin/users')->with('success', 'Role user berhasil diperbarui.');
    }
}
