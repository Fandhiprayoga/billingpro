# CodeIgniter 4 + Shield RBAC Boilerplate

Boilerplate project CodeIgniter 4 dengan **CodeIgniter Shield** untuk autentikasi dan **Role-Based Access Control (RBAC)**, menggunakan template dashboard **Stisla**.

## Fitur

- ✅ Autentikasi (Login, Register, Logout) menggunakan CodeIgniter Shield
- ✅ Role-Based Access Control (RBAC) dengan 4 role default
- ✅ Template Dashboard Stisla yang sudah di-slice
- ✅ Manajemen User (CRUD)
- ✅ Manajemen Role & Permission Matrix
- ✅ Profil User
- ✅ Pengaturan Sistem
- ✅ Filter berdasarkan Role dan Permission
- ✅ Dynamic Sidebar berdasarkan permission user
- ✅ Modul Licensing & Billing (Plans, Orders, Licenses)
- ✅ Pembayaran Manual (upload bukti bayar + admin approval)
- ✅ Auto-generate License Key (20 karakter unik)
- ✅ API Endpoint untuk POS external (activate & check license)
- ✅ Payment Service Layer (siap integrasi Payment Gateway)

## Roles Default

| Role | Deskripsi |
|------|-----------|
| **Super Admin** | Kontrol penuh terhadap seluruh sistem |
| **Admin** | Administrator harian sistem |
| **Manager** | Melihat laporan dan mengelola data |
| **User** | Pengguna umum dengan akses terbatas |

## Instalasi

### 1. Clone / Copy Project

```bash
cd ci4-app
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Konfigurasi Environment

Copy file `.env` dan sesuaikan konfigurasi database:

```env
database.default.hostname = localhost
database.default.database = ci4_shield_rbac
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
```

### 4. Buat Database

Buat database MySQL dengan nama `ci4_shield_rbac` (atau sesuai konfigurasi).

### 5. Jalankan Migration

```bash
php spark migrate --all
```

### 6. Jalankan Seeder

```bash
php spark db:seed UserSeeder
php spark db:seed PlanSeeder
```

### 7. Jalankan Server

```bash
php spark serve
```

Akses di browser: `http://localhost:8080`

## Akun Default

| Role | Email | Password |
|------|-------|----------|
| Super Admin | superadmin@example.com | password123 |
| Admin | admin@example.com | password123 |
| Manager | manager@example.com | password123 |
| User | user@example.com | password123 |

## Struktur Folder

