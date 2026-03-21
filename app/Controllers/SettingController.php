<?php

namespace App\Controllers;

use App\Models\LicenseModel;
use App\Models\ManagerActivityLogModel;
use App\Models\ManagerCustomerModel;
use App\Models\OrderModel;
use App\Models\PaymentConfirmationModel;

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
        'App.bankName'        => '',
        'App.bankAccountNumber' => '',
        'App.bankAccountName' => '',
        'App.maintenanceMode' => '0',
        'App.maintenanceMsg'  => 'Sistem sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.',
        'AuthGroups.defaultGroup' => 'user',
        'Auth.allowRegistration'  => true,
        'Email.protocol'      => 'smtp',
        'Email.SMTPHost'      => '',
        'Email.SMTPPort'      => 587,
        'Email.SMTPUser'      => '',
        'Email.SMTPPass'      => '',
        'Email.SMTPCrypto'    => 'tls',
        'Email.fromEmail'     => 'noreply@example.com',
        'Email.fromName'      => 'CI4 RBAC',
    ];

    /**
     * Halaman pengaturan — tab-based
     */
    public function index()
    {
        $activeTab = $this->request->getGet('tab') ?? 'general';

        $authGroups = config('AuthGroups');

        $data = [
            'title'            => 'Pengaturan',
            'page_title'       => 'Pengaturan Sistem',
            'activeTab'        => $activeTab,
            'groups'           => $authGroups->groups,
            'settings'         => $this->getAllSettings(),
            'storageInfo'      => $this->getStorageInfo(),
            'proofFiles'       => $this->getPaymentProofFiles(),
            'transactionStats' => $this->getTransactionStats(),
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
            'favicon'             => 'permit_empty|uploaded[favicon]|max_size[favicon,512]|ext_in[favicon,ico,png,svg]',
            'login_logo'          => 'permit_empty|uploaded[login_logo]|max_size[login_logo,2048]|ext_in[login_logo,png,jpg,jpeg,svg,webp]',
            'bank_name'           => 'permit_empty|max_length[100]',
            'bank_account_number' => 'permit_empty|max_length[50]',
            'bank_account_name'   => 'permit_empty|max_length[100]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        setting('App.siteName', $this->request->getPost('site_name'));
        setting('App.siteNameShort', $this->request->getPost('site_name_short'));
        setting('App.siteDescription', $this->request->getPost('site_description'));
        setting('App.siteFooter', $this->request->getPost('site_footer'));
        setting('App.siteVersion', $this->request->getPost('site_version'));

        // Payment destination settings
        setting('App.bankName', $this->request->getPost('bank_name') ?? '');
        setting('App.bankAccountNumber', $this->request->getPost('bank_account_number') ?? '');
        setting('App.bankAccountName', $this->request->getPost('bank_account_name') ?? '');

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

        setting('AuthGroups.defaultGroup', $this->request->getPost('default_role'));
        setting('Auth.allowRegistration', (bool) $this->request->getPost('allow_registration'));
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

        setting('Email.protocol', $this->request->getPost('mail_protocol'));
        setting('Email.SMTPHost', $this->request->getPost('mail_hostname') ?? '');
        setting('Email.SMTPPort', (int) ($this->request->getPost('mail_port') ?? 587));
        setting('Email.SMTPUser', $this->request->getPost('mail_username') ?? '');
        setting('Email.SMTPCrypto', $this->request->getPost('mail_encryption') === 'none' ? '' : $this->request->getPost('mail_encryption'));
        setting('Email.fromEmail', $this->request->getPost('mail_from_email') ?? '');
        setting('Email.fromName', $this->request->getPost('mail_from_name') ?? '');

        // Password hanya di-update jika diisi
        $password = $this->request->getPost('mail_password');
        if (! empty($password)) {
            setting('Email.SMTPPass', $password);
        }

        return redirect()->to('/admin/settings?tab=mail')->with('success', 'Pengaturan email berhasil diperbarui.');
    }

    /**
     * Test kirim email dengan konfigurasi yang tersimpan
     */
    public function testMail()
    {
        $rules = [
            'to'      => 'required|valid_email',
            'subject' => 'permit_empty|max_length[255]',
            'message' => 'permit_empty|max_length[2000]',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => implode(' ', $this->validator->getErrors()),
            ]);
        }

        $to      = $this->request->getPost('to');
        $subject = $this->request->getPost('subject') ?: 'Test Email';
        $body    = $this->request->getPost('message') ?: 'Ini adalah email percobaan.';

        $fromEmail = setting('Email.fromEmail') ?: 'noreply@example.com';
        $fromName  = setting('Email.fromName') ?: 'CI4 RBAC';

        helper('email');
        $email = emailer(['mailType' => 'html']);
        $email->setFrom($fromEmail, $fromName);
        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage($body);

        if ($email->send(false)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Email berhasil dikirim ke ' . esc($to),
            ]);
        }

        $debugMessage = $email->printDebugger(['headers', 'subject', 'body']);
        log_message('error', 'Test email failed: ' . $debugMessage);

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Gagal mengirim email. Periksa konfigurasi SMTP Anda.',
        ]);
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

    /**
     * Hapus branding (favicon / login logo) dan kembalikan ke default.
     */
    public function deleteBranding(string $type)
    {
        $allowed = [
            'favicon'    => 'App.favicon',
            'login_logo' => 'App.loginLogo',
        ];

        if (! isset($allowed[$type])) {
            return redirect()->to('/admin/settings?tab=general')->with('error', 'Tipe branding tidak valid.');
        }

        $settingKey = $allowed[$type];
        $currentPath = setting($settingKey);

        // Hapus file fisik jika ada
        if ($currentPath && file_exists(WRITEPATH . 'uploads/' . $currentPath)) {
            unlink(WRITEPATH . 'uploads/' . $currentPath);
        }

        // Reset setting ke kosong (default)
        setting($settingKey, '');

        $label = $type === 'favicon' ? 'Favicon' : 'Logo halaman login';

        return redirect()->to('/admin/settings?tab=general')->with('success', "{$label} berhasil dihapus dan dikembalikan ke default.");
    }

    // ================================================================
    // WAREHOUSING — Storage & Cleanup Methods
    // ================================================================

    /**
     * Informasi penggunaan storage per direktori di writable/
     */
    private function getStorageInfo(): array
    {
        $dirs = [
            'payment_proofs' => [
                'label' => 'Bukti Pembayaran',
                'path'  => WRITEPATH . 'uploads/payment_proofs',
                'icon'  => 'fa-file-image',
            ],
            'branding' => [
                'label' => 'Branding (Favicon & Logo)',
                'path'  => WRITEPATH . 'uploads/branding',
                'icon'  => 'fa-palette',
            ],
            'logs' => [
                'label' => 'Log Aplikasi',
                'path'  => WRITEPATH . 'logs',
                'icon'  => 'fa-file-alt',
            ],
            'sessions' => [
                'label' => 'Session Files',
                'path'  => WRITEPATH . 'session',
                'icon'  => 'fa-clock',
            ],
            'debugbar' => [
                'label' => 'Debug Bar',
                'path'  => WRITEPATH . 'debugbar',
                'icon'  => 'fa-bug',
            ],
            'cache' => [
                'label' => 'Cache',
                'path'  => WRITEPATH . 'cache',
                'icon'  => 'fa-database',
            ],
        ];

        $result = [];
        foreach ($dirs as $key => $info) {
            $fileCount = 0;
            $totalSize = 0;

            if (is_dir($info['path'])) {
                $iterator = new \DirectoryIterator($info['path']);
                foreach ($iterator as $file) {
                    if ($file->isDot() || $file->getFilename() === 'index.html' || $file->getFilename() === '.gitkeep') {
                        continue;
                    }
                    if ($file->isFile()) {
                        $fileCount++;
                        $totalSize += $file->getSize();
                    }
                }
            }

            $result[$key] = [
                'label'     => $info['label'],
                'icon'      => $info['icon'],
                'path'      => $info['path'],
                'fileCount' => $fileCount,
                'totalSize' => $totalSize,
                'sizeHuman' => $this->formatBytes($totalSize),
            ];
        }

        return $result;
    }

    /**
     * List file bukti pembayaran dengan info order terkait
     */
    private function getPaymentProofFiles(): array
    {
        $proofDir = WRITEPATH . 'uploads/payment_proofs';
        if (! is_dir($proofDir)) {
            return [];
        }

        // Ambil data dari DB untuk cross-reference
        $confirmationModel = new PaymentConfirmationModel();
        $db = \Config\Database::connect();
        $confirmations = $db->table('payment_confirmations pc')
            ->select('pc.proof_image, pc.status AS confirmation_status, pc.created_at AS uploaded_at, o.order_number, o.status AS order_status')
            ->join('orders o', 'o.id = pc.order_id', 'left')
            ->get()
            ->getResultArray();

        // Index by filename
        $dbIndex = [];
        foreach ($confirmations as $row) {
            $filename = basename($row['proof_image'] ?? '');
            if ($filename) {
                $dbIndex[$filename] = $row;
            }
        }

        $files = [];
        $iterator = new \DirectoryIterator($proofDir);
        foreach ($iterator as $file) {
            if ($file->isDot() || $file->getFilename() === 'index.html') {
                continue;
            }
            if (! $file->isFile()) {
                continue;
            }

            $filename = $file->getFilename();
            $dbInfo = $dbIndex[$filename] ?? null;

            $orderStatus = $dbInfo['order_status'] ?? null;
            $isDeletable = $dbInfo === null  // orphaned
                || in_array($orderStatus, ['paid', 'cancelled', 'expired'], true);

            $files[] = [
                'filename'     => $filename,
                'size'         => $file->getSize(),
                'sizeHuman'    => $this->formatBytes($file->getSize()),
                'modified'     => $file->getMTime(),
                'modifiedDate' => date('Y-m-d H:i', $file->getMTime()),
                'orderNumber'  => $dbInfo['order_number'] ?? null,
                'orderStatus'  => $orderStatus,
                'confirmationStatus' => $dbInfo['confirmation_status'] ?? null,
                'isDeletable'  => $isDeletable,
                'isOrphaned'   => $dbInfo === null,
            ];
        }

        // Sort oldest first
        usort($files, fn($a, $b) => $a['modified'] <=> $b['modified']);

        return $files;
    }

    /**
     * Hapus file bukti pembayaran yang dipilih
     */
    public function deletePaymentProofs()
    {
        $filenames = $this->request->getPost('filenames');
        if (empty($filenames) || ! is_array($filenames)) {
            return redirect()->to('/admin/settings?tab=storage')->with('error', 'Tidak ada file yang dipilih.');
        }

        $proofDir = WRITEPATH . 'uploads/payment_proofs';
        $deleted = 0;
        $skipped = 0;

        $db = \Config\Database::connect();

        foreach ($filenames as $filename) {
            // Sanitize: basename only, no path traversal
            $safe = basename($filename);
            if ($safe !== $filename || $safe === 'index.html') {
                $skipped++;
                continue;
            }

            $fullPath = $proofDir . DIRECTORY_SEPARATOR . $safe;
            if (! file_exists($fullPath)) {
                $skipped++;
                continue;
            }

            // Delete physical file
            if (unlink($fullPath)) {
                // Clear proof_image in DB
                $relativePath = 'payment_proofs/' . $safe;
                $db->table('payment_confirmations')
                    ->where('proof_image', $relativePath)
                    ->update(['proof_image' => '']);

                $deleted++;
            } else {
                $skipped++;
            }
        }

        log_message('info', "Warehousing: Admin #{$this->getAdminId()} deleted {$deleted} payment proof file(s). Skipped: {$skipped}.");

        $msg = "{$deleted} file berhasil dihapus.";
        if ($skipped > 0) {
            $msg .= " {$skipped} file dilewati.";
        }

        return redirect()->to('/admin/settings?tab=storage')->with('success', $msg);
    }

    /**
     * Cleanup direktori sistem (logs, sessions, debugbar, cache)
     */
    public function cleanupDirectory(string $target)
    {
        $targets = [
            'logs'     => ['path' => WRITEPATH . 'logs',     'label' => 'Log Aplikasi',   'maxAge' => 30 * 86400],
            'sessions' => ['path' => WRITEPATH . 'session',  'label' => 'Session Files',   'maxAge' => 7 * 86400],
            'debugbar' => ['path' => WRITEPATH . 'debugbar', 'label' => 'Debug Bar',       'maxAge' => 3 * 86400],
            'cache'    => ['path' => WRITEPATH . 'cache',    'label' => 'Cache',            'maxAge' => 0],
        ];

        if (! isset($targets[$target])) {
            return redirect()->to('/admin/settings?tab=storage')->with('error', 'Target cleanup tidak valid.');
        }

        $config  = $targets[$target];
        $dir     = $config['path'];
        $maxAge  = $config['maxAge'];
        $cutoff  = $maxAge > 0 ? time() - $maxAge : PHP_INT_MAX; // maxAge=0 means delete all
        $deleted = 0;

        if (is_dir($dir)) {
            $iterator = new \DirectoryIterator($dir);
            foreach ($iterator as $file) {
                if ($file->isDot() || $file->getFilename() === 'index.html' || $file->getFilename() === '.gitkeep') {
                    continue;
                }
                if (! $file->isFile()) {
                    continue;
                }

                if ($maxAge === 0 || $file->getMTime() < $cutoff) {
                    if (unlink($file->getPathname())) {
                        $deleted++;
                    }
                }
            }
        }

        $label = $config['label'];
        $ageLabel = $maxAge > 0 ? ' (lebih dari ' . ($maxAge / 86400) . ' hari)' : '';
        log_message('info', "Warehousing: Admin #{$this->getAdminId()} cleaned up {$deleted} {$label} file(s){$ageLabel}.");

        return redirect()->to('/admin/settings?tab=storage')->with('success', "{$deleted} file {$label} berhasil dihapus{$ageLabel}.");
    }

    // ================================================================
    // WAREHOUSING — Transaction Data Reset
    // ================================================================

    /**
     * Statistik jumlah record per tabel transaksi
     */
    private function getTransactionStats(): array
    {
        $db = \Config\Database::connect();

        return [
            'orders' => [
                'label'   => 'Orders',
                'desc'    => 'Data pesanan/order. Reset akan menghapus juga konfirmasi pembayaran & file bukti bayar.',
                'count'   => $db->table('orders')->countAllResults(),
                'icon'    => 'fa-shopping-cart',
                'color'   => 'danger',
            ],
            'payment_confirmations' => [
                'label'   => 'Konfirmasi Pembayaran',
                'desc'    => 'Data konfirmasi pembayaran manual. Reset akan menghapus file bukti bayar di server.',
                'count'   => $db->table('payment_confirmations')->countAllResults(),
                'icon'    => 'fa-receipt',
                'color'   => 'warning',
            ],
            'licenses' => [
                'label'   => 'Lisensi',
                'desc'    => 'Data lisensi (termasuk trial). Semua lisensi akan dihapus permanen.',
                'count'   => $db->table('licenses')->countAllResults(),
                'icon'    => 'fa-key',
                'color'   => 'info',
            ],
            'activity_logs' => [
                'label'   => 'Log Aktivitas Manager',
                'desc'    => 'Catatan aktivitas canvassing manager.',
                'count'   => $db->table('manager_activity_logs')->countAllResults(),
                'icon'    => 'fa-history',
                'color'   => 'secondary',
            ],
            'manager_assignments' => [
                'label'   => 'Penugasan Manager-Customer',
                'desc'    => 'Data penugasan manager ke customer.',
                'count'   => $db->table('manager_customers')->countAllResults(),
                'icon'    => 'fa-users',
                'color'   => 'dark',
            ],
        ];
    }

    /**
     * Reset data transaksi per tabel — memerlukan konfirmasi password admin
     */
    public function resetTransactionData()
    {
        $target   = $this->request->getPost('target');
        $password = $this->request->getPost('password');

        // Validasi input
        if (empty($target) || empty($password)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Target dan password wajib diisi.']);
        }

        // Verifikasi password admin
        $user = auth()->user();
        $passwords = service('passwords');
        if (! $passwords->verify($password, $user->password_hash)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Password salah. Silakan coba lagi.']);
        }

        $db = \Config\Database::connect();
        $deleted = 0;

        try {
            $db->transStart();

            switch ($target) {
                case 'orders':
                    // Delete proof files first
                    $this->deleteAllProofFiles();
                    // Clear related activity logs
                    $db->table('manager_activity_logs')
                        ->whereIn('reference_type', ['order', 'payment_confirmation'])
                        ->delete();
                    $deleted += $db->table('payment_confirmations')->countAllResults();
                    $db->table('payment_confirmations')->truncate();
                    $deleted += $db->table('orders')->countAllResults();
                    $db->table('orders')->truncate();
                    break;

                case 'payment_confirmations':
                    $this->deleteAllProofFiles();
                    $db->table('manager_activity_logs')
                        ->where('reference_type', 'payment_confirmation')
                        ->delete();
                    $deleted = $db->table('payment_confirmations')->countAllResults();
                    $db->table('payment_confirmations')->truncate();
                    break;

                case 'licenses':
                    $db->table('manager_activity_logs')
                        ->where('reference_type', 'license')
                        ->delete();
                    $deleted = $db->table('licenses')->countAllResults();
                    $db->table('licenses')->truncate();
                    break;

                case 'activity_logs':
                    $deleted = $db->table('manager_activity_logs')->countAllResults();
                    $db->table('manager_activity_logs')->truncate();
                    break;

                case 'manager_assignments':
                    $deleted = $db->table('manager_customers')->countAllResults();
                    $db->table('manager_customers')->truncate();
                    break;

                default:
                    return $this->response->setJSON(['success' => false, 'message' => 'Target reset tidak valid.']);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON(['success' => false, 'message' => 'Gagal mereset data. Transaksi database dibatalkan.']);
            }

            $targetLabels = [
                'orders'                 => 'Orders + Konfirmasi Pembayaran',
                'payment_confirmations'  => 'Konfirmasi Pembayaran',
                'licenses'               => 'Lisensi',
                'activity_logs'          => 'Log Aktivitas Manager',
                'manager_assignments'    => 'Penugasan Manager-Customer',
            ];

            $label = $targetLabels[$target] ?? $target;
            log_message('notice', "RESET DATA: Admin #{$this->getAdminId()} ({$user->username}) reset '{$label}'. {$deleted} record(s) dihapus.");

            return $this->response->setJSON([
                'success' => true,
                'message' => "Berhasil mereset data {$label}. {$deleted} record dihapus.",
                'deleted' => $deleted,
            ]);
        } catch (\Throwable $e) {
            log_message('error', "Reset data gagal: " . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Terjadi kesalahan saat mereset data.']);
        }
    }

    /**
     * Hapus semua file bukti pembayaran dari disk
     */
    private function deleteAllProofFiles(): void
    {
        $proofDir = WRITEPATH . 'uploads/payment_proofs';
        if (! is_dir($proofDir)) {
            return;
        }

        $iterator = new \DirectoryIterator($proofDir);
        foreach ($iterator as $file) {
            if ($file->isDot() || $file->getFilename() === 'index.html') {
                continue;
            }
            if ($file->isFile()) {
                unlink($file->getPathname());
            }
        }
    }

    /**
     * Format bytes ke human-readable string
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen((string) $bytes) - 1) / 3);
        $factor = min($factor, count($units) - 1);

        return round($bytes / pow(1024, $factor), $precision) . ' ' . $units[$factor];
    }

    /**
     * Get current admin user ID
     */
    private function getAdminId(): int
    {
        return (int) auth()->id();
    }
}
