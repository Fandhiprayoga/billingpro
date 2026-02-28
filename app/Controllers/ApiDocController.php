<?php

namespace App\Controllers;

class ApiDocController extends BaseController
{
    /**
     * Halaman dokumentasi API.
     */
    public function index()
    {
        $data = [
            'title'      => 'Dokumentasi API',
            'page_title' => 'Dokumentasi API',
            'base_api'   => rtrim(base_url(), '/'),
        ];

        return $this->renderView('api_docs/index', $data);
    }
}