```
app/
├── Config/
│   ├── Auth.php              # Konfigurasi Shield
│   ├── AuthGroups.php        # Definisi Role, Permission, Matrix
│   ├── Filters.php           # Filter aliases
│   └── Routes.php            # Routing aplikasi
├── Controllers/
│   ├── BaseController.php    # Base controller dengan renderView()
│   ├── AuthController.php    # Override login/register Shield
│   ├── DashboardController.php
│   ├── UserController.php    # CRUD User
│   ├── RoleController.php    # View Roles & Permissions
│   ├── ProfileController.php
│   ├── SettingController.php
│   ├── PlanController.php    # CRUD Paket Lisensi (Admin)
│   ├── OrderController.php   # Order + approval + bukti bayar (Admin)
│   ├── LicenseController.php # Manajemen Lisensi (Admin)
│   ├── UserOrderController.php   # Order & pembayaran (User biasa)
│   ├── UserLicenseController.php # Lisensi saya (User biasa)
│   └── Api/
│       └── LicenseApiController.php  # API untuk POS external
├── Database/
│   ├── Migrations/
│   │   ├── *_CreatePlansTable.php
│   │   ├── *_CreateOrdersTable.php
│   │   ├── *_CreatePaymentConfirmationsTable.php
│   │   └── *_CreateLicensesTable.php
│   └── Seeds/
│       ├── UserSeeder.php    # Seeder user default
│       └── PlanSeeder.php    # Seeder paket lisensi default
├── Filters/
│   ├── RoleFilter.php        # Filter berdasarkan role
│   └── PermissionFilter.php  # Filter berdasarkan permission
├── Libraries/
│   └── Payment/
│       ├── PaymentHandlerInterface.php  # Interface payment gateway
│       ├── ManualPaymentHandler.php     # Handler pembayaran manual
│       └── PaymentService.php           # Service utama (Strategy Pattern)
├── Models/
│   ├── PlanModel.php         # Model paket lisensi
│   ├── OrderModel.php        # Model order
│   ├── PaymentConfirmationModel.php  # Model konfirmasi pembayaran
│   └── LicenseModel.php      # Model lisensi
└── Views/
    ├── layouts/
    │   ├── app.php           # Layout utama dashboard
    │   └── auth.php          # Layout halaman auth
    ├── partials/
    │   ├── navbar.php        # Navbar dengan user dropdown
    │   ├── sidebar.php       # Sidebar dinamis per permission
    │   └── footer.php        # Footer
    ├── auth/
    │   ├── login.php
    │   └── register.php
    ├── dashboard/
    │   └── index.php
    ├── users/
    │   ├── index.php
    │   ├── create.php
    │   └── edit.php
    ├── roles/
    │   ├── index.php
    │   └── permissions.php
    ├── profile/
    │   └── index.php
    └── settings/
        └── index.php
    ├── plans/                # Views paket lisensi
    │   ├── index.php
    │   ├── create.php
    │   └── edit.php
    ├── orders/               # Views order & pembayaran
    │   ├── index.php
    │   ├── create.php
    │   ├── view.php
    │   └── upload_confirmation.php
    ├── licenses/             # Views lisensi (Admin)
    │   ├── index.php
    │   └── view.php
    └── user_billing/         # Views billing (User biasa)
        ├── plans.php
        ├── orders.php
        ├── order_create.php
        ├── order_view.php
        ├── upload_confirmation.php
        ├── licenses.php
        └── license_view.php
public/
└── assets/                   # Asset template Stisla
    ├── css/
    ├── js/
    ├── img/
    └── fonts/
```

## Penggunaan RBAC

### Melindungi Route dengan Role

```php
// Hanya superadmin dan admin yang bisa akses
$routes->get('admin/panel', 'Admin::index', ['filter' => 'role:superadmin,admin']);
```

### Melindungi Route dengan Permission

```php
// Hanya yang punya permission users.create
$routes->get('users/create', 'User::create', ['filter' => 'permission:users.create']);
```

### Cek Permission di Controller

```php
$user = auth()->user();

if ($user->can('users.edit')) {
    // boleh edit
}

if ($user->inGroup('superadmin')) {
    // adalah superadmin
}
```

### Cek Permission di View

```php
<?php if (auth()->user()->can('users.create')): ?>
    <a href="/admin/users/create" class="btn btn-primary">Tambah User</a>
<?php endif; ?>
```

### Menambah Role Baru

Edit file `app/Config/AuthGroups.php`:

```php
public array $groups = [
    // ... role existing ...
    'editor' => [
        'title'       => 'Editor',
        'description' => 'Can manage content.',
    ],
];

public array $matrix = [
    // ... matrix existing ...
    'editor' => [
        'content.create',
        'content.edit',
        'content.delete',
    ],
];
```

### Menambah Permission Baru

```php
public array $permissions = [
    // ... permissions existing ...
    'content.create' => 'Dapat membuat konten',
    'content.edit'   => 'Dapat mengedit konten',
    'content.delete' => 'Dapat menghapus konten',
];
```

## Panduan Membuat Menu/Modul Baru

Berikut langkah-langkah lengkap untuk menambah menu/modul baru dengan RBAC. Contoh: membuat modul **Artikel** (`articles`).

### Langkah 1 — Daftarkan Permission di `app/Config/AuthGroups.php`

