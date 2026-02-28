<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * FileController
 *
 * Melayani file yang diupload dari writable/uploads secara aman.
 * File di writable/ tidak bisa diakses langsung via web server,
 * sehingga perlu controller ini untuk membaca dan mengirim filenya.
 */
class FileController extends BaseController
{
    /**
     * Serve file dari writable/uploads.
     *
     * @param string ...$segments Path segments setelah /uploads/
     */
    public function serve(string ...$segments): ResponseInterface
    {
        $relativePath = implode('/', $segments);

        // Sanitasi path: cegah directory traversal
        $relativePath = str_replace(['..', "\0"], '', $relativePath);

        $filePath = WRITEPATH . 'uploads/' . $relativePath;

        if (! is_file($filePath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File tidak ditemukan.');
        }

        // Deteksi mime type
        $mimeType = mime_content_type($filePath);

        // Hanya izinkan file gambar
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/x-icon', 'image/vnd.microsoft.icon', 'image/svg+xml'];
        if (! in_array($mimeType, $allowedMimes)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Tipe file tidak diizinkan.');
        }

        // Set header cache (1 jam)
        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Length', (string) filesize($filePath))
            ->setHeader('Cache-Control', 'public, max-age=3600')
            ->setBody(file_get_contents($filePath));
    }
}
