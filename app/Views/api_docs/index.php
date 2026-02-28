<?php
/**
 * Dokumentasi API — Halaman referensi lengkap endpoint API untuk integrasi POS external.
 *
 * @var string $base_api  Base URL aplikasi
 */
?>

<style>
  .api-card { border-left: 4px solid; }
  .api-card.post { border-left-color: #49A942; }
  .api-card .method-badge { font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px; }
  .api-card .method-badge.post { background-color: #49A942; }
  .param-table th { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; }
  .param-table td { vertical-align: middle; font-size: 0.9rem; }
  .response-block { max-height: 400px; overflow-y: auto; }
  .response-block pre { margin: 0; white-space: pre; font-size: 0.85rem; }
  .code-block { background: #1e1e1e !important; color: #d4d4d4 !important; padding: 16px; border-radius: 6px; overflow-x: auto; }
  .code-block pre { margin: 0; white-space: pre; font-size: 0.85rem; color: #d4d4d4 !important; background: transparent !important; }
  .code-block code { color: #d4d4d4 !important; background: transparent !important; }
  .copy-btn { cursor: pointer; }
  .status-badge { font-size: 0.75rem; padding: 3px 8px; }
  .nav-api .nav-link { font-size: 0.9rem; padding: 0.5rem 1rem; }
  .nav-api .nav-link.active { font-weight: 600; }
  .toc-link { color: #6c757d; text-decoration: none; font-size: 0.9rem; display: block; padding: 4px 0; }
  .toc-link:hover { color: #6777ef; }
  .toc-link.active { color: #6777ef; font-weight: 600; }
  .api-url-box { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 10px 16px; font-family: monospace; font-size: 0.95rem; }
</style>

<div class="row">
  <!-- Sidebar TOC -->
  <div class="col-md-3">
    <div class="card" style="position: sticky; top: 80px;">
      <div class="card-header">
        <h4><i class="fas fa-list"></i> Daftar Endpoint</h4>
      </div>
      <div class="card-body p-3">
        <div class="mb-3">
          <small class="text-muted font-weight-bold text-uppercase">Base URL</small>
          <div class="api-url-box mt-1 small">
            <?= esc($base_api) ?>
          </div>
        </div>
        <hr>
        <nav>
          <a href="#section-overview" class="toc-link"><i class="fas fa-info-circle mr-1"></i> Overview</a>
          <a href="#section-auth" class="toc-link"><i class="fas fa-lock mr-1"></i> Autentikasi</a>
          <small class="text-muted font-weight-bold text-uppercase d-block mt-3 mb-1">License</small>
          <a href="#api-activate" class="toc-link">
            <span class="badge badge-success mr-1">POST</span> /api/license/activate
          </a>
          <a href="#api-check" class="toc-link">
            <span class="badge badge-success mr-1">POST</span> /api/license/check
          </a>
          <hr>
          <small class="text-muted font-weight-bold text-uppercase d-block mb-1">Kode Status</small>
          <a href="#section-status-codes" class="toc-link"><i class="fas fa-exclamation-triangle mr-1"></i> HTTP Status Codes</a>
        </nav>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="col-md-9">

    <!-- Overview -->
    <div class="card" id="section-overview">
      <div class="card-header">
        <h4><i class="fas fa-info-circle"></i> Overview</h4>
      </div>
      <div class="card-body">
        <p>API ini digunakan oleh aplikasi <strong>external (POS / Web)</strong> untuk mengaktivasi dan memeriksa status lisensi. API bersifat <strong>publik</strong> (tidak memerlukan session/login) dan menggunakan format <strong>JSON</strong>.</p>

        <table class="table table-sm table-bordered">
          <tr>
            <td width="150"><strong>Base URL</strong></td>
            <td><code><?= esc($base_api) ?></code></td>
          </tr>
          <tr>
            <td><strong>Format</strong></td>
            <td><code>application/json</code></td>
          </tr>
          <tr>
            <td><strong>Method</strong></td>
            <td>Semua endpoint menggunakan <span class="badge badge-success">POST</span></td>
          </tr>
          <tr>
            <td><strong>Encoding</strong></td>
            <td>UTF-8</td>
          </tr>
        </table>
      </div>
    </div>

    <!-- Authentication -->
    <div class="card" id="section-auth">
      <div class="card-header">
        <h4><i class="fas fa-lock"></i> Autentikasi</h4>
      </div>
      <div class="card-body">
        <div class="alert alert-info">
          <i class="fas fa-info-circle mr-1"></i>
          API ini <strong>tidak memerlukan token atau API key</strong>. Autentikasi dilakukan melalui <code>license_key</code> yang dikirim pada setiap request. Parameter <code>device_id</code> bersifat <strong>opsional</strong> untuk fitur device locking.
        </div>
        <p>Keamanan dijamin melalui mekanisme:</p>
        <ul>
          <li><strong>License Key</strong> — 20 karakter unik yang di-generate saat order disetujui</li>
          <li><strong>Device Locking</strong> <span class="badge badge-secondary">Opsional</span> — Jika <code>device_id</code> dikirim, lisensi dikunci ke perangkat tersebut saat pertama kali diaktivasi. Cocok untuk aplikasi POS desktop.</li>
          <li><strong>Expiry Check</strong> — Lisensi secara otomatis expired setelah masa berlaku habis</li>
        </ul>
        <div class="alert alert-light border small">
          <i class="fas fa-globe mr-1"></i>
          <strong>Untuk Aplikasi Web:</strong> Anda tidak perlu mengirim <code>device_id</code>. Cukup gunakan <code>license_key</code> saja untuk aktivasi dan pengecekan lisensi.
        </div>
      </div>
    </div>

    <!-- ============================================================= -->
    <!-- API 1: Activate License -->
    <!-- ============================================================= -->
    <div class="card api-card post" id="api-activate">
      <div class="card-header d-flex align-items-center">
        <span class="badge method-badge post text-white mr-2">POST</span>
        <h4 class="mb-0"><code>/api/license/activate</code></h4>
      </div>
      <div class="card-body">
        <p>Mengaktivasi lisensi. Jika <code>device_id</code> dikirim, lisensi akan dikunci (<em>lock</em>) ke perangkat tersebut. Jika tidak, lisensi diaktivasi tanpa device locking (cocok untuk aplikasi web).</p>

        <div class="alert alert-info small">
          <i class="fas fa-info-circle mr-1"></i>
          <strong>Info:</strong> Parameter <code>device_id</code> bersifat opsional. Jika dikirim dan lisensi sudah di-lock ke device lain, aktivasi akan ditolak.
        </div>

        <!-- URL -->
        <h6 class="font-weight-bold mt-4 mb-2"><i class="fas fa-link mr-1"></i> URL Lengkap</h6>
        <div class="api-url-box mb-3">
          <code><?= esc($base_api) ?>/api/license/activate</code>
          <button class="btn btn-sm btn-outline-primary float-right py-0 copy-btn" onclick="copyText('<?= esc($base_api) ?>/api/license/activate')">
            <i class="fas fa-copy"></i>
          </button>
        </div>

        <!-- Request Parameters -->
        <h6 class="font-weight-bold mt-4 mb-2"><i class="fas fa-arrow-circle-up mr-1"></i> Request Parameters</h6>
        <table class="table table-bordered param-table">
          <thead class="thead-light">
            <tr>
              <th width="160">Parameter</th>
              <th width="80">Tipe</th>
              <th width="80">Wajib</th>
              <th>Deskripsi</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><code>license_key</code></td>
              <td><span class="badge badge-light">string</span></td>
              <td><span class="badge badge-danger">Ya</span></td>
              <td>License key 20 karakter yang diperoleh setelah order disetujui. Contoh: <code>ABCDE12345FGHIJ67890</code></td>
            </tr>
            <tr>
              <td><code>device_id</code></td>
              <td><span class="badge badge-light">string</span></td>
              <td><span class="badge badge-secondary">Tidak</span></td>
              <td>Identifier unik perangkat (opsional). Jika dikirim, lisensi akan di-lock ke device ini. Bisa berupa serial number, MAC address, atau UUID perangkat. Contoh: <code>POS-DEVICE-001</code></td>
            </tr>
          </tbody>
        </table>

        <!-- Request Example -->
        <h6 class="font-weight-bold mt-4 mb-2"><i class="fas fa-code mr-1"></i> Contoh Request</h6>
        <ul class="nav nav-pills mb-2" role="tablist">
          <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#activate-curl">cURL</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#activate-js">JavaScript</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#activate-php">PHP</a></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane fade show active" id="activate-curl">
            <div class="code-block">
<pre># Minimal (untuk aplikasi web)
curl -X POST <?= esc($base_api) ?>/api/license/activate \
  -H "Content-Type: application/json" \
  -d '{
    "license_key": "ABCDE12345FGHIJ67890"
  }'

# Dengan device locking (untuk POS desktop)
curl -X POST <?= esc($base_api) ?>/api/license/activate \
  -H "Content-Type: application/json" \
  -d '{
    "license_key": "ABCDE12345FGHIJ67890",
    "device_id": "POS-DEVICE-001"
  }'</pre>
            </div>
          </div>
          <div class="tab-pane fade" id="activate-js">
            <div class="code-block">
<pre>// Payload minimal (untuk aplikasi web)
const payload = {
  license_key: 'ABCDE12345FGHIJ67890'
};

// Opsional: tambahkan device_id untuk device locking
// payload.device_id = 'POS-DEVICE-001';

const response = await fetch('<?= esc($base_api) ?>/api/license/activate', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify(payload)
});

const data = await response.json();
console.log(data);</pre>
            </div>
          </div>
          <div class="tab-pane fade" id="activate-php">
            <div class="code-block">
<pre>// Payload minimal (untuk aplikasi web)
$payload = [
    'license_key' => 'ABCDE12345FGHIJ67890',
];

// Opsional: tambahkan device_id untuk device locking
// $payload['device_id'] = 'POS-DEVICE-001';

$ch = curl_init('<?= esc($base_api) ?>/api/license/activate');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => json_encode($payload),
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);
print_r($result);</pre>
            </div>
          </div>
        </div>

        <!-- Response Success -->
        <h6 class="font-weight-bold mt-4 mb-2"><i class="fas fa-check-circle text-success mr-1"></i> Response — Sukses <span class="status-badge badge badge-success">200 OK</span></h6>
        <div class="code-block">
<pre>{
  "status": "success",
  "message": "Lisensi berhasil diaktivasi.",
  "data": {
    "license_key": "ABCDE12345FGHIJ67890",
    "plan": "Professional",
    "device_id": null,
    "activated_at": "2026-02-27 10:30:00",
    "expires_at": "2026-03-29 10:30:00",
    "status": "active"
  }
}</pre>
        </div>

        <!-- Response Fields -->
        <h6 class="font-weight-bold mt-3 mb-2"><i class="fas fa-list mr-1"></i> Response Fields (data)</h6>
        <table class="table table-bordered param-table">
          <thead class="thead-light">
            <tr>
              <th width="150">Field</th>
              <th width="80">Tipe</th>
              <th>Deskripsi</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><code>license_key</code></td>
              <td><span class="badge badge-light">string</span></td>
              <td>License key yang diaktivasi</td>
            </tr>
            <tr>
              <td><code>plan</code></td>
              <td><span class="badge badge-light">string</span></td>
              <td>Nama paket lisensi (contoh: Starter, Professional, Enterprise)</td>
            </tr>
            <tr>
              <td><code>device_id</code></td>
              <td><span class="badge badge-light">string|null</span></td>
              <td>Device ID yang terkunci pada lisensi ini (<code>null</code> jika tidak dikirim)</td>
            </tr>
            <tr>
              <td><code>activated_at</code></td>
              <td><span class="badge badge-light">datetime</span></td>
              <td>Tanggal dan waktu aktivasi (<code>Y-m-d H:i:s</code>)</td>
            </tr>
            <tr>
              <td><code>expires_at</code></td>
              <td><span class="badge badge-light">datetime</span></td>
              <td>Tanggal dan waktu kadaluarsa lisensi</td>
            </tr>
            <tr>
              <td><code>status</code></td>
              <td><span class="badge badge-light">string</span></td>
              <td>Status lisensi: <code>active</code></td>
            </tr>
          </tbody>
        </table>

        <!-- Error Responses -->
        <h6 class="font-weight-bold mt-4 mb-2"><i class="fas fa-times-circle text-danger mr-1"></i> Response — Error</h6>

        <div class="accordion" id="activateErrors">
          <!-- 400 -->
          <div class="card mb-1">
            <div class="card-header p-2" id="ae400">
              <button class="btn btn-link btn-sm text-left w-100" data-toggle="collapse" data-target="#ae400body">
                <span class="badge badge-warning mr-2">400</span> Parameter tidak lengkap
              </button>
            </div>
            <div id="ae400body" class="collapse" data-parent="#activateErrors">
              <div class="card-body p-2">
                <div class="code-block">
<pre>{
  "status": "error",
  "message": "license_key wajib diisi."
}</pre>
                </div>
                <small class="text-muted mt-1 d-block">Terjadi ketika <code>license_key</code> kosong atau tidak dikirim.</small>
              </div>
            </div>
          </div>
          <!-- 404 -->
          <div class="card mb-1">
            <div class="card-header p-2" id="ae404">
              <button class="btn btn-link btn-sm text-left w-100" data-toggle="collapse" data-target="#ae404body">
                <span class="badge badge-info mr-2">404</span> Lisensi tidak ditemukan
              </button>
            </div>
            <div id="ae404body" class="collapse" data-parent="#activateErrors">
              <div class="card-body p-2">
                <div class="code-block">
<pre>{
  "status": "error",
  "message": "Lisensi tidak ditemukan."
}</pre>
                </div>
                <small class="text-muted mt-1 d-block">License key yang dikirim tidak cocok dengan data manapun di database.</small>
              </div>
            </div>
          </div>
          <!-- 403 - tidak aktif -->
          <div class="card mb-1">
            <div class="card-header p-2" id="ae403a">
              <button class="btn btn-link btn-sm text-left w-100" data-toggle="collapse" data-target="#ae403abody">
                <span class="badge badge-danger mr-2">403</span> Lisensi tidak aktif
              </button>
            </div>
            <div id="ae403abody" class="collapse" data-parent="#activateErrors">
              <div class="card-body p-2">
                <div class="code-block">
<pre>{
  "status": "error",
  "message": "Lisensi tidak aktif. Status: revoked"
}</pre>
                </div>
                <small class="text-muted mt-1 d-block">Status lisensi bukan <code>active</code> (misalnya <code>revoked</code>, <code>suspended</code>).</small>
              </div>
            </div>
          </div>
          <!-- 403 - expired -->
          <div class="card mb-1">
            <div class="card-header p-2" id="ae403b">
              <button class="btn btn-link btn-sm text-left w-100" data-toggle="collapse" data-target="#ae403bbody">
                <span class="badge badge-danger mr-2">403</span> Lisensi sudah expired
              </button>
            </div>
            <div id="ae403bbody" class="collapse" data-parent="#activateErrors">
              <div class="card-body p-2">
                <div class="code-block">
<pre>{
  "status": "error",
  "message": "Lisensi sudah expired."
}</pre>
                </div>
                <small class="text-muted mt-1 d-block">Lisensi sudah melewati tanggal <code>expires_at</code>. Status otomatis di-update ke <code>expired</code>.</small>
              </div>
            </div>
          </div>
          <!-- 403 - device lain -->
          <div class="card mb-1">
            <div class="card-header p-2" id="ae403c">
              <button class="btn btn-link btn-sm text-left w-100" data-toggle="collapse" data-target="#ae403cbody">
                <span class="badge badge-danger mr-2">403</span> Sudah digunakan di perangkat lain
              </button>
            </div>
            <div id="ae403cbody" class="collapse" data-parent="#activateErrors">
              <div class="card-body p-2">
                <div class="code-block">
<pre>{
  "status": "error",
  "message": "Lisensi sudah digunakan di perangkat lain."
}</pre>
                </div>
                <small class="text-muted mt-1 d-block">Hanya terjadi jika <code>device_id</code> dikirim. Lisensi sudah di-lock ke <code>device_id</code> lain. Satu lisensi hanya bisa digunakan di satu perangkat.</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================= -->
    <!-- API 2: Check License -->
    <!-- ============================================================= -->
    <div class="card api-card post" id="api-check">
      <div class="card-header d-flex align-items-center">
        <span class="badge method-badge post text-white mr-2">POST</span>
        <h4 class="mb-0"><code>/api/license/check</code></h4>
      </div>
      <div class="card-body">
        <p>Memeriksa status dan masa aktif lisensi. Endpoint ini dipanggil secara berkala oleh aplikasi untuk memverifikasi bahwa lisensi masih berlaku. Jika <code>device_id</code> dikirim, akan divalidasi kecocokannya dengan device yang terdaftar.</p>

        <div class="alert alert-info small">
          <i class="fas fa-lightbulb mr-1"></i>
          <strong>Tips:</strong> Panggil endpoint ini saat aplikasi dibuka atau secara periodik (misalnya setiap 24 jam) untuk memastikan lisensi masih valid.
        </div>

        <!-- URL -->
        <h6 class="font-weight-bold mt-4 mb-2"><i class="fas fa-link mr-1"></i> URL Lengkap</h6>
        <div class="api-url-box mb-3">
          <code><?= esc($base_api) ?>/api/license/check</code>
          <button class="btn btn-sm btn-outline-primary float-right py-0 copy-btn" onclick="copyText('<?= esc($base_api) ?>/api/license/check')">
            <i class="fas fa-copy"></i>
          </button>
        </div>

        <!-- Request Parameters -->
        <h6 class="font-weight-bold mt-4 mb-2"><i class="fas fa-arrow-circle-up mr-1"></i> Request Parameters</h6>
        <table class="table table-bordered param-table">
          <thead class="thead-light">
            <tr>
              <th width="160">Parameter</th>
              <th width="80">Tipe</th>
              <th width="80">Wajib</th>
              <th>Deskripsi</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><code>license_key</code></td>
              <td><span class="badge badge-light">string</span></td>
              <td><span class="badge badge-danger">Ya</span></td>
              <td>License key 20 karakter. Contoh: <code>ABCDE12345FGHIJ67890</code></td>
            </tr>
            <tr>
              <td><code>device_id</code></td>
              <td><span class="badge badge-light">string</span></td>
              <td><span class="badge badge-secondary">Tidak</span></td>
              <td>Device ID perangkat (opsional). Jika dikirim dan lisensi sudah di-lock ke device, akan divalidasi kecocokannya.</td>
            </tr>
          </tbody>
        </table>

        <!-- Request Example -->
        <h6 class="font-weight-bold mt-4 mb-2"><i class="fas fa-code mr-1"></i> Contoh Request</h6>
        <ul class="nav nav-pills mb-2" role="tablist">
          <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#check-curl">cURL</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#check-js">JavaScript</a></li>
          <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#check-php">PHP</a></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane fade show active" id="check-curl">
            <div class="code-block">
<pre># Minimal (untuk aplikasi web)
curl -X POST <?= esc($base_api) ?>/api/license/check \
  -H "Content-Type: application/json" \
  -d '{
    "license_key": "ABCDE12345FGHIJ67890"
  }'

# Dengan device_id (untuk POS desktop)
curl -X POST <?= esc($base_api) ?>/api/license/check \
  -H "Content-Type: application/json" \
  -d '{
    "license_key": "ABCDE12345FGHIJ67890",
    "device_id": "POS-DEVICE-001"
  }'</pre>
            </div>
          </div>
          <div class="tab-pane fade" id="check-js">
            <div class="code-block">
<pre>// Payload minimal (untuk aplikasi web)
const payload = {
  license_key: 'ABCDE12345FGHIJ67890'
};

// Opsional: tambahkan device_id jika menggunakan device locking
// payload.device_id = 'POS-DEVICE-001';

const response = await fetch('<?= esc($base_api) ?>/api/license/check', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify(payload)
});

const data = await response.json();

if (data.data.is_active) {
  console.log('Lisensi aktif, sisa ' + data.data.days_remaining + ' hari');
} else {
  console.log('Lisensi tidak aktif: ' + data.data.status);
}</pre>
            </div>
          </div>
          <div class="tab-pane fade" id="check-php">
            <div class="code-block">
<pre>// Payload minimal (untuk aplikasi web)
$payload = [
    'license_key' => 'ABCDE12345FGHIJ67890',
];

// Opsional: tambahkan device_id jika menggunakan device locking
// $payload['device_id'] = 'POS-DEVICE-001';

$ch = curl_init('<?= esc($base_api) ?>/api/license/check');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => json_encode($payload),
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($result['data']['is_active']) {
    echo "Lisensi aktif, sisa {$result['data']['days_remaining']} hari";
} else {
    echo "Lisensi tidak aktif: {$result['data']['status']}";
}</pre>
            </div>
          </div>
        </div>

        <!-- Response Success -->
        <h6 class="font-weight-bold mt-4 mb-2"><i class="fas fa-check-circle text-success mr-1"></i> Response — Sukses <span class="status-badge badge badge-success">200 OK</span></h6>
        <div class="code-block">
<pre>{
  "status": "success",
  "message": "Data lisensi ditemukan.",
  "data": {
    "license_key": "ABCDE12345FGHIJ67890",
    "plan": "Professional",
    "device_id": "POS-DEVICE-001",
    "activated_at": "2026-02-27 10:30:00",
    "expires_at": "2026-03-29 10:30:00",
    "status": "active",
    "is_active": true,
    "days_remaining": 30
  }
}</pre>
        </div>

        <!-- Response Fields -->
        <h6 class="font-weight-bold mt-3 mb-2"><i class="fas fa-list mr-1"></i> Response Fields (data)</h6>
        <table class="table table-bordered param-table">
          <thead class="thead-light">
            <tr>
              <th width="150">Field</th>
              <th width="80">Tipe</th>
              <th>Deskripsi</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><code>license_key</code></td>
              <td><span class="badge badge-light">string</span></td>
              <td>License key yang dicek</td>
            </tr>
            <tr>
              <td><code>plan</code></td>
              <td><span class="badge badge-light">string</span></td>
              <td>Nama paket lisensi</td>
            </tr>
            <tr>
              <td><code>device_id</code></td>
              <td><span class="badge badge-light">string|null</span></td>
              <td>Device ID yang terdaftar (<code>null</code> jika belum diaktivasi)</td>
            </tr>
            <tr>
              <td><code>activated_at</code></td>
              <td><span class="badge badge-light">datetime|null</span></td>
              <td>Tanggal aktivasi (<code>null</code> jika belum diaktivasi)</td>
            </tr>
            <tr>
              <td><code>expires_at</code></td>
              <td><span class="badge badge-light">datetime</span></td>
              <td>Tanggal kadaluarsa lisensi</td>
            </tr>
            <tr>
              <td><code>status</code></td>
              <td><span class="badge badge-light">string</span></td>
              <td>Status lisensi: <code>active</code>, <code>expired</code>, <code>revoked</code>, <code>suspended</code></td>
            </tr>
            <tr>
              <td><code>is_active</code></td>
              <td><span class="badge badge-light">boolean</span></td>
              <td><code>true</code> jika lisensi aktif dan belum expired, <code>false</code> jika sebaliknya</td>
            </tr>
            <tr>
              <td><code>days_remaining</code></td>
              <td><span class="badge badge-light">integer</span></td>
              <td>Jumlah hari tersisa sebelum lisensi expired. <code>0</code> jika sudah expired.</td>
            </tr>
          </tbody>
        </table>

        <!-- Error Responses -->
        <h6 class="font-weight-bold mt-4 mb-2"><i class="fas fa-times-circle text-danger mr-1"></i> Response — Error</h6>

        <div class="accordion" id="checkErrors">
          <!-- 400 -->
          <div class="card mb-1">
            <div class="card-header p-2" id="ce400">
              <button class="btn btn-link btn-sm text-left w-100" data-toggle="collapse" data-target="#ce400body">
                <span class="badge badge-warning mr-2">400</span> Parameter tidak lengkap
              </button>
            </div>
            <div id="ce400body" class="collapse" data-parent="#checkErrors">
              <div class="card-body p-2">
                <div class="code-block">
<pre>{
  "status": "error",
  "message": "license_key wajib diisi."
}</pre>
                </div>
              </div>
            </div>
          </div>
          <!-- 404 -->
          <div class="card mb-1">
            <div class="card-header p-2" id="ce404">
              <button class="btn btn-link btn-sm text-left w-100" data-toggle="collapse" data-target="#ce404body">
                <span class="badge badge-info mr-2">404</span> Lisensi tidak ditemukan
              </button>
            </div>
            <div id="ce404body" class="collapse" data-parent="#checkErrors">
              <div class="card-body p-2">
                <div class="code-block">
<pre>{
  "status": "error",
  "message": "Lisensi tidak ditemukan."
}</pre>
                </div>
              </div>
            </div>
          </div>
          <!-- 403 -->
          <div class="card mb-1">
            <div class="card-header p-2" id="ce403">
              <button class="btn btn-link btn-sm text-left w-100" data-toggle="collapse" data-target="#ce403body">
                <span class="badge badge-danger mr-2">403</span> Device ID tidak cocok
              </button>
            </div>
            <div id="ce403body" class="collapse" data-parent="#checkErrors">
              <div class="card-body p-2">
                <div class="code-block">
<pre>{
  "status": "error",
  "message": "Device ID tidak cocok dengan lisensi ini."
}</pre>
                </div>
                <small class="text-muted mt-1 d-block">Hanya terjadi jika <code>device_id</code> dikirim. Device ID yang dikirim tidak sama dengan device yang terdaftar pada lisensi.</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================= -->
    <!-- HTTP Status Codes Reference -->
    <!-- ============================================================= -->
    <div class="card" id="section-status-codes">
      <div class="card-header">
        <h4><i class="fas fa-exclamation-triangle"></i> HTTP Status Codes</h4>
      </div>
      <div class="card-body">
        <p>Daftar semua kode status HTTP yang mungkin dikembalikan oleh API:</p>
        <table class="table table-bordered param-table">
          <thead class="thead-light">
            <tr>
              <th width="100">Kode</th>
              <th width="200">Status</th>
              <th>Deskripsi</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><span class="badge badge-success">200</span></td>
              <td>OK</td>
              <td>Request berhasil. Data lisensi dikembalikan di field <code>data</code>.</td>
            </tr>
            <tr>
              <td><span class="badge badge-warning">400</span></td>
              <td>Bad Request</td>
              <td>Parameter wajib tidak dikirim atau kosong (<code>license_key</code>).</td>
            </tr>
            <tr>
              <td><span class="badge badge-danger">403</span></td>
              <td>Forbidden</td>
              <td>Lisensi tidak aktif, sudah expired, atau device ID tidak cocok.</td>
            </tr>
            <tr>
              <td><span class="badge badge-info">404</span></td>
              <td>Not Found</td>
              <td>License key tidak ditemukan di database.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Flow Diagram -->
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-project-diagram"></i> Alur Integrasi</h4>
      </div>
      <div class="card-body">
        <div class="alert alert-light border">
<pre class="mb-0" style="font-size: 0.85rem;">
┌─────────────────┐     ┌──────────────────────┐     ┌─────────────────┐
│ Aplikasi Client │     │   Billing API Server │     │    Database     │
└────────┬────────┘     └──────────┬───────────┘     └────────┬────────┘
         │                         │                          │
         │  1. POST /activate      │                          │
         │  {license_key}          │                          │
         │  + device_id (opsional) │                          │
         │────────────────────────>│                          │
         │                         │  2. Cek license_key      │
         │                         │─────────────────────────>│
         │                         │                          │
         │                         │  3. Validasi status,     │
         │                         │     expiry, device lock  │
         │                         │<─────────────────────────│
         │                         │                          │
         │                         │  4. Simpan activated_at  │
         │                         │     (+ device_id jika    │
         │                         │      dikirim)            │
         │                         │─────────────────────────>│
         │                         │                          │
         │  5. Response 200        │                          │
         │  {status, data}         │                          │
         │<────────────────────────│                          │
         │                         │                          │
         │  ═══ Setelah Aktif ═══  │                          │
         │                         │                          │
         │  6. POST /check         │                          │
         │  {license_key}          │                          │
         │────────────────────────>│                          │
         │                         │  7. Cek status &         │
         │                         │     days_remaining       │
         │                         │<─────────────────────────│
         │                         │                          │
         │  8. Response 200        │                          │
         │  {is_active, days_left} │                          │
         │<────────────────────────│                          │
         │                         │                          │
</pre>
        </div>

        <h6 class="font-weight-bold mt-3">Rekomendasi Alur di Aplikasi:</h6>
        <ol>
          <li><strong>Pertama kali dibuka</strong> — Minta user input license key → panggil <code>/activate</code></li>
          <li><strong>Simpan lokal</strong> — Simpan <code>license_key</code> di config/storage lokal aplikasi</li>
          <li><strong>Setiap buka aplikasi</strong> — Panggil <code>/check</code> untuk verifikasi lisensi masih valid</li>
          <li><strong>Handle expired</strong> — Jika <code>is_active = false</code>, tampilkan pesan untuk perpanjang lisensi</li>
          <li><strong>Handle offline</strong> — Jika server tidak bisa dihubungi, gunakan cache lokal terakhir (dengan batas waktu)</li>
        </ol>

        <div class="alert alert-info small mt-3">
          <i class="fas fa-desktop mr-1"></i>
          <strong>Untuk POS Desktop:</strong> Jika ingin mengunci lisensi ke perangkat tertentu, kirim <code>device_id</code> (serial number/MAC address) pada saat <code>/activate</code>. Lisensi kemudian hanya bisa digunakan di perangkat tersebut.
        </div>
      </div>
    </div>

  </div>
</div>

<script>
function copyText(text) {
  navigator.clipboard.writeText(text).then(function() {
    if (typeof iziToast !== 'undefined') {
      iziToast.success({ title: 'Berhasil', message: 'URL berhasil disalin!', position: 'topRight' });
    } else {
      alert('URL berhasil disalin!');
    }
  }).catch(function() {
    var el = document.createElement('textarea');
    el.value = text;
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    alert('URL berhasil disalin!');
  });
}
</script>