Tambahkan permission baru di `$permissions`:

```php
public array $permissions = [
    // ... existing ...
    'articles.list'   => 'Dapat melihat daftar artikel',
    'articles.create' => 'Dapat membuat artikel baru',
    'articles.edit'   => 'Dapat mengedit artikel',
    'articles.delete' => 'Dapat menghapus artikel',
];
```

Lalu assign permission ke role yang sesuai di `$matrix`:

```php
public array $matrix = [
    'superadmin' => [
        'admin.*', 'users.*', 'roles.*', 'dashboard.*', 'reports.*',
        'articles.*',   // <-- tambahkan
    ],
    'admin' => [
        'admin.access', 'users.list', 'users.create', 'users.edit', 'users.delete',
        'dashboard.*', 'reports.*',
        'articles.*',   // <-- tambahkan
    ],
    'manager' => [
        'admin.access', 'users.list', 'dashboard.*', 'reports.*',
        'articles.list', // <-- hanya bisa lihat
    ],
    'user' => [
        'dashboard.access',
    ],
];
```

### Langkah 2 — Buat Migration

```bash
php spark make:migration CreateArticlesTable
```

Edit file migration yang dihasilkan di `app/Database/Migrations/`:

```php
public function up()
{
    $this->forge->addField([
        'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
        'title'      => ['type' => 'VARCHAR', 'constraint' => 255],
        'slug'       => ['type' => 'VARCHAR', 'constraint' => 255],
        'content'    => ['type' => 'TEXT', 'null' => true],
        'author_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
        'status'     => ['type' => 'ENUM', 'constraint' => ['draft', 'published'], 'default' => 'draft'],
        'created_at' => ['type' => 'DATETIME', 'null' => true],
        'updated_at' => ['type' => 'DATETIME', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->createTable('articles');
}
```

Lalu jalankan:

```bash
php spark migrate
```

### Langkah 3 — Buat Model

Buat file `app/Models/ArticleModel.php`:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class ArticleModel extends Model
{
    protected $table         = 'articles';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['title', 'slug', 'content', 'author_id', 'status'];
    protected $useTimestamps = true;
}
```

### Langkah 4 — Buat Controller

Buat file `app/Controllers/ArticleController.php`:

```php
<?php

namespace App\Controllers;

use App\Models\ArticleModel;

class ArticleController extends BaseController
{
    protected ArticleModel $articleModel;

    public function __construct()
    {
        $this->articleModel = new ArticleModel();
    }

    public function index()
    {
        $data = [
            'title'      => 'Daftar Artikel',
            'page_title' => 'Daftar Artikel',
            'articles'   => $this->articleModel->findAll(),
        ];

        return $this->renderView('articles/index', $data);
    }

    public function create()
    {
        $data = [
            'title'      => 'Tambah Artikel',
            'page_title' => 'Tambah Artikel',
        ];

        return $this->renderView('articles/create', $data);
    }

    public function store()
    {
        // validasi & simpan
    }

    public function edit($id)
    {
        // tampilkan form edit
    }

    public function update($id)
    {
        // validasi & update
    }

    public function delete($id)
    {
        // hapus artikel
    }
}
```

### Langkah 5 — Tambah Route di `app/Config/Routes.php`

Tambahkan di dalam group `admin` yang sudah ada:

```php
$routes->group('admin', ['filter' => 'permission:admin.access'], static function ($routes) {

    // ... route existing ...

    // Artikel
    $routes->group('articles', static function ($routes) {
        $routes->get('/', 'ArticleController::index', ['filter' => 'permission:articles.list']);
        $routes->get('create', 'ArticleController::create', ['filter' => 'permission:articles.create']);
        $routes->post('store', 'ArticleController::store', ['filter' => 'permission:articles.create']);
        $routes->get('edit/(:num)', 'ArticleController::edit/$1', ['filter' => 'permission:articles.edit']);
        $routes->post('update/(:num)', 'ArticleController::update/$1', ['filter' => 'permission:articles.edit']);
        $routes->post('delete/(:num)', 'ArticleController::delete/$1', ['filter' => 'permission:articles.delete']);
    });
});
```

### Langkah 6 — Buat View

Buat file view di `app/Views/articles/index.php`:

```php
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Daftar Artikel</h4>
        <div class="card-header-action">
          <?php if (auth()->user()->can('articles.create')): ?>
          <a href="<?= base_url('admin/articles/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Artikel
          </a>
          <?php endif; ?>
        </div>
      </div>
      <div class="card-body">
        <!-- isi tabel artikel -->
      </div>
    </div>
  </div>
