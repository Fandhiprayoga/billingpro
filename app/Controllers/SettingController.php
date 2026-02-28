<?php

namespace App\Controllers;

class SettingController extends BaseController
{
    /**
     * Default setting values
     */
    private array $defaults = [
        'App.siteName'        => 'CI4 Shield RBAC',
        'App.siteNameShort'   => 'C4',
        'App.siteDescription' => 'Boilerplate CodeIgniter 4 dengan Shield RBAC',
        'App.siteFooter'      => 'CI4 Shield RBAC Boilerplate',
        'App.siteVersion'     => '1.0.0',
        'App.favicon'         => '',
        'App.loginLogo'       => '',
        'App.maintenanceMode' => '0',
        'App.maintenanceMsg'  => 'Sistem sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.',
        'App.defaultRole'     => 'user',
        'App.allowRegistration' => '1',
        'Mail.protocol'       => 'smtp',
        'Mail.hostname'       => '',
        'Mail.port'           => '587',
        'Mail.username'       => '',
        'Mail.password'       => '',
        'Mail.encryption'     => 'tls',
        'Mail.fromEmail'      => 'noreply@example.com',
        'Mail.fromName'       => 'CI4 RBAC',
    ];

    /**
     * Halaman pengaturan — tab-based
     */
    public function index()
    {
        $activeTab = $this->request->getGet('tab') ?? 'general';

        $authGroups = config('AuthGroups');

        $data = [
            'title'      => 'Pengaturan',
            'page_title' => 'Pengaturan Sistem',
            'activeTab'  => $activeTab,
            'groups'     => $authGroups->groups,
            'settings'   => $this->getAllSettings(),
        ];

        return $this->renderView('settings/index', $data);
    }

    /**
     * Update pengaturan umum
     */
    public function updateGeneral()
    {
        $rules = [
            'site_name'        => 'required|max_length[100]',
            'site_name_short'  => 'permit_empty|max_length[10]',
            'site_description' => 'permit_empty|max_length[255]',
            'site_footer'      => 'permit_empty|max_length[100]',
            'site_version'     => 'permit_empty|max_length[20]',
            'favicon'          => 'permit_empty|uploaded[favicon]|max_size[favicon,512]|ext_in[favicon,ico,png,svg]',
            'login_logo'       => 'permit_empty|uploaded[login_logo]|max_size[login_logo,2048]|ext_in[login_logo,png,jpg,jpeg,svg,webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        setting('App.siteName', $this->request->getPost('site_name'));
        setting('App.siteNameShort', $this->request->getPost('site_name_short'));
        setting('App.siteDescription', $this->request->getPost('site_description'));
        setting('App.siteFooter', $this->request->getPost('site_footer'));
        setting('App.siteVersion', $this->request->getPost('site_version'));

        // Handle favicon upload
        $favicon = $this->request->getFile('favicon');
        if ($favicon && $favicon->isValid() && ! $favicon->hasMoved()) {
            $uploadPath = WRITEPATH . 'uploads/branding';
            // Hapus file lama
            $oldFavicon = setting('App.favicon');
            if ($oldFavicon && file_exists(WRITEPATH . 'uploads/' . $oldFavicon)) {
                unlink(WRITEPATH . 'uploads/' . $oldFavicon);
            }
            $newName = 'favicon_' . time() . '.' . $favicon->getExtension();
            $favicon->move($uploadPath, $newName);
            setting('App.favicon', 'branding/' . $newName);
        }

        // Handle login logo upload
        $logo = $this->request->getFile('login_logo');
        if ($logo && $logo->isValid() && ! $logo->hasMoved()) {
            $uploadPath = WRITEPATH . 'uploads/branding';
            // Hapus file lama
            $oldLogo = setting('App.loginLogo');
            if ($oldLogo && file_exists(WRITEPATH . 'uploads/' . $oldLogo)) {
                unlink(WRITEPATH . 'uploads/' . $oldLogo);
            }
            $newName = 'login_logo_' . time() . '.' . $logo->getExtension();
            $logo->move($uploadPath, $newName);
            setting('App.loginLogo', 'branding/' . $newName);
        }

        return redirect()->to('/admin/settings?tab=general')->with('success', 'Pengaturan umum berhasil diperbarui.');
    }

    /**
     * Update pengaturan autentikasi
     */
    public function updateAuth()
    {
        $rules = [
            'default_role'       => 'required',
            'allow_registration' => 'permit_empty',
            'maintenance_mode'   => 'permit_empty',
            'maintenance_msg'    => 'permit_empty|max_length[500]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        setting('App.defaultRole', $this->request->getPost('default_role'));
        setting('App.allowRegistration', $this->request->getPost('allow_registration') ? '1' : '0');
        setting('App.maintenanceMode', $this->request->getPost('maintenance_mode') ? '1' : '0');
        setting('App.maintenanceMsg', $this->request->getPost('maintenance_msg') ?? '');

        return redirect()->to('/admin/settings?tab=auth')->with('success', 'Pengaturan autentikasi berhasil diperbarui.');
    }

    /**
     * Update pengaturan email
     */
    public function updateMail()
    {
        $rules = [
            'mail_protocol'   => 'required|in_list[smtp,sendmail,mail]',
            'mail_hostname'   => 'permit_empty|max_length[255]',
            'mail_port'       => 'permit_empty|numeric',
            'mail_username'   => 'permit_empty|max_length[255]',
            'mail_password'   => 'permit_empty|max_length[255]',
            'mail_encryption' => 'required|in_list[tls,ssl,none]',
            'mail_from_email' => 'permit_empty|valid_email',
            'mail_from_name'  => 'permit_empty|max_length[100]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        setting('Mail.protocol', $this->request->getPost('mail_protocol'));
        setting('Mail.hostname', $this->request->getPost('mail_hostname') ?? '');
        setting('Mail.port', $this->request->getPost('mail_port') ?? '587');
        setting('Mail.username', $this->request->getPost('mail_username') ?? '');
        setting('Mail.encryption', $this->request->getPost('mail_encryption'));
        setting('Mail.fromEmail', $this->request->getPost('mail_from_email') ?? '');
        setting('Mail.fromName', $this->request->getPost('mail_from_name') ?? '');

        // Password hanya di-update jika diisi
        $password = $this->request->getPost('mail_password');
        if (! empty($password)) {
            setting('Mail.password', $password);
        }

        return redirect()->to('/admin/settings?tab=mail')->with('success', 'Pengaturan email berhasil diperbarui.');
    }

    /**
     * Ambil semua settings, gunakan default jika belum ada di DB
     */
    private function getAllSettings(): array
    {
        $result = [];

        foreach ($this->defaults as $key => $default) {
            $value = setting($key);
            $result[$key] = $value ?? $default;
        }

        return $result;
    }
}
