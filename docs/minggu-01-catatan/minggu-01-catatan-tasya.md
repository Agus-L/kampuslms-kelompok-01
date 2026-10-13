# Catatan Minggu 1

## Identitas Mahasiswa

Nama: Anastasya Salsabila Khoirunnisa  
NIM: 10241009  

---

## READ — Bedah Instalasi Anda Sendiri

Berdasarkan penelusuran arsitektur dasar Laravel 12 pada repositori [KampusLMS](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01), berikut analisis mendalam terhadap 4 aspek telaah siklus hidup (*lifecycle*) request:

---

### 1. Bedah `public/index.php` (Single Entry Point)
- **Berkas:**  
  [`public/index.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/public/index.php)
- **Kode:**
  ```php
  <?php

  use Illuminate\Foundation\Application;
  use Illuminate\Http\Request;

  define('LARAVEL_START', microtime(true));

  // Cek status maintenance mode...
  if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
      require $maintenance;
  }

  // Muat Composer autoloader...
  require __DIR__.'/../vendor/autoload.php';

  // Inisialisasi aplikasi Laravel dan tangani request...
  /** @var Application $app */
  $app = require_once __DIR__.'/../bootstrap/app.php';

  $app->handleRequest(Request::capture());
  ```
- **Fungsi Berkas (3 Kalimat Padat):**  
  1. [`public/index.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/public/index.php) adalah gerbang masuk tunggal (*single entry point*) bagi seluruh HTTP request yang masuk dari web server ke aplikasi Laravel.
  2. Berkas ini mencatat waktu awal eksekusi, memeriksa mode pemeliharaan (*maintenance mode*), dan memuat Composer autoloader untuk pustaka dependensi PHP.
  3. Terakhir, berkas ini memanggil konfigurasi aplikasi dari [`bootstrap/app.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/bootstrap/app.php) dan mengeksekusi `Request::capture()` untuk memproses request menjadi HTTP response yang dikirim kembali ke pengguna.

---

### 2. Bedah `bootstrap/app.php` (Pusat Konfigurasi Laravel 12)
- **Berkas:**  
  [`bootstrap/app.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/bootstrap/app.php)
- **Kode:**
  ```php
  return Application::configure(basePath: dirname(__DIR__))
      ->withRouting(
          web: __DIR__.'/../routes/web.php',
          api: __DIR__.'/../routes/api.php',
          commands: __DIR__.'/../routes/console.php',
          health: '/up',
      )
      ->withMiddleware(function (Middleware $middleware) {
          $middleware->alias([
              'role' => \App\Http\Middleware\EnsureUserHasRole::class,
          ]);
      })
      ->withExceptions(function (Exceptions $exceptions) {
          // Konfigurasi custom response exception API (401, 403, 422)
      })->create();
  ```
- **Tiga Blok Konfigurasi Utama:**
  - **`withRouting(...)`:** Mendaftarkan rute web ([`routes/web.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/routes/web.php)), API ([`routes/api.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/routes/api.php)), command console, serta endpoint bawaan `health: '/up'` untuk health-check server.
  - **`withMiddleware(...)`:** Mengelola middleware global, grup, maupun alias route (contoh: alias `'role'`). Di Laravel 12, berkas `app/Http/Kernel.php` **ditiadakan** dan dipusatkan di closure ini.
  - **`withExceptions(...)`:** Mengatur penanganan error/exception aplikasi secara terpusat (menggantikan `Handler.php`), seperti respons JSON kustom untuk error 401, 403, dan 422.

---

### 3. Bedah `routes/web.php` & Blade
- **Berkas Rute & View:**  
  [`routes/web.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/routes/web.php) dan [`resources/views/welcome.blade.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/resources/views/welcome.blade.php).
- **Kode Rute Awal (`/`):**
  ```php
  Route::get('/', function () {
      return view('welcome');
  })->name('home');
  ```
- **Koneksi Rute ke Tampilan:**  
  Request ke URL root (`/`) diarahkan router untuk merender template Blade di [`welcome.blade.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/resources/views/welcome.blade.php).
- **Pembuktian Modifikasi:**  
  Mengubah teks header pada baris 55 file `welcome.blade.php`:
  ```html
  <!-- Sebelum diubah: -->
  <h1 class="mb-1 font-medium">Let's get started</h1>

  <!-- Setelah diubah: -->
  <h1 class="mb-1 font-medium">Selamat Datang di KampusLMS - Kelompok 01</h1>
  ```
  Saat browser di-*refresh*, teks baru langsung tampil, membuktikan siklus: URL dicocokkan di `web.php` lalu menghasilkan rendering file Blade terkait.

---

### 4. Perbandingan `php artisan route:list` vs `routes/web.php`
- **Penyebab Rute Terminal Lebih Banyak:**  
  - Berkas [`routes/web.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/routes/web.php) hanya memuat rute logika bisnis yang **ditulis manual** oleh pengembang (`/`, `/tentang`, `/login`, dashboard).
  - Perintah `php artisan route:list` menampilkan **seluruh rute aktif** dalam sistem, mencakup rute bawaan framework dan dependensi eksternal:
    1. **`/up`**: Rute internal health-check dari parameter `health: '/up'` di `bootstrap/app.php`.
    2. **`storage/{path}`**: Rute bawaan Laravel Filesystem untuk akses berkas storage publik.
    3. **`sanctum/csrf-cookie`**: Rute autentikasi CSRF dari package Laravel Sanctum.
    4. **`livewire/*`**: Endpoint internal dari package Livewire untuk pertukaran data komponen.