</div>
```

### Langkah 7 — Tambah Menu di Sidebar

Edit file `app/Views/partials/sidebar.php`, tambahkan di dalam section **Administrasi**:

```php
<?php if ($currentUser->can('articles.list')): ?>
<li class="<?= isMenuActive('admin/articles') ?>">
  <a class="nav-link" href="<?= base_url('admin/articles') ?>">
    <i class="fas fa-newspaper"></i> <span>Artikel</span>
  </a>
</li>
<?php endif; ?>
```

### Ringkasan Checklist

| # | File yang diubah/dibuat | Apa yang ditambah |
|---|-------------------------|-------------------|
| 1 | `app/Config/AuthGroups.php` | Permission baru + assign ke matrix role |
| 2 | `app/Database/Migrations/` | Migration tabel baru |
| 3 | `app/Models/` | Model baru |
| 4 | `app/Controllers/` | Controller baru (extend `BaseController`, pakai `renderView()`) |
| 5 | `app/Config/Routes.php` | Route baru dengan filter permission |
| 6 | `app/Views/` | View files (index, create, edit) |
| 7 | `app/Views/partials/sidebar.php` | Menu baru dibungkus `$currentUser->can()` |

> **Prinsip utama:** Permission didaftarkan dulu → assign ke role di matrix → gunakan filter di route → cek di view untuk tampilkan/sembunyikan elemen UI.

---

## Modul Licensing & Billing

Modul ini menangani seluruh alur penjualan lisensi POS, mulai dari pembuatan paket, pembuatan order, pembayaran manual, hingga generate license key otomatis.

### Alur Kerja (Flow)

```
User pilih Plan → Buat Order → Upload Bukti Bayar → Admin Review
    → Approve → License Key otomatis di-generate (20 karakter)
    → Reject → Order dibatalkan
