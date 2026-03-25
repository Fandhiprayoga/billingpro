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
- ✅ API Endpoint untuk aplikasi external / POS / Web (activate & check license)
- ✅ Payment Service Layer (siap integrasi Payment Gateway)
- ✅ Profil Pelanggan (nama usaha, no. telp, propinsi, kabupaten) — otomatis tersimpan saat registrasi

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

---

## Instalasi dengan Docker (Containerisasi)

Sebagai alternatif instalasi manual, project ini sudah menyediakan konfigurasi Docker lengkap.

### Prasyarat

- [Docker](https://docs.docker.com/get-docker/) dan [Docker Compose](https://docs.docker.com/compose/install/) sudah terinstal

### Arsitektur Container

| Service | Container | Image | Port | Deskripsi |
|---------|-----------|-------|------|-----------|
| **app** | `app-billingpro` | PHP 8.2 + Apache (build dari Dockerfile) | `8080:80` | Aplikasi CodeIgniter 4 |
| **billingpro-mysql** | `billingpro_mariadb` | MariaDB 10.11 | `3306:3306` | Database MySQL |
| **phpmyadmin** | `pma_billingpro` | phpMyAdmin (latest) | `8081:80` | Database management UI |

### 1. Konfigurasi Environment

Copy file `env` menjadi `.env` dan sesuaikan untuk Docker:

```env
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8080/'

database.default.hostname = billingpro-mysql
database.default.database = billingpro
database.default.username = user
database.default.password = password
database.default.DBDriver = MySQLi
database.default.port = 3306
```

> **Penting:** `database.default.hostname` harus menggunakan nama service Docker (`billingpro-mysql`), bukan `localhost`.

### 2. Build & Jalankan Container

```bash
docker compose up --build -d
```

Tunggu hingga semua container berjalan. Cek status:

```bash
docker compose ps
```

### 3. Install Dependencies (jika menggunakan volume mount)

Karena volume mount akan menimpa folder `vendor/` di container, jalankan composer install di dalam container:

```bash
docker exec app-billingpro composer install
```

### 4. Jalankan Migration

```bash
docker exec app-billingpro php spark migrate --all
```

### 5. Jalankan Seeder

```bash
docker exec app-billingpro php spark db:seed UserSeeder
docker exec app-billingpro php spark db:seed PlanSeeder
```

### 6. Akses Aplikasi

| Layanan | URL |
|---------|-----|
| Aplikasi | [http://localhost:8080](http://localhost:8080) |
| phpMyAdmin | [http://localhost:8081](http://localhost:8081) |

> Login phpMyAdmin: Server `billingpro-mysql`, User `user`, Password `password`

### Perintah Docker Berguna

```bash
# Melihat log aplikasi
docker compose logs -f app

# Masuk ke shell container
docker exec -it app-billingpro bash

# Menjalankan perintah spark
docker exec app-billingpro php spark <command>

# Menghentikan semua container
docker compose down

# Menghentikan dan hapus volume (reset database)
docker compose down -v
```

---

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
│   ├── UserController.php    # CRUD User + profil pelanggan
│   ├── RoleController.php    # View Roles & Permissions
│   ├── ProfileController.php # Profil user + data usaha pelanggan
│   ├── SettingController.php
│   ├── PlanController.php    # CRUD Paket Lisensi (Admin)
│   ├── OrderController.php   # Order + approval + bukti bayar (Admin)
│   ├── LicenseController.php # Manajemen Lisensi (Admin)
│   ├── UserOrderController.php   # Order & pembayaran (User biasa)
│   ├── UserLicenseController.php # Lisensi saya (User biasa)
│   └── Api/
│       └── LicenseApiController.php  # API untuk aplikasi external (POS / Web)
├── Database/
│   ├── Migrations/
│   │   ├── *_CreateCustomerProfilesTable.php
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
│   ├── CustomerProfileModel.php      # Model profil pelanggan
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
| `customer_profiles` | Profil pelanggan (nama usaha, no. telp, propinsi, kabupaten) — relasi 1:1 dengan `users` |
| `plans` | Paket lisensi (nama, harga, durasi, fitur) |
| `orders` | Order pembelian (terkait user & plan, status, payment method) |
| `payment_confirmations` | Bukti pembayaran manual (bank, rekening, bukti transfer) |
| `licenses` | Lisensi yang di-generate (license_key 20 char, UUID, device locking opsional) |

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

## API Endpoint untuk Aplikasi External

Dua endpoint publik (tanpa session/login) untuk digunakan oleh aplikasi external (POS desktop maupun aplikasi web).

> **Catatan:** Parameter `device_id` bersifat **opsional**. Untuk aplikasi web, cukup kirim `license_key` saja. Untuk POS desktop yang ingin device locking, kirim juga `device_id`.

### POST `/api/license/activate`

Aktivasi lisensi. Jika `device_id` dikirim, lisensi akan di-lock ke device tersebut.

**Request (minimal — untuk aplikasi web):**

```json
{
  "license_key": "ABCDE12345FGHIJ67890"
}
```

**Request (dengan device locking — untuk POS desktop):**

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
    "device_id": null,
    "activated_at": "2026-02-27 10:30:00",
    "expires_at": "2026-03-29 10:30:00",
    "status": "active"
  }
}
```

**Parameter:**

| Parameter | Tipe | Wajib | Deskripsi |
|-----------|------|-------|-----------|
| `license_key` | string | **Ya** | License key 20 karakter |
| `device_id` | string | Tidak | Identifier perangkat (opsional, untuk device locking) |

**Response (error):**

| HTTP Code | Kondisi |
|-----------|---------|
| `400` | `license_key` kosong |
| `404` | Lisensi tidak ditemukan |
| `403` | Lisensi tidak aktif / expired / sudah di-lock ke device lain |

### POST `/api/license/check`

Cek status dan masa aktif lisensi. Jika `device_id` dikirim dan lisensi sudah di-lock, akan divalidasi kecocokannya.

**Request (minimal — untuk aplikasi web):**

```json
{
  "license_key": "ABCDE12345FGHIJ67890"
}
```

**Request (dengan device check — untuk POS desktop):**

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
    "device_id": null,
    "activated_at": "2026-02-27 10:30:00",
    "expires_at": "2026-03-29 10:30:00",
    "status": "active",
    "is_active": true,
    "days_remaining": 30
  }
}
```

**Parameter:**

| Parameter | Tipe | Wajib | Deskripsi |
|-----------|------|-------|-----------|
| `license_key` | string | **Ya** | License key 20 karakter |
| `device_id` | string | Tidak | Device ID (opsional, divalidasi jika lisensi sudah di-lock) |

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

## Profil Pelanggan (Customer Profile)

Setiap user (khususnya role **User** / pelanggan) memiliki data profil tambahan yang tersimpan di tabel `customer_profiles` (relasi 1:1 dengan `users`).

### Tabel `customer_profiles`

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | INT (PK) | Primary key |
| `user_id` | INT (FK → users.id, UNIQUE) | Relasi ke tabel users |
| `nama_usaha` | VARCHAR(255) | Nama toko / usaha pelanggan |
| `no_telp` | VARCHAR(20) | Nomor HP / telepon |
| `propinsi` | VARCHAR(100) | Propinsi |
| `kabupaten` | VARCHAR(100) | Kabupaten / Kota |
| `created_at` | DATETIME | Timestamp dibuat |
| `updated_at` | DATETIME | Timestamp diperbarui |

### Integrasi

Profil pelanggan terintegrasi di 3 tempat:

| Halaman | Deskripsi |
|---------|-----------|
| **Register** (`/register`) | Form registrasi menyertakan field profil pelanggan. Data disimpan otomatis via Shield Event `register`. |
| **Profil** (`/profile`) | User bisa melihat dan mengedit data usahanya sendiri. |
| **Admin Users** (`/admin/users`) | Admin bisa melihat (di DataTable) dan mengedit profil pelanggan saat create/edit user. |

### Arsitektur

- **Event-based**: Registrasi menggunakan Shield Event `register` (di `app/Config/Events.php`) untuk menyimpan profil tanpa mengubah kode Shield.
- **Table Extension Pattern**: Data profil pelanggan disimpan di tabel terpisah (`customer_profiles`) agar tidak mengganggu tabel `users` milik Shield.
- **`CustomerProfileModel`**: Menyediakan helper `getByUserId()` dan `saveProfile()` untuk operasi CRUD.

---

## Modul Canvassing (Sales Management)

Modul canvassing memungkinkan **Manager** (sales) mengelola customer yang di-assign kepadanya — membuat order, upload bukti bayar, mengelola lisensi, membuat trial lisensi, serta approve/reject order. Semua aktivitas tercatat dalam log aktivitas.

### Alur Kerja (Flow)

```
Admin assign Customer ke Manager
    → Manager lihat data Customer di dashboard
    → Manager buat Order untuk Customer
    → Manager upload Bukti Bayar
    → Manager approve/reject Order
        → Approve → License Key otomatis di-generate
        → Reject → Order dibatalkan
    → Manager bisa buat Trial Lisensi untuk Customer
    → Manager bisa request perpanjangan Lisensi
```

### Akses Berdasarkan Role

| Fitur | Superadmin | Admin | Manager | User |
|-------|-----------|-------|---------|------|
| Assign customer ke manager | ✅ | ✅ | ❌ | ❌ |
| Dashboard canvassing | ✅ | ❌ | ✅ | ❌ |
| Lihat customer yang di-assign | ✅ | ✅ | ✅ | ❌ |
| Buat order untuk customer | ✅ | ❌ | ✅ | ❌ |
| Upload bukti bayar customer | ✅ | ❌ | ✅ | ❌ |
| Approve/Reject order | ✅ | ❌ | ✅ | ❌ |
| Kelola lisensi customer | ✅ | ❌ | ✅ | ❌ |
| Buat trial lisensi | ✅ | ❌ | ✅ | ❌ |
| Log aktivitas canvassing | ✅ | ❌ | ✅ | ❌ |

### Permissions

| Permission | Deskripsi |
|-----------|-----------|
| `canvassing.dashboard` | Dapat mengakses dashboard canvassing |
| `canvassing.customers.list` | Dapat melihat daftar customer |
| `canvassing.customers.view` | Dapat melihat detail customer |
| `canvassing.orders.list` | Dapat melihat order customer |
| `canvassing.orders.create` | Dapat membuat order untuk customer |
| `canvassing.orders.approve` | Dapat menyetujui order customer |
| `canvassing.orders.reject` | Dapat menolak order customer |
| `canvassing.payments.upload` | Dapat upload bukti bayar untuk customer |
| `canvassing.licenses.list` | Dapat melihat lisensi customer |
| `canvassing.licenses.renew` | Dapat request perpanjangan lisensi customer |
| `canvassing.trials.list` | Dapat melihat trial lisensi customer |
| `canvassing.trials.create` | Dapat membuat trial lisensi untuk customer |
| `canvassing.trials.view` | Dapat melihat detail trial lisensi customer |
| `canvassing.activity.view` | Dapat melihat log aktivitas canvassing |
| `canvassing.assign` | Dapat assign customer ke manager (khusus Admin) |

### Database Schema

#### Tabel `manager_customers`

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | INT (PK) | Primary key |
| `manager_id` | INT (FK → users.id) | Manager yang mengelola |
| `customer_id` | INT (FK → users.id) | Customer yang di-assign |
| `assigned_at` | DATETIME | Waktu assignment |
| `status` | ENUM('active', 'inactive') | Status assignment |
| `notes` | TEXT | Catatan tambahan |
| `created_at` | DATETIME | Timestamp dibuat |
| `updated_at` | DATETIME | Timestamp diperbarui |

> **Unique Constraint:** Kombinasi (`manager_id`, `customer_id`) harus unik.

#### Tabel `manager_activity_logs`

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | INT (PK) | Primary key |
| `manager_id` | INT (FK → users.id) | Manager yang melakukan aksi |
| `customer_id` | INT (FK → users.id) | Customer yang terkait |
| `action_type` | ENUM | Jenis aksi (lihat tabel di bawah) |
| `reference_id` | INT | ID referensi (order/license/payment) |
| `reference_type` | VARCHAR(50) | Tipe referensi: `order`, `license`, `payment_confirmation` |
| `description` | TEXT | Deskripsi aksi |
| `ip_address` | VARCHAR(45) | IP address manager |
| `created_at` | DATETIME | Timestamp aksi |

**Action Types:**

| Action Type | Label | Ikon |
|------------|-------|------|
| `create_order` | Buat Order | `fa-cart-plus` |
| `upload_payment` | Upload Bukti Bayar | `fa-upload` |
| `manage_license` | Kelola Lisensi | `fa-key` |
| `view_profile` | Lihat Profil | `fa-eye` |
| `assign_customer` | Assign Customer | `fa-user-plus` |
| `unassign_customer` | Unassign Customer | `fa-user-minus` |
| `create_trial` | Buat Trial | `fa-flask` |
| `approve_order` | Setujui Order | `fa-check-circle` |
| `reject_order` | Tolak Order | `fa-times-circle` |

#### Kolom Tambahan pada Tabel Existing

| Tabel | Kolom Baru | Tipe | Deskripsi |
|-------|-----------|------|-----------|
| `orders` | `created_by_manager_id` | INT (nullable) | Manager yang membuat order |
| `payment_confirmations` | `uploaded_by_manager_id` | INT (nullable) | Manager yang upload bukti bayar |

### Migrations

| File | Deskripsi |
|------|-----------|
| `2026-03-15-020000_CreateManagerCustomersTable` | Membuat tabel `manager_customers` |
| `2026-03-15-030000_CreateManagerActivityLogsTable` | Membuat tabel `manager_activity_logs` |
| `2026-03-15-040000_AddManagerFieldsToOrdersAndPayments` | Menambah kolom manager di `orders` & `payment_confirmations` |
| `2026-03-15-050000_AddCreateTrialToActivityLogEnum` | Menambah `create_trial` ke ENUM `action_type` |
| `2026-03-15-060000_AddApproveRejectToActivityLogEnum` | Menambah `approve_order` & `reject_order` ke ENUM `action_type` |

### Struktur Controller & Route

#### Route Manager (prefix: `/canvassing`)

Semua route dalam group ini dilindungi filter `permission:canvassing.dashboard`.

```php
// Dashboard & Activity Log
GET  /canvassing/dashboard                              → CanvassingDashboardController::index
GET  /canvassing/activity-log                           → CanvassingDashboardController::activityLog
GET  /canvassing/activity-log/ajax                      → CanvassingDashboardController::activityLogAjax

// Customer Management
GET  /canvassing/my-customers                           → CustomerController::index
GET  /canvassing/my-customers/ajax                      → CustomerController::ajax
GET  /canvassing/my-customers/(:num)                    → CustomerController::detail/$1

// Order Management
GET  /canvassing/customer-orders                        → CustomerOrderController::index
GET  /canvassing/customer-orders/ajax                   → CustomerOrderController::ajax
GET  /canvassing/customer-orders/create/(:num)          → CustomerOrderController::create/$1
POST /canvassing/customer-orders/store/(:num)           → CustomerOrderController::store/$1
GET  /canvassing/customer-orders/view/(:segment)        → CustomerOrderController::view/$1
POST /canvassing/customer-orders/approve/(:segment)     → CustomerOrderController::approve/$1
POST /canvassing/customer-orders/reject/(:segment)      → CustomerOrderController::reject/$1

// Payment Proof
GET  /canvassing/customer-orders/upload-proof/(:segment)  → CustomerPaymentController::uploadForm/$1
POST /canvassing/customer-orders/submit-proof/(:segment)  → CustomerPaymentController::submitProof/$1

// License Management
GET  /canvassing/customer-licenses                      → CustomerLicenseController::index
GET  /canvassing/customer-licenses/ajax                 → CustomerLicenseController::ajax
GET  /canvassing/customer-licenses/(:segment)           → CustomerLicenseController::detail/$1
GET  /canvassing/customer-licenses/renew/(:segment)     → CustomerLicenseController::renew/$1
POST /canvassing/customer-licenses/store-renewal/(:segment) → CustomerLicenseController::storeRenewal/$1

// Trial License
GET  /canvassing/customer-trials                        → CustomerTrialController::index
GET  /canvassing/customer-trials/ajax                   → CustomerTrialController::ajax
GET  /canvassing/customer-trials/create/(:num)          → CustomerTrialController::create/$1
POST /canvassing/customer-trials/store/(:num)           → CustomerTrialController::store/$1
GET  /canvassing/customer-trials/view/(:segment)        → CustomerTrialController::view/$1
```

#### Route Admin (prefix: `/admin/canvassing-assign`)

Dilindungi filter `permission:admin.access` + `permission:canvassing.assign`.

```php
GET  /admin/canvassing-assign/                          → AssignController::index
GET  /admin/canvassing-assign/ajax                      → AssignController::ajax
POST /admin/canvassing-assign/store                     → AssignController::store
POST /admin/canvassing-assign/remove/(:num)             → AssignController::remove/$1
```

### Struktur File

```
app/
├── Controllers/
│   └── Canvassing/
│       ├── CanvassingDashboardController.php   # Dashboard & log aktivitas
│       ├── CustomerController.php              # Kelola customer
│       ├── CustomerOrderController.php         # Kelola order customer
│       ├── CustomerPaymentController.php       # Upload bukti bayar
│       ├── CustomerLicenseController.php       # Kelola lisensi customer
│       ├── CustomerTrialController.php         # Kelola trial lisensi
│       └── AssignController.php                # Admin: assign customer
├── Models/
│   ├── ManagerCustomerModel.php                # Model assignment manager-customer
│   └── ManagerActivityLogModel.php             # Model log aktivitas
├── Database/
│   └── Migrations/
│       ├── 2026-03-15-020000_CreateManagerCustomersTable.php
│       ├── 2026-03-15-030000_CreateManagerActivityLogsTable.php
│       ├── 2026-03-15-040000_AddManagerFieldsToOrdersAndPayments.php
│       ├── 2026-03-15-050000_AddCreateTrialToActivityLogEnum.php
│       └── 2026-03-15-060000_AddApproveRejectToActivityLogEnum.php
└── Views/
    └── canvassing/
        ├── dashboard.php                       # Dashboard manager
        ├── activity_log.php                    # Log aktivitas + filter
        ├── assign/
        │   └── index.php                      # Admin: halaman assign
        ├── customers/
        │   ├── index.php                      # Daftar customer
        │   └── detail.php                     # Detail customer
        ├── orders/
        │   ├── index.php                      # Daftar order
        │   ├── create.php                     # Form buat order
        │   ├── view.php                       # Detail order + approve/reject
        │   └── upload_proof.php               # Form upload bukti bayar
        ├── licenses/
        │   ├── index.php                      # Daftar lisensi
        │   ├── detail.php                     # Detail lisensi
        │   └── renew.php                      # Form perpanjangan
        └── trials/
            ├── index.php                      # Daftar trial
            ├── create.php                     # Form buat trial
            └── view.php                       # Detail trial
```

### Menu Sidebar

#### Untuk Manager

```
📋 Canvassing
├── Dashboard          (fas fa-tachometer-alt)
├── Customer Saya      (fas fa-users)
├── Order Customer     (fas fa-shopping-cart)
├── Lisensi Customer   (fas fa-key)
├── Trial Lisensi      (fas fa-flask)
└── Log Aktivitas      (fas fa-history)
```

#### Untuk Admin

```
📋 Canvassing
└── Assign Customer    (fas fa-user-friends)
```

### Fitur Dashboard Manager

Dashboard manager menampilkan:

| Stat Card | Deskripsi |
|-----------|-----------|
| Total Customer | Jumlah customer yang di-assign |
| Total Order | Jumlah order dari customer |
| Trial Aktif | Jumlah trial lisensi yang masih aktif |
| Dibatalkan | Jumlah order yang dibatalkan/ditolak |

Ditambah:
- **Tabel Aktivitas Terbaru** — 10 aktivitas terakhir dengan badge warna per action type
- **Quick Links** — Shortcut ke halaman canvassing utama

### Log Aktivitas

Halaman log aktivitas mendukung filter:
- **Rentang Tanggal** — Filter berdasarkan datetime range
- **Customer** — Filter berdasarkan customer tertentu (Select2 dropdown)
- **Jenis Aksi** — Filter berdasarkan action type (Select2 dropdown)

---

## Lisensi

MIT License
