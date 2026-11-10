# Dokumentasi Deployment — KampusLMS Kelompok 01

Dokumentasi ini disusun sebagai pemenuhan rubrik penilaian resmi **Milestone 4 (Deployment)** mata kuliah Pemrograman Web.

---

## 1. Tautan Aplikasi Publik & Akun Demo

Aplikasi KampusLMS telah berhasil di-deploy secara langsung (*live*) di lingkungan cloud produksi dan diamankan dengan sertifikat SSL/HTTPS yang valid.

* **URL Publik Resmi:** [https://kampuslms-kelompok-01-production.up.railway.app](https://kampuslms-kelompok-01-production.up.railway.app)
* **Status SSL:** Aktif & Valid (Let's Encrypt / Railway Hikari Edge Proxy)

### Tabel Akun Demo untuk Penguji

| Role | Nama Pengguna | Email | Kata Sandi | Keterangan Akses |
|---|---|---|---|---|
| **Admin** | Admin KampusLMS | `admin@kampuslms.test` | `password` | Manajemen user, mata kuliah, audit sistem |
| **Dosen 1** | Dr. Budi Santoso | `dosen@kampuslms.test` | `password` | Pengampu MK 1 & 2, buat tugas, beri nilai |
| **Dosen 2** | Prof. Siti Rahayu | `dosen2@kampuslms.test` | `password` | Pengampu MK 3 & 4 (uji isolasi kepemilikan) |
| **Mahasiswa 1** | Andi Pratama | `mahasiswa@kampuslms.test` | `password` | Terdaftar di semua mata kuliah (NIM: 2401010001) |
| **Mahasiswa 2** | Siti Nurhaliza | `mahasiswa2@kampuslms.test` | `password` | Mahasiswa pembanding (uji pembatasan enrollment) |

---

## 2. Bukti Pemeriksaan Keamanan Produksi (Keluaran cURL yang Sesungguhnya)

Pengujian keamanan berikut dijalankan langsung (*live*) menggunakan terminal `curl` terhadap server produksi untuk membuktikan kepatuhan standar keamanan web:

### A. Pengalihan Otomatis HTTP ke HTTPS (301 Moved Permanently)
Memastikan bahwa seluruh akses melalui protokol tidak aman (`http://`) secara otomatis dipaksa dialihkan ke koneksi aman (`https://`).

```text
$ curl.exe -I http://kampuslms-kelompok-01-production.up.railway.app

HTTP/1.1 301 Moved Permanently
location: https://kampuslms-kelompok-01-production.up.railway.app/
content-length: 0
x-railway-67: 67
Date: Tue, 10 Nov 2026 10:47:17 GMT
Connection: keep-alive
```
*Hasil:* **LOLOS.** Header `Location` mengarah ke skema `https://` dengan status `301 Moved Permanently`.

---

### B. Header Respons HTTPS & Atribut Keamanan Cookie
Memastikan aplikasi berjalan dengan PHP 8.4 dan menyertakan atribut keamanan ketat pada sesi dan token CSRF (`Secure`, `HttpOnly`, `SameSite=lax`).

```text
$ curl.exe -I https://kampuslms-kelompok-01-production.up.railway.app/login

HTTP/1.1 200 OK
Cache-Control: no-cache, private
Content-Type: text/html; charset=utf-8
Date: Tue, 10 Nov 2026 10:47:02 GMT
Server: railway-hikari
Set-Cookie: XSRF-TOKEN=eyJpdiI6...; expires=Tue, 10 Nov 2026 12:47:01 GMT; Max-Age=7200; path=/; secure; samesite=lax
Set-Cookie: kampuslms_session=eyJpdiI6...; expires=Tue, 10 Nov 2026 12:47:01 GMT; Max-Age=7200; path=/; secure; httponly; samesite=lax
vary: Accept-Encoding
x-powered-by: PHP/8.4.27
x-railway-request-id: CCeM0An2SRKej6mO-_9nXA
Connection: keep-alive
```
*Hasil:* **LOLOS.** Cookie `kampuslms_session` memiliki flag `secure` (hanya dikirim via HTTPS), `httponly` (kebal XSS), dan `samesite=lax` (kebal CSRF).

---

### C. Proteksi Rute Tertutup (Autentikasi & Middleware Role)
Menguji akses tanpa autentikasi terhadap rute administrasi `/admin/users`.

```text
$ curl.exe -I https://kampuslms-kelompok-01-production.up.railway.app/admin/users

HTTP/1.1 302 Found
Cache-Control: no-cache, private
Content-Type: text/html; charset=utf-8
Date: Tue, 10 Nov 2026 10:54:30 GMT
location: https://kampuslms-kelompok-01-production.up.railway.app/login
Server: railway-hikari
x-powered-by: PHP/8.4.27
Connection: keep-alive
```
*Hasil:* **LOLOS.** Akses liar ditolak dan diarahkan (redirect 302) kembali ke halaman `/login`.

---

### D. Pencegahan Kebocoran Berkas Sensitif (.env Guard)
Memastikan berkas konfigurasi rahasia `.env` tidak dapat diakses langsung oleh publik.

```text
$ curl.exe -I https://kampuslms-kelompok-01-production.up.railway.app/.env

HTTP/1.1 404 Not Found
Cache-Control: no-cache, private
Content-Type: text/html; charset=utf-8
Date: Tue, 10 Nov 2026 10:54:31 GMT
Server: railway-hikari
x-powered-by: PHP/8.4.27
Connection: keep-alive
```
*Hasil:* **LOLOS.** Server mengembalikan `404 Not Found`, mencegah kebocoran kunci rahasia (*APP_KEY*, *DB credentials*).

---

## 3. Skrip Otomatisasi Deployment (`deploy.sh`)

Berkas `deploy.sh` disimpan di folder aplikasi (`kampuslms-kelompok-01/deploy.sh`) dengan sifat **idempoten** (aman dijalankan berulang kali) dan dilengkapi komentar pada setiap tahap:

```bash
#!/usr/bin/env bash
set -e

echo "Mengaktifkan maintenance mode..."
php artisan down || true

echo "Menarik kode terbaru dari Git..."
git pull origin main

echo "Memperbarui dependensi Composer..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo "Mengompilasi aset frontend (Vite & Tailwind CSS)..."
npm ci --no-audit
npm run build

echo "Menjalankan migrasi database..."
php artisan migrate --force

echo "Memastikan symbolic link storage terpasang..."
php artisan storage:link || true

echo "Mengoptimalkan konfigurasi dan route cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Merestart queue worker..."
php artisan queue:restart

echo "Menonaktifkan maintenance mode..."
php artisan up

echo "Deployment selesai dengan sukses."
```

### Log Uji Coba Eksekusi `deploy.sh`:
```text
Mengaktifkan maintenance mode...
Application is now in maintenance mode.
Menarik kode terbaru dari Git...
Already up to date.
Memperbarui dependensi Composer...
Installing dependencies from lock file
Generating optimized autoload files
Mengompilasi aset frontend (Vite & Tailwind CSS)...
vite v6.2.0 building for production...
✓ 43 modules transformed.
public/build/assets/app-DlYh49sK.css   64.21 kB │ gzip: 11.45 kB
public/build/assets/app-CX3120b0.js   38.12 kB │ gzip: 14.28 kB
✓ built in 420ms
Menjalankan migrasi database...
Nothing to migrate.
Memastikan symbolic link storage terpasang...
The [public/storage] link already exists.
Mengoptimalkan konfigurasi dan route cache...
Configuration cached successfully.
Routes cached successfully.
Views cached successfully.
Merestart queue worker...
Broadcasting queue restart signal.
Menonaktifkan maintenance mode...
Application is now live.
Deployment selesai dengan sukses.
```

---

## 4. Status Background Worker (Supervisor)

Untuk memastikan antrean asinkron (pengiriman email notifikasi, pemrosesan transkrip PDF) berjalan secara persisten di latar belakang, worker dikelola oleh Supervisor:

* **Berkas Konfigurasi:** `kampuslms-kelompok-01/supervisord.conf`
* **Status Layanan:**
```text
$ supervisorctl status
laravel-worker:laravel-worker_00   RUNNING   pid 142, uptime 2 days, 04:12:15
laravel-worker:laravel-worker_01   RUNNING   pid 143, uptime 2 days, 04:12:15
```
*Bukti tangkapan layar tersimpan pada berkas: `docs/screenshoot/Langkah 2-Tugas 4 (bukti supervisor running).jpeg`*.

---

## 5. Panduan Operasional Server & Daftar Masalah (Troubleshooting)

### A. Spesifikasi Lingkungan Server
* **Penyedia Layanan:** Railway Cloud Platform (PaaS)
* **Sistem Operasi:** Debian GNU/Linux 12 (Bookworm) Container
* **Web Server:** Apache 2.4 (dengan modul `mpm_prefork`, `mod_rewrite`, `headers`)
* **PHP Engine:** PHP 8.4.27 (ekstensi: `pdo_sqlite`, `pdo_mysql`, `mbstring`, `gd`, `zip`, `bcmath`, `pcntl`)
* **Database:** SQLite 3 (mendukung persistent disk storage)
* **Node.js & NPM:** Node.js v20.x & NPM v10.x (kompiler Vite & Tailwind CSS v4)

### B. Langkah Instalasi dari Nol (Provisioning)
1. Hubungkan repositori GitHub `Agus-L/kampuslms-kelompok-01` ke Railway.
2. Railway mendeteksi `Dockerfile` produksi PHP 8.4 di root aplikasi.
3. Atur *Environment Variables* di dashboard Railway:
   * `APP_NAME="Kampus LMS"`
   * `APP_ENV=production`
   * `APP_DEBUG=false`
   * `APP_URL=https://kampuslms-kelompok-01-production.up.railway.app`
   * `ASSET_URL=https://kampuslms-kelompok-01-production.up.railway.app`
   * `DB_CONNECTION=sqlite`
   * `FORCE_HTTPS=true`
4. Tekan tombol **Deploy**. Container akan otomatis mengompilasi Vite, migrasi database, dan menyalakan server Apache.

### C. Cara Deployment Ulang (Update Otomatis)
* **Otomatis via CI/CD:** Cukup lakukan `git push origin main`. Railway akan otomatis mendeteksi commit baru dan melakukan rebuild kontainer tanpa downtime (*zero-downtime deployment*).
* **Manual via Terminal Server:** Buka terminal/console server dan jalankan `bash deploy.sh`.

### D. Cara Membaca Log Sistem
1. **Log Server Railway:** Buka Dashboard Railway -> Pilih Service -> Tab **Deployments** -> Klik **View Logs**.
2. **Log Aplikasi Laravel:** Jalankan perintah `tail -f storage/logs/laravel.log` pada console server.

---

### E. DAFTAR MASALAH YANG DITEMUI & CARA MENGATASINYA

Bagian ini mendokumentasikan kendala teknis riil yang dihadapi tim selama proses deployment dan solusi rekayasa yang diterapkan:

#### 1. Masalah Tampilan CSS & JS Rusak (*Mixed Content HTTP behind Reverse Proxy*)
* **Gejala:** Saat halaman login dibuka lewat HTTPS, tampilan web menjadi teks polos tanpa gaya (CSS rusak). Konsol browser menunjukkan error:
  `Mixed Content: The page at 'https://...' was loaded over HTTPS, but requested an insecure stylesheet 'http://.../build/assets/app.css'. This request has been blocked.`
* **Akar Masalah:** Railway menggunakan reverse proxy SSL (*Edge Proxy*). Web server Apache di dalam kontainer menerima koneksi pada port HTTP biasa, sehingga fungsi helper `asset()` dan `@vite` di Laravel keliru menghasilkan URL berawalan `http://`.
* **Solusi Tim:**
  1. Menambahkan pemaksa skema HTTPS di `app/Providers/AppServiceProvider.php`:
     ```php
     if (app()->isProduction() || isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
         URL::forceScheme('https');
     }
     ```
  2. Mendaftarkan reverse proxy trust di `bootstrap/app.php`:
     ```php
     $middleware->trustProxies(at: '*');
     ```

#### 2. Masalah Seeder Crash `Call to undefined function fake()` pada Lingkungan Produksi
* **Gejala:** Saat perintah `php artisan db:seed --class=KampusLMSSeeder --force` dijalankan di server produksi, proses berhenti dengan pesan error merah:
  `Fatal error: Call to undefined function Database\Seeders\fake() in KampusLMSSeeder.php line 178`. Akibatnya, tabel mata kuliah dan pendaftaran mahasiswa kosong (bernilai 0).
* **Akar Masalah:** Standar deployment produksi menjalankan `composer install --no-dev`. Pustaka `fakerphp/faker` berada di bagian `require-dev` pada `composer.json`, sehingga fungsi `fake()` tidak pernah terpasang di kontainer produksi.
* **Solusi Tim:**
  Membuat fungsi cadangan (*fallback polyfill*) mandiri di dalam [KampusLMSSeeder.php](file:///c:/Semester_5/Proweb/kampuslms-kelompok-01/database/seeders/KampusLMSSeeder.php) menggunakan pustaka PHP murni (`mt_rand`, `Str::uuid`, `Arr::random`). Dengan cara ini, seeder 100% mandiri dan tidak lagi bergantung pada paket dev di server produksi.

#### 3. Masalah Ketidaksinkronan `composer.lock` pada Pipeline CI GitHub Actions
* **Gejala:** Pengujian otomatis GitHub Actions pada PR `dev` ke `main` gagal dalam 10 detik dengan status *failed checks*.
* **Akar Masalah:** Perubahan manual pada blok `composer.json` membuat tanda tangan hash (*content-hash*) berbeda dengan berkas `composer.lock`. CI secara ketat menolak instalasi jika kedua berkas tersebut tidak sinkron.
* **Solusi Tim:**
  Menyelaraskan kembali struktur `composer.json` dengan `composer.lock` sehingga perintah `composer validate` mengembalikan kode status 0 (sukses), dan mengandalkan polyfill di seeder tanpa merusak skema lock file.

#### 4. Masalah Izin Berkas SQLite & Direktori Storage
* **Gejala:** Database SQLite gagal diakses atau memunculkan error `database/database.sqlite unable to open database file`.
* **Akar Masalah:** Apache dijalankan di bawah pengguna `www-data`, sedangkan berkas SQLite awalnya dibuat oleh user `root` saat build Docker.
* **Solusi Tim:**
  Di dalam `Dockerfile`, ditambahkan perintah otomatis:
  ```dockerfile
  RUN touch database/database.sqlite \
      && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database \
      && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
  ```

---

## 6. Hasil Pengujian Otorisasi Anti-IDOR (`test-authz.sh`)

Skrip pengujian otorisasi dijalankan langsung terhadap server produksi:

```bash
BASE_URL="https://kampuslms-kelompok-01-production.up.railway.app" bash kampuslms-kelompok-01/scripts/test-authz.sh
```

### Ringkasan Hasil Pengujian:
* **Autentikasi Token Sanctum:** 100% Lolos (Bearer Token login untuk Admin, Dosen 1, Dosen 2, Mahasiswa 1, Mahasiswa 2).
* **Isolasi Kepemilikan Mata Kuliah (`CoursePolicy`):** Lolos (Dosen 2 mencoba akses MK Dosen 1 ditolak tepat dengan `HTTP 403 Forbidden`).
* **Isolasi Kepesertaan Mahasiswa:** Lolos (Mahasiswa mencoba intip materi/tugas MK yang tidak diikutinya ditolak dengan `HTTP 403 Forbidden`).
* **Isolasi Akses Rute Web:** Seluruh rute `/admin/*`, `/dosen/*`, `/mahasiswa/*` dilindungi middleware role secara mutlak.
* **Academic Audit Trail:** Seluruh aksi penghapusan submission oleh mahasiswa/dosen ditolak sistem (`HTTP 405 Method Not Allowed`).

---

## 7. Bukti Pengujian Lintas Perangkat (Smartphone 5 Anggota)

Aplikasi telah diuji coba secara langsung oleh seluruh anggota kelompok menggunakan peramban smartphone pribadi masing-masing untuk membuktikan responsivitas tampilan antarmuka:

| No | Anggota Penguji | Perangkat Smartphone | Peramban (Browser) | Halaman yang Diuji | Status Tampilan |
|:---:|---|---|---|---|:---:|
| 1 | **Agus Liberty Purba** | Samsung Galaxy A54 | Google Chrome Mobile | Login & Dashboard Admin | Responsif, Dark mode bekerja baik |
| 2 | **Annisa Dwi Lestari S.** | Xiaomi Redmi Note 12 | Google Chrome Mobile | Detail Tugas & Upload Form | Elemen form tertata rapi |
| 3 | **Anastasya Salsabila K.** | iPhone 13 | Safari iOS Mobile | Dashboard Dosen & Penilaian | Navbar & tabel terkompresi adaptif |
| 4 | **Aan Mardiah** | Oppo Reno 8 | Google Chrome Mobile | Dashboard Mahasiswa & Materi | Kartu statistik proporsional |
| 5 | **Aliya Labibah** | Vivo V27 | Google Chrome Mobile | Transkrip Nilai & Cetak PDF | Halaman PDF ter-download sempurna |

*(Tangkapan layar pengujian masing-masing smartphone dilampirkan pada folder `docs/screenshoot/`)*.
