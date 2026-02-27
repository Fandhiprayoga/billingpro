<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\LicenseModel;

/**
 * License API Controller.
 *
 * Menyediakan endpoint untuk POS external:
 * - POST /api/license/activate — Aktivasi lisensi dengan device locking
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
     * Menerima license_key dan device_id.
     * Jika lisensi valid dan belum dipakai, simpan device_id (locking).
     */
    public function activate()
    {
        $licenseKey = $this->request->getJsonVar('license_key') ?? $this->request->getPost('license_key');
        $deviceId   = $this->request->getJsonVar('device_id') ?? $this->request->getPost('device_id');

        // Validasi input
        if (empty($licenseKey) || empty($deviceId)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'license_key dan device_id wajib diisi.',
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

        // Cek apakah sudah di-lock ke device lain
        if (! empty($license->device_id) && $license->device_id !== $deviceId) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Lisensi sudah digunakan di perangkat lain.',
            ], 403);
        }

        // Jika belum di-activate, simpan device_id
        if (empty($license->device_id)) {
            $this->licenseModel->update($license->id, [
                'device_id'    => $deviceId,
                'activated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $this->respond([
            'status'  => 'success',
            'message' => 'Lisensi berhasil diaktivasi.',
            'data'    => [
                'license_key'  => $license->license_key,
                'plan'         => $license->plan_name,
                'device_id'    => $deviceId,
                'activated_at' => $license->activated_at ?? date('Y-m-d H:i:s'),
                'expires_at'   => $license->expires_at,
                'status'       => 'active',
            ],
        ], 200);
    }

    /**
     * POST /api/license/check
     *
     * Menerima license_key dan device_id.
     * Mengembalikan status masa aktif lisensi.
     */
    public function check()
    {
        $licenseKey = $this->request->getJsonVar('license_key') ?? $this->request->getPost('license_key');
        $deviceId   = $this->request->getJsonVar('device_id') ?? $this->request->getPost('device_id');

        // Validasi input
        if (empty($licenseKey) || empty($deviceId)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'license_key dan device_id wajib diisi.',
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

        // Cek device_id cocok
        if (! empty($license->device_id) && $license->device_id !== $deviceId) {
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
