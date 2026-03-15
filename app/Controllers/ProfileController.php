<?php

namespace App\Controllers;

use App\Models\CustomerProfileModel;

class ProfileController extends BaseController
{
    protected CustomerProfileModel $profileModel;

    public function __construct()
    {
        $this->profileModel = new CustomerProfileModel();
    }

    public function index()
    {
        $user    = auth()->user();
        $profile = $this->profileModel->getByUserId($user->id);

        $data = [
            'title'      => 'Profil Saya',
            'page_title' => 'Profil',
            'user'       => $user,
            'userGroups' => $user->getGroups(),
            'profile'    => $profile,
        ];

        return $this->renderView('profile/index', $data);
    }

    public function update()
    {
        $user = auth()->user();

        $rules = [
            'username' => 'required|min_length[3]|max_length[30]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $user->username = $this->request->getPost('username');

        // Update password jika diisi
        $password = $this->request->getPost('password');
        if (! empty($password)) {
            $user->password = $password;
        }

        $users = auth()->getProvider();
        $users->save($user);

        // Update customer profile
        $this->profileModel->saveProfile($user->id, [
            'nama_usaha' => $this->request->getPost('nama_usaha'),
            'no_telp'    => $this->request->getPost('no_telp'),
            'propinsi'   => $this->request->getPost('propinsi'),
            'kabupaten'  => $this->request->getPost('kabupaten'),
        ]);

        return redirect()->to('/profile')->with('success', 'Profil berhasil diperbarui.');
    }
}
