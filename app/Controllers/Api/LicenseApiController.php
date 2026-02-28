<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\LicenseModel;

/**
 * License API Controller.
 *
 * Menyediakan endpoint untuk aplikasi external (POS / Web):
 * - POST /api/license/activate — Aktivasi lisensi (device_id opsional)
 * - POST /api/license/check    — Cek status lisensi
 */
class LicenseApiController extends ResourceController
{
    protected $format = 'json';
    protected LicenseModel $licenseModel;

    public function __construct()
    {
        $this->licenseModel = new LicenseModel();
    }

    /**
     * POST /api/license/activate
     *
     * Menerima license_key (wajib) dan device_id (opsional).
     * Jika device_id dikirim, lisensi akan di-lock ke device tersebut.
     * Jika tidak, lisensi diaktivasi tanpa device locking (cocok untuk app web).
     */
    public function activate()
    {
        $licenseKey = $this->request->getJsonVar('license_key') ?? $this->request->getPost('license_key');
        $deviceId   = $this->request->getJsonVar('device_id') ?? $this->request->getPost('device_id');

        // Validasi input — hanya license_key yang wajib
        if (empty($licenseKey)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'license_key wajib diisi.',
            ], 400);
        }

        // Cari lisensi
        $license = $this->licenseModel->findByKey($licenseKey);

        if (! $license) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Lisensi tidak ditemukan.',
            ], 404);
        }

        // Cek apakah lisensi masih aktif
        if ($license->status !== 'active') {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Lisensi tidak aktif. Status: ' . $license->status,
            ], 403);
        }

        // Cek apakah sudah expired
        if (strtotime($license->expires_at) < time()) {
            $this->licenseModel->update($license->id, ['status' => 'expired']);
            return $this->respond([
                'status'  => 'error',
                'message' => 'Lisensi sudah expired.',
            ], 403);
        }

        // Cek device locking (hanya jika device_id dikirim DAN lisensi sudah punya device)
        if (! empty($deviceId) && ! empty($license->device_id) && $license->device_id !== $deviceId) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Lisensi sudah digunakan di perangkat lain.',
            ], 403);
        }

        // Aktivasi: simpan device_id (jika ada) dan activated_at
        if (empty($license->activated_at)) {
            $updateData = [
                'activated_at' => date('Y-m-d H:i:s'),
            ];
            if (! empty($deviceId)) {
                $updateData['device_id'] = $deviceId;
            }
            $this->licenseModel->update($license->id, $updateData);
        } elseif (! empty($deviceId) && empty($license->device_id)) {
            // Lisensi sudah diaktivasi tapi belum ada device, lock sekarang
            $this->licenseModel->update($license->id, [
                'device_id' => $deviceId,
            ]);
        }

        return $this->respond([
            'status'  => 'success',
            'message' => 'Lisensi berhasil diaktivasi.',
            'data'    => [
                'license_key'  => $license->license_key,
                'plan'         => $license->plan_name,
                'device_id'    => $deviceId ?: $license->device_id,
                'activated_at' => $license->activated_at ?? date('Y-m-d H:i:s'),
                'expires_at'   => $license->expires_at,
                'status'       => 'active',
            ],
        ], 200);
    }

    /**
     * POST /api/license/check
     *
     * Menerima license_key (wajib) dan device_id (opsional).
     * Jika device_id dikirim dan lisensi sudah di-lock, akan divalidasi kecocokannya.
     */
    public function check()
    {
        $licenseKey = $this->request->getJsonVar('license_key') ?? $this->request->getPost('license_key');
        $deviceId   = $this->request->getJsonVar('device_id') ?? $this->request->getPost('device_id');

        // Validasi input — hanya license_key yang wajib
        if (empty($licenseKey)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'license_key wajib diisi.',
            ], 400);
        }

        // Cari lisensi
        $license = $this->licenseModel->findByKey($licenseKey);

        if (! $license) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Lisensi tidak ditemukan.',
            ], 404);
        }

        // Cek device_id cocok (hanya jika keduanya ada)
        if (! empty($deviceId) && ! empty($license->device_id) && $license->device_id !== $deviceId) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Device ID tidak cocok dengan lisensi ini.',
            ], 403);
        }

        // Cek expired
        $now       = time();
        $expiresAt = strtotime($license->expires_at);
        $isExpired = $expiresAt < $now;

        if ($isExpired && $license->status === 'active') {
            $this->licenseModel->update($license->id, ['status' => 'expired']);
            $license->status = 'expired';
        }

        $daysRemaining = $isExpired ? 0 : (int) ceil(($expiresAt - $now) / 86400);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Data lisensi ditemukan.',
            'data'    => [
                'license_key'    => $license->license_key,
                'plan'           => $license->plan_name,
                'device_id'      => $license->device_id,
                'activated_at'   => $license->activated_at,
                'expires_at'     => $license->expires_at,
                'status'         => $license->status,
                'is_active'      => $license->status === 'active' && ! $isExpired,
                'days_remaining' => $daysRemaining,
            ],
        ], 200);
    }
}