```

### Database Schema

| Tabel | Deskripsi |
|-------|-----------|
| `plans` | Paket lisensi (nama, harga, durasi, fitur) |
| `orders` | Order pembelian (terkait user & plan, status, payment method) |
| `payment_confirmations` | Bukti pembayaran manual (bank, rekening, bukti transfer) |
| `licenses` | Lisensi yang di-generate (license_key 20 char, device locking) |

### Permissions

| Permission | Deskripsi |
|-----------|-----------|
| `plans.list` | Melihat daftar paket |
| `plans.create` | Membuat paket baru |
| `plans.edit` | Mengedit paket |
| `plans.delete` | Menghapus paket |
| `orders.list` | Melihat daftar order |
| `orders.create` | Membuat order baru |
| `orders.view` | Melihat detail order |
| `orders.approve` | Menyetujui order (generate lisensi) |
| `orders.reject` | Menolak order |
| `licenses.list` | Melihat daftar lisensi |
| `licenses.view` | Melihat detail lisensi |
| `licenses.revoke` | Mencabut lisensi |
| `payments.list` | Melihat konfirmasi pembayaran |
| `payments.review` | Mereview konfirmasi pembayaran |

### Akses Berdasarkan Role

Modul billing memisahkan akses admin dan user biasa melalui controller & route terpisah:

| Fitur | Admin/Superadmin | Manager | User |
|-------|-----------------|---------|------|
| CRUD Paket Lisensi | ✅ | ❌ (hanya lihat) | ❌ |
| Lihat semua order | ✅ | ✅ | ❌ |
| Approve/Reject order | ✅ | ❌ | ❌ |
| Revoke lisensi | ✅ | ❌ | ❌ |
| Buat order sendiri | — | — | ✅ |
| Upload bukti bayar | — | — | ✅ |
| Lihat order sendiri | — | — | ✅ |
| Lihat lisensi sendiri | — | — | ✅ |
| Browse paket lisensi | — | — | ✅ |

#### Route Admin vs User

| Route | Controller | Role | Deskripsi |
|-------|-----------|------|-----------|
| `/admin/plans/*` | `PlanController` | Admin | CRUD paket lisensi |
| `/admin/orders/*` | `OrderController` | Admin | Kelola semua order |
| `/admin/licenses/*` | `LicenseController` | Admin | Kelola semua lisensi |
| `/plans` | `UserOrderController::plans` | User | Lihat paket tersedia |
| `/my-orders` | `UserOrderController::index` | User | Lihat order sendiri |
| `/my-orders/create` | `UserOrderController::create` | User | Buat order baru |
| `/my-orders/view/:id` | `UserOrderController::view` | User | Detail order (milik sendiri) |
| `/my-orders/upload-confirmation/:id` | `UserOrderController::uploadConfirmation` | User | Upload bukti bayar |
| `/my-licenses` | `UserLicenseController::index` | User | Lihat lisensi sendiri |
| `/my-licenses/view/:id` | `UserLicenseController::view` | User | Detail lisensi (milik sendiri) |

> **Penting:** Controller user (`UserOrderController`, `UserLicenseController`) memfilter semua query berdasarkan `auth()->id()` sehingga user hanya bisa melihat data miliknya sendiri.

### Paket Default (Seeder)

| Paket | Harga | Durasi |
|-------|-------|--------|
| Starter | Rp 99.000 | 30 hari |
| Professional | Rp 249.000 | 30 hari |
| Enterprise | Rp 499.000 | 30 hari |
| Enterprise Yearly | Rp 4.990.000 | 365 hari |

---

## API Endpoint untuk POS External

Dua endpoint publik (tanpa session/login) untuk digunakan oleh aplikasi POS luar.

### POST `/api/license/activate`

Aktivasi lisensi dan lock ke device tertentu.

**Request:**

```json
{
  "license_key": "ABCDE12345FGHIJ67890",
  "device_id": "POS-DEVICE-001"
}
```

**Response (sukses):**

```json
{
  "status": "success",
  "message": "Lisensi berhasil diaktivasi.",
  "data": {
    "license_key": "ABCDE12345FGHIJ67890",
    "plan": "Professional",
    "device_id": "POS-DEVICE-001",
    "activated_at": "2026-02-27 10:30:00",
    "expires_at": "2026-03-29 10:30:00",
    "status": "active"
  }
}
```

**Response (error):**

| HTTP Code | Kondisi |
|-----------|---------|
| `400` | `license_key` atau `device_id` kosong |
| `404` | Lisensi tidak ditemukan |
| `403` | Lisensi tidak aktif / expired / sudah di-lock ke device lain |

### POST `/api/license/check`

Cek status dan masa aktif lisensi.

**Request:**

```json
{
  "license_key": "ABCDE12345FGHIJ67890",
  "device_id": "POS-DEVICE-001"
}
```

**Response (sukses):**

```json
{
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
}
```

---

## Arsitektur Payment Service (Future-Proofing)

Sistem pembayaran dibangun menggunakan **Strategy Pattern** agar mudah menambahkan payment gateway baru (Midtrans, Xendit, dll.) tanpa mengubah struktur tabel `orders`.

### Struktur File

```
app/Libraries/Payment/
├── PaymentHandlerInterface.php   # Interface (kontrak)
├── ManualPaymentHandler.php      # Handler pembayaran manual
└── PaymentService.php            # Service utama (registry)
```

### Cara Kerja

```
PaymentService
├── registerHandler(ManualPaymentHandler)    ← sudah aktif
├── registerHandler(MidtransPaymentHandler)  ← tambahkan nanti
└── registerHandler(XenditPaymentHandler)    ← tambahkan nanti
```

`PaymentService` memilih handler yang tepat berdasarkan kolom `payment_method` di tabel `orders`. Tidak perlu mengubah tabel ataupun controller.

### Menambahkan Payment Gateway Baru (Contoh: Midtrans)

#### Langkah 1 — Buat Handler

Buat file `app/Libraries/Payment/MidtransPaymentHandler.php`:

```php
<?php

namespace App\Libraries\Payment;

class MidtransPaymentHandler implements PaymentHandlerInterface
{
    public function getMethod(): string
    {
        return 'midtrans';
    }

    public function processPayment(object $order, array $data = []): array
    {
        // Panggil Midtrans Snap API untuk buat transaksi
        // Simpan payment_reference (transaction_id) ke order

        $snapToken = $this->createSnapToken($order);

        return [
            'success' => true,
            'message' => 'Silakan selesaikan pembayaran.',
            'data'    => [
                'snap_token'  => $snapToken,
                'redirect_url' => 'https://app.midtrans.com/snap/v2/vtweb/' . $snapToken,
            ],
        ];
    }

    public function verifyPayment(object $order, array $data = []): array
    {
        // Verifikasi callback/notification dari Midtrans
        // Update status order jika valid

        $transactionStatus = $data['transaction_status'] ?? '';

        return [
            'success' => $transactionStatus === 'settlement',
            'message' => 'Status: ' . $transactionStatus,
            'data'    => ['status' => $transactionStatus],
        ];
    }

    public function getPaymentStatus(object $order): string
    {
        // Cek status transaksi ke Midtrans API
        return 'pending';
    }

    private function createSnapToken(object $order): string
    {
        // Implementasi Midtrans Snap API
        // Gunakan library midtrans/midtrans-php

        return 'snap-token-placeholder';
    }
}
```

#### Langkah 2 — Daftarkan di PaymentService

Edit file `app/Libraries/Payment/PaymentService.php`, di constructor:

```php
public function __construct()
{
    $this->orderModel   = new OrderModel();
    $this->licenseModel = new LicenseModel();

    // Register handlers
    $this->registerHandler(new ManualPaymentHandler());
    $this->registerHandler(new MidtransPaymentHandler());  // ← tambahkan
}
```

#### Langkah 3 — Buat Order dengan Method Baru

Di controller atau service, cukup ubah parameter `payment_method`:

```php
$paymentService->createOrder($userId, $planId, 'midtrans');
```

#### Langkah 4 — (Opsional) Tambahkan Callback Route

```php
// di Routes.php
$routes->group('api', static function ($routes) {
    $routes->post('payment/midtrans/callback', 'Api\PaymentCallbackController::midtrans');
});
```

### Tabel Orders — Kolom untuk Gateway

Tabel `orders` sudah menyediakan kolom yang siap pakai:

| Kolom | Kegunaan |
|-------|----------|
| `payment_method` | Identifier handler: `manual`, `midtrans`, `xendit` |
| `payment_reference` | ID transaksi dari gateway (snap_token, invoice_id, dll.) |
| `paid_at` | Timestamp pembayaran berhasil |
| `status` | `pending` → `awaiting_confirmation` → `paid` / `cancelled` |

> **Prinsip:** Logika pembayaran terisolasi di masing-masing handler. Controller dan tabel tetap sama. Cukup `registerHandler()` untuk menambah gateway baru.

---

## Lisensi

MIT License
