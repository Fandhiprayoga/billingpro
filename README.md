# billingpro — Sistem Manajemen Lisensi & Billing

**billingpro** adalah aplikasi web berbasis **SaaS (Software as a Service)** untuk manajemen lisensi, billing, dan canvassing. Aplikasi ini memungkinkan pengelolaan paket layanan, pembuatan pesanan, konfirmasi pembayaran manual, penerbitan lisensi, serta manajemen pelanggan oleh tim canvassing (manager).

## Fitur Utama

- **Dashboard** — Ringkasan statistik dan overview sistem
- **Manajemen Pengguna** — CRUD pengguna dengan Role-Based Access Control (RBAC) dan 4 role: Superadmin, Admin, Manager, User
- **Manajemen Paket/Plan** — Buat dan kelola paket layanan dengan harga dan durasi
- **Manajemen Pesanan/Order** — Alur pemesanan, konfirmasi pembayaran manual (upload bukti transfer), dan persetujuan admin
- **Manajemen Lisensi** — Penerbitan license key unik (format: XXXXX-XXXXX-XXXXX-XXXXX), aktivasi, pencabutan, dan perpanjangan
- **Trial License** — Pembuatan lisensi percobaan oleh admin untuk pelanggan
- **Modul Canvassing** — Manager dapat mengelola pelanggan, membuat pesanan atas nama pelanggan, upload bukti pembayaran, dan memantau lisensi pelanggan
- **Laporan** — Laporan pendapatan berdasarkan rentang tanggal, export ke file/PDF
- **API Publik** — Endpoint untuk aktivasi dan pengecekan lisensi dari aplikasi eksternal (POS/Web)
- **Pengaturan Sistem** — Konfigurasi umum, autentikasi, email, branding (favicon/logo)
- **Dokumentasi API** — Halaman referensi endpoint API bawaan

## Tech Stack

### Backend

| Teknologi | Versi | Keterangan |
|---|---|---|
| **PHP** | 8.2+ | Bahasa pemrograman utama |
| **CodeIgniter 4** | ^4.7 | Framework web full-stack |
| **CodeIgniter Shield** | ^1.2 | Sistem autentikasi & otorisasi (RBAC) |
| **MySQL** | 5.7+ / 8.x | Database relasional |
| **Composer** | - | Dependency manager PHP |

### Frontend

| Teknologi | Keterangan |
|---|---|
| **Bootstrap 4** | CSS framework untuk layout responsif |
| **Stisla** | Template dashboard admin |
| **DataTables** | Tabel interaktif (sorting, searching, pagination) |
| **jQuery** | Library JavaScript |
| **Select2** | Enhanced select dropdown |
| **Font Awesome 5** | Icon library |
| **Moment.js** | Pengelolaan format tanggal/waktu |

### Testing

| Teknologi | Keterangan |
|---|---|
| **PHPUnit 10** | Unit testing framework |
| **FakerPHP** | Generasi data dummy untuk testing |

## Persyaratan Sistem

- **PHP** versi 8.2 atau lebih tinggi
- **MySQL** versi 5.7+ atau 8.x
- **Composer** terinstal secara global
- **Web Server**: Apache (dengan mod_rewrite) atau Nginx

Ekstensi PHP yang diperlukan:
- `intl`
- `mbstring`
- `json` (aktif secara default)
- `mysqlnd`
- `curl`

## Instalasi

### 1. Clone Repository

```bash
git clone <url-repository> posbilling
cd posbilling
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Konfigurasi Environment

Salin file `env` menjadi `.env`, kemudian sesuaikan konfigurasinya:

```bash
cp env .env
```

Edit file `.env` dan sesuaikan:

```env
# Base URL aplikasi
app.baseURL = 'http://localhost:8080/'

# Konfigurasi Database
database.default.hostname = localhost
database.default.database = posbilling
database.default.username = root
database.default.password = your_password
database.default.DBDriver = MySQLi
database.default.port = 3306
```

### 4. Buat Database

Buat database MySQL baru sesuai nama yang dikonfigurasi di `.env`:

```sql
CREATE DATABASE posbilling CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

### 5. Jalankan Migrasi & Seeder

```bash
php spark migrate
php spark db:seed
```

### 6. Jalankan Aplikasi

```bash
php spark serve
```

Aplikasi akan berjalan di `http://localhost:8080`.

### 7. Konfigurasi Web Server (Production)

Arahkan document root web server ke folder `public/` pada project, **bukan** ke root project. Contoh konfigurasi virtual host Apache:

```apache
<VirtualHost *:80>
    ServerName posbilling.local
    DocumentRoot /path/to/posbilling/public

    <Directory /path/to/posbilling/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## Instalasi dengan Docker

Sebagai alternatif instalasi manual, project ini menyediakan konfigurasi Docker lengkap dengan 3 service: aplikasi (PHP 8.2 + Apache), database (MariaDB 10.11), dan phpMyAdmin.

### Prasyarat

- [Docker](https://docs.docker.com/get-docker/) dan [Docker Compose](https://docs.docker.com/compose/install/) terinstal

### Arsitektur Container

| Service | Container | Image | Port |
|---------|-----------|-------|------|
| **app** | `app-billingpro` | PHP 8.2 + Apache | `8080` |
| **billingpro-mysql** | `billingpro_mariadb` | MariaDB 10.11 | `3306` |
| **phpmyadmin** | `pma_billingpro` | phpMyAdmin | `8081` |

### Langkah Instalasi

**1. Konfigurasi Environment**

```bash
cp env .env
```

Edit `.env` untuk Docker:

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

> **Penting:** Hostname harus menggunakan nama service Docker (`billingpro-mysql`), bukan `localhost`.

**2. Build & Jalankan**

```bash
docker compose up --build -d
```

**3. Install Dependencies**

```bash
docker exec app-billingpro composer install
```

**4. Migrasi & Seeder**

```bash
docker exec app-billingpro php spark migrate --all
docker exec app-billingpro php spark db:seed UserSeeder
docker exec app-billingpro php spark db:seed PlanSeeder
```

**5. Akses Aplikasi**

| Layanan | URL |
|---------|-----|
| Aplikasi | http://localhost:8080 |
| phpMyAdmin | http://localhost:8081 |

### Perintah Docker Berguna

```bash
docker compose ps                          # Cek status container
docker compose logs -f app                 # Log aplikasi
docker exec -it app-billingpro bash        # Shell container
docker exec app-billingpro php spark ...   # Jalankan perintah spark
docker compose down                        # Hentikan container
docker compose down -v                     # Hentikan + hapus volume (reset DB)
```

## API Endpoints

Aplikasi menyediakan API publik (tanpa autentikasi) untuk integrasi dengan aplikasi POS/Web eksternal:

| Method | Endpoint | Keterangan |
|---|---|---|
| `POST` | `/api/license/activate` | Aktivasi lisensi dari aplikasi eksternal |
| `POST` | `/api/license/check` | Verifikasi validitas lisensi |

## Kontak

Jika mengalami kendala atau memiliki pertanyaan terkait aplikasi ini, silakan hubungi:

📧 **Email**: [fduga2@gmail.com](mailto:fduga2@gmail.com)