---

## BREAK — Rusak dengan Sengaja

Eksperimen empat skenario kerusakan dilakukan untuk menguji ketahanan konfigurasi, exception handling, dan keamanan pada KampusLMS:

### Tabel Eksperimen 4 Kerusakan

| # | Yang Dirusak | Prediksi Sebelum Mencoba | Pesan Error Sebenarnya & Nama Exception |
|---|---|---|---|
| **1** | Ganti nama `.env` menjadi `.env.bak` | Aplikasi gagal memuat konfigurasi utama dan berhenti berjalan. | `MissingAppKeyException`<br>*"No application encryption key has been specified."* (konfigurasi jatuh ke default `null`). |
| **2** | Kosongkan nilai `APP_KEY` di `.env` (`APP_KEY=`) | Enkripsi data dan verifikasi sesi gagal, memicu error enkripsi. | `Illuminate\Encryption\MissingAppKeyException`<br>*"No application encryption key has been specified."* |
| **3** | Ubah `DB_DATABASE` ke nama yang tidak ada (`db_palsu`) | Halaman statis tetap jalan, tetapi fitur/rute yang melakukan query database akan gagal koneksi. | `Illuminate\Database\QueryException`<br>*"SQLSTATE[HY000] [1049] Unknown database 'db_palsu'"* |
| **4** | Ubah `APP_DEBUG=false`, lalu ulangi nomor 3 | Detail error teknis disembunyikan dan diganti halaman kesalahan umum. | Status HTTP `500 | Server Error`<br>(Halaman generik tanpa rincian database maupun stack trace). |

---

### 1. Ganti Nama `.env` Menjadi `.env.bak`
- **Prediksi Sebelum Mencoba:**  
  Aplikasi gagal membaca variabel environment konfigurasi utama sehingga sistem berhenti beroperasi.
- **Hasil Aktual & Kode Status:**  
  `MissingAppKeyException: No application encryption key has been specified.`
- **Analisis & Risiko:**  
  Ketika `.env` tidak ditemukan, helper `env()` mengembalikan nilai default dari direktori `config/`. Karena default `APP_KEY` adalah `null`, framework menghentikan proses eksekusi.

---

### 2. Kosongkan Nilai `APP_KEY` di `.env`
- **Prediksi Sebelum Mencoba:**  
  Sistem enkripsi Laravel gagal berjalan saat memproses cookie dan session pengguna.
- **Hasil Aktual & Kode Status:**  
  `Illuminate\Encryption\MissingAppKeyException: No application encryption key has been specified.` pada `EncryptionServiceProvider`.
- **Analisis & Risiko:**  
  `APP_KEY` berfungsi sebagai kunci enkripsi AES-256 untuk cookie session, data terenkripsi, dan Message Authentication Code (MAC). Tanpa kunci ini, data sesi tidak dapat diamankan dan aplikasi rentan terhadap pemalsuan session (*session tampering*).

---

### 3. Ubah `DB_DATABASE` ke Nama yang Tidak Ada
- **Prediksi Sebelum Mencoba:**  
  Aplikasi berjalan normal pada rute statis, namun melempar exception saat mengeksekusi query database.
- **Hasil Aktual & Kode Status:**  
  `Illuminate\Database\QueryException: SQLSTATE[HY000] [1049] Unknown database 'db_palsu'` (atau `PDOException`).
- **Analisis & Risiko:**  
  Koneksi PDO ke server database gagal menemukan schema basis data yang dituju saat kueri dijalankan oleh Eloquent/Query Builder.

---

### 4. Ubah `APP_DEBUG=false`, Lalu Ulangi Percobaan Nomor 3
- **Prediksi Sebelum Mencoba:**  
  Detail error teknis disembunyikan dari browser dan digantikan oleh halaman kesalahan generik.
- **Hasil Aktual & Kode Status:**  
  HTTP `500 | Server Error` (Halaman standar tanpa stack trace dan tanpa kredensial).
- **Analisis & Risiko (CWE-200: Information Disclosure):**  
  - Saat `APP_DEBUG=true`, halaman Ignition mengekspos *stack trace* lengkap, cuplikan baris kode, nama database, hingga kredensial `.env`.
  - Jika dibiarkan aktif di server produksi, penyerang cukup memicu satu error untuk membaca rahasia aplikasi secara terbuka.
  - Menyetel `APP_DEBUG=false` di server produksi adalah kewajiban mutlak; detail teknis error disembunyikan dari publik dan hanya dicatat secara aman ke berkas log server ([`storage/logs/laravel.log`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/storage/logs/laravel.log)).
