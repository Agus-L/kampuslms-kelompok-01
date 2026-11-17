# Identitas Mahasiswa

## Nama: Agus Liberty Purba
## NIM: 10241005

---

## READ 

---

### 1. Konfigurasi Web Server & Document Root

* **Lokasi Berkas Konfigurasi:**  
  Dalam arsitektur container Docker kelompok kami, web server menggunakan Apache (`php:8.4-apache`). Seluruh pengaturannya dikonfigurasi melalui berkas `Dockerfile` dan konfigurasi virtual host web server (`/etc/apache2/sites-available/000-default.conf` serta `/etc/apache2/apache2.conf`).
* **Baris Penentu Document Root:**  
  Di dalam `Dockerfile`, baris instruksi yang mengatur document root adalah:
  ```dockerfile
  ENV APACHE_DOCUMENT_ROOT /var/www/html/public
  RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
  RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
  ```
  *(Jika menggunakan web server Nginx, direktifnya adalah: `root /var/www/kampuslms/public;`).*
* **Arah Folder:**  
  Direktif di atas mengarahkan web server secara ketat ke direktori **`public/`** (`/var/www/html/public`), dan **bukan** ke folder akar repositori (`/var/www/html`).
* **Alasan Teknis & Risiko Keamanan Jika Salah Konfigurasi:**  
  Direktori `public/` berfungsi sebagai batas luar (*perimeter*) aplikasi yang aman untuk diakses publik. Di dalamnya hanya tersedia berkas statis (CSS/JS hasil kompilasi Vite, gambar) dan titik masuk aplikasi `index.php`.  
  Apabila web server diarahkan ke direktori utama proyek, struktur internal akan terekspos langsung ke internet:
  1. `/.env` $\rightarrow$ Kredensial sensitif seperti kata sandi database, API key, dan `APP_KEY` dapat dibaca atau diunduh bebas oleh siapa pun lewat peramban.
  2. `/storage/logs/laravel.log` $\rightarrow$ Jejak error dan query SQL internal server dapat diintip oleh penyerang.
  3. `/composer.json` & `/vendor/` $\rightarrow$ Membocorkan daftar dependensi dan versinya, memudahkan eksploitasi celah keamanan (*known vulnerabilities*).

---

### 2. Analisis Perbandingan Lingkungan (`php artisan about`)

Perintah `php artisan about` dijalankan untuk melihat ringkasan konfigurasi framework Laravel 12 pada komputer lokal dan membandingkannya dengan container server produksi di cloud Railway:

| Komponen / Konfigurasi | Lingkungan Pengembangan Lokal (Laptop) | Server Produksi Cloud (Railway Live) | Penjelasan Perbedaan & Urgensi Keamanan |
| :--- | :--- | :--- | :--- |
| **`Application Environment (APP_ENV)`** | `local` | **`production`** | Mode produksi mengaktifkan lapisan proteksi maksimal dan mematikan fungsi bantu pengembang. |
| **`Debug Mode (APP_DEBUG)`** | `true (ENABLED)` | **`false (OFF)`** | Wajib dinonaktifkan di server publik agar pesan galat fatal (*stack trace*) dan kredensial `.env` tidak bocor ke layar pengguna. |
| **`Application URL (APP_URL)`** | `http://localhost:8000` | `https://kampuslms-kelompok-01-production.up.railway.app` | Menggunakan domain publik HTTPS resmi yang diamankan enkripsi SSL/TLS otomatis dari Let's Encrypt via Railway Proxy. |
| **`PHP Version`** | PHP 8.2 / 8.3 CLI (Windows) | PHP 8.4.27 (Debian Linux Container) | Server cloud berjalan pada PHP 8.4 versi Linux yang dilengkapi ekstensi `pdo_sqlite`, `pcntl`, `gd`, dan `zip`. |
| **`Configuration Cache`** | `NOT CACHED` | **`CACHED`** | Konfigurasi sistem disatukan ke dalam file memori statis agar akses request lebih cepat dan pembacaan `.env` dihentikan. |
| **`Route Cache`** | `NOT CACHED` | **`CACHED`** | Rute URL aplikasi dibekukan menjadi array cepat, mengurangi beban kalkulasi CPU saat memproses routing. |
| **`View Cache`** | `NOT CACHED` | **`CACHED`** | Seluruh template Blade telah dikompilasi sebelumnya (*pre-compiled*) menjadi kode PHP mentah siap saji. |
| **`Queue Connection`** | `sync` | **`database`** (Dikelola Supervisor) | Tugas berat (notifikasi, generate PDF) dikirim ke antrean background sehingga respons halaman web tetap instan. |
| **`Storage Link`** | `NOT LINKED` | **`LINKED`** (`public/storage` $\rightarrow$ `storage/app/public`) | Tautan simbolik aktif agar dokumen tugas dan materi pembelajaran dapat diakses oleh browser mahasiswa. |

---

### 3. Pengawasan Antrean Latar Belakang (`supervisorctl status`)

Saat memeriksa status proses antrean (*queue worker*) pada konsol terminal server Railway, diperoleh informasi status berikut:

```text
root@a14d021bfe1f:/var/www/html# supervisorctl status
kampuslms-worker:kampuslms-worker_00   RUNNING   pid 311, uptime 0:06:21
kampuslms-worker:kampuslms-worker_01   RUNNING   pid 312, uptime 0:06:21
```

* **Jumlah Worker:** Terdapat **2 worker antrean** yang beroperasi bersamaan (`kampuslms-worker_00` dan `kampuslms-worker_01`) sesuai parameter `numprocs=2` pada berkas `supervisord.conf`.
* **Peran Kunci Supervisor:**  
  Jika antrean hanya dijalankan manual via `php artisan queue:work`, proses tersebut akan mati begitu sesi terminal ditutup. Supervisor bertindak sebagai pengawas proses (*process supervisor*) yang menjamin:
  1. Worker otomatis dijalankan saat server melakukan proses booting (`autostart=true`).
  2. Worker otomatis dinyalakan ulang jika sewaktu-waktu mengalami *crash* atau kehabisan memori (`autorestart=true`).
  3. Menjamin pengiriman notifikasi tugas kuliah baru (`SendAssignmentPublishedNotification`) dan pengolahan nilai berjalan lancar tanpa membebani browser pengguna.

---

### 4. Audit Berkas Catatan Kesalahan (`storage/logs/laravel.log`)

Di lingkungan produksi dengan `APP_DEBUG=false`, berkas log menjadi rujukan utama bagi tim developer untuk menginvestigasi anomali sistem tanpa mengeksposnya ke pengguna umum.

* Berkas diperiksa menggunakan perintah: `tail -n 30 storage/logs/laravel.log`.
* **Hasil Analisis:**  
  Log server produksi berada dalam kondisi bersih dan normal. Entri yang tercatat hanya mencakup siklus hidup aplikasi (inisialisasi container, migrasi skema tabel, pemuatan data awal seeder akun demo, serta pengujian skrip otorisasi anti-IDOR `test-authz.sh`):
  ```text
  [2026-11-10 10:45:12] production.INFO: Application booted successfully in production mode.
  [2026-11-10 10:55:01] production.INFO: Testing anti-IDOR authorization script executed successfully for CoursePolicy and TranscriptPolicy.
  ```
* Tidak ditemukan catatan galat kritis (*critical/fatal error*), dan seluruh waktu tercatat akurat dengan format UTC standar ISO 8601.

---

### 5. Verifikasi Keamanan Akses Berkas dengan `curl -I`

Untuk membuktikan secara konkret bahwa berkas sensitif server terlindungi dari akses publik, kami melakukan serangkaian pengujian header HTTP menggunakan `curl.exe -I` langsung ke domain produksi:

* **Uji 1: Memeriksa Akses Berkas Konfigurasi `.env`**
  ```bash
  curl.exe -I https://kampuslms-kelompok-01-production.up.railway.app/.env
  ```
  *Hasil Respons:* `HTTP/1.1 404 Not Found`  
   **Status: AMAN (LOLOS).** Berkas rahasia `.env` tidak dapat diakses maupun diunduh dari jaringan luar.

* **Uji 2: Memeriksa Akses Berkas Log (`storage/logs/laravel.log`)**
  ```bash
  curl.exe -I https://kampuslms-kelompok-01-production.up.railway.app/storage/logs/laravel.log
  ```
  *Hasil Respons:* `HTTP/1.1 404 Not Found`  
   **Status: AMAN (LOLOS).** Catatan aktivitas dan log internal server terlindungi penuh.

* **Uji 3: Memeriksa Akses Berkas Dependensi (`composer.json`)**
  ```bash
  curl.exe -I https://kampuslms-kelompok-01-production.up.railway.app/composer.json
  ```
  *Hasil Respons:* `HTTP/1.1 404 Not Found`  
  **Status: AMAN (LOLOS).** Informasi versi pustaka dependensi tidak dapat dilihat oleh pihak luar.

* **Uji 4: Verifikasi Redirect Otomatis HTTP ke HTTPS**
  ```bash
  curl.exe -I http://kampuslms-kelompok-01-production.up.railway.app
  ```
  *Hasil Respons:* `HTTP/1.1 301 Moved Permanently` $\rightarrow$ dialihkan otomatis ke `https://...`  
   **Status: AMAN (LOLOS).** Seluruh lalu lintas data tanpa enkripsi dipaksa berpindah ke protokol aman HTTPS.

* **Uji 5: Verifikasi Header Keamanan Cookie pada Endpoint Utama**
  ```bash
  curl.exe -I https://kampuslms-kelompok-01-production.up.railway.app
  ```
  *Hasil Respons:* `HTTP/1.1 200 OK` dengan header:  
  `Set-Cookie: kampuslms_session=...; secure; httponly; samesite=lax`  
   **Status: AMAN (LOLOS).** Sesi login terproteksi penuh dari potensi serangan script jahat (XSS) dan pembajakan cookie.

---

### 6. Pengujian Lintas Perangkat (Akses Melalui Smartphone)

Saya menguji performa dan tampilan antarmuka web KampusLMS secara langsung menggunakan smartphone pribadi (**Xiaomi Redmi Note 12**) melalui peramban **Google Chrome Mobile** via jaringan seluler 4G:

* **Responsivitas Tata Letak (Layout Mobile):**  
  Seluruh elemen UI beradaptasi dengan sangat baik. Sistem grid berbasis Tailwind CSS secara fleksibel menyusun daftar kartu mata kuliah, pengumuman tugas, dan grafik progres ke dalam layout satu kolom vertikal (*single column*). Tidak ditemukan teks yang saling bertumpuk maupun geseran layar horizontal yang merusak tampilan (*no horizontal overflow*).
* **Interaktivitas Menu Navigasi:**  
  Sidebar desktop otomatis terlipat menjadi navigasi *mobile drawer* (ikon hamburger) yang responsif dan mudah dijangkau dengan satu tangan.
* **Pengujian Fungsionalitas Utama:**
  1. **Autentikasi & Akun Demo:** Proses masuk menggunakan tombol cepat akun demo mahasiswa dan dosen berjalan lancar dan instan.
  2. **Pengunggahan Dokumen Tugas:** Form pengumpulan tugas mahasiswa terintegrasi baik dengan *native file picker* pada OS Android, memungkinkan unggah file dokumen atau foto tugas secara langsung.
  3. **Halaman Transkrip & Ekspor Dokumen PDF (`/mahasiswa/transcript`):** Ringkasan IPK dan catatan kehadiran tampil proporsional. Tombol "Pratinjau PDF" dan "Unduh PDF" berhasil memicu file PDF transkrip resmi ber-kop ITK secara cepat dan dapat dibaca dengan tata letak font yang tetap presisi pada pembaca PDF ponsel.

---
---

## BREAK — Delapan Percobaan Skenario Kegagalan Server (45 Menit)

Berikut tabel rangkuman 8 eksperimen pengujian skenario kerusakan server yang telah kami lakukan dan pelajari perilakunya di lingkungan produksi:

| # | Skenario yang Diuji | Dugaan/Prediksi Awal | Hasil Sebenarnya & Analisis Teknis |
|---|---------------------|----------------------|-------------------------------------|
| 1 | Mengaktifkan `APP_DEBUG=true`, memicu error, lalu membukanya di jendela *Incognito* | Peramban akan menyajikan halaman diagnostik kesalahan secara lengkap (*Ignition page*), yang secara tidak langsung membocorkan konfigurasi internal server kepada pengguna umum. | **Terbukti menimbulkan celah keamanan kritis (*critical information disclosure*).** Begitu `APP_DEBUG=true` diaktifkan dan rute error dipanggil, jendela penyamaran tanpa login langsung memperlihatkan layar merah Laravel Ignition. Layar tersebut menampilkan data sensitif: username & password database, nilai `APP_KEY`, versi runtime PHP/Laravel, serta baris kode sumber dan struktur direktori server (`/var/www/html/...`). Eksperimen ini menjadi bukti nyata mengapa nilai `APP_DEBUG` wajib mutlak bernilai `false` di server produksi publik. Segera setelah uji coba, pengaturannya dikembalikan ke `false` dan cache konfigurasi dibersihkan. |
| 2 | Mengubah Document Root web server ke folder akar proyek, lalu membuka URL `/.env` | Berkas konfigurasi rahasia `.env` akan langsung terunduh secara polos sebagai teks biasa oleh peramban karena web server tidak meneruskannya ke engine PHP. | **Terjadi kebocoran data kredensial fatal.** Saat direktif virtual host diubah dari `/var/www/html/public` ke folder induk `/var/www/html`, perintah pengujian `curl -I https://domain/.env` yang semula `404 Not Found` berubah menjadi `200 OK`. Saat tautan diakses di browser, berkas `.env` langsung terunduh otomatis ke komputer dan memperlihatkan seluruh kredensial database produksi tanpa sensor. Eksperimen ini menegaskan bahwa pembatasan Document Root ke folder `public/` adalah garda terdepan perlindungan aplikasi web Laravel. Konfigurasi segera diamankan kembali ke `public/`. |
| 3 | Menjalankan `config:cache` sementara masih ada pemanggilan fungsi `env()` di dalam Controller | Pemanggilan fungsi `env()` pada controller akan menghasilkan nilai kosong (`null`) karena berkas fisik `.env` tidak lagi dibaca oleh Laravel setelah proses caching konfigurasi. | **Aplikasi mengalami gangguan logika/tampilan (*bug/crash*) karena nilai konfigurasi berubah menjadi `null`.** Ketika kode uji `env('APP_NAME')` diletakkan di dalam controller setelah eksekusi `php artisan config:cache`, judul web pada halaman terkait mendadak hilang karena fungsi tersebut mengembalikan nilai `null`. Hal ini membuktikan bahwa mekanisme `config:cache` membekukan semua setelan ke `bootstrap/cache/config.php` dan sengaja menghentikan pembacaan berkas `.env` demi efisiensi resource. Eksperimen ini menegaskan aturan baku: fungsi `env()` hanya boleh ditulis di dalam berkas konfigurasi `config/*.php`, sedangkan pada Controller/View wajib menggunakan helper `config('app.name')`. |
| 4 | Mematikan service Supervisor, kemudian membuat tugas kuliah baru | Pembuatan tugas oleh dosen tetap tampak berhasil di layar web, namun antrean pengiriman notifikasi ke mahasiswa terhenti di tabel database tanpa memunculkan pesan error (*silent failure*). | **Terjadi kegagalan tersembunyi (*silent failure*) yang merugikan proses perkuliahan.** Saat antrean dihentikan lewat `supervisorctl stop kampuslms-worker:*` lalu dosen mempublikasikan tugas, web tetap memunculkan alert sukses berwarna hijau. Namun saat akun mahasiswa dibuka, angka lonceng notifikasi tetap 0 dan mahasiswa tidak mengetahui adanya tugas baru. Saat tabel database diperiksa, job notifikasi (`SendAssignmentPublishedNotification`) hanya tertahan dengan status *pending* di tabel `jobs`. Ketika Supervisor diaktifkan kembali (`supervisorctl start`), worker seketika memproses antrean dan notifikasi langsung masuk ke akun mahasiswa. Ini membuktikan vitalnya peran Supervisor dalam menjaga kontinuitas tugas background. |
| 5 | Melakukan deploy kode program baru tanpa mengeksekusi `queue:restart` | Queue worker yang sedang aktif akan tetap mengeksekusi logika kodingan lama yang tersimpan di memori RAM, sehingga perubahan kode terbaru tidak akan berdampak pada antrean. | **Worker terbukti tetap mengeksekusi kode versi lama (*stale code execution*).** Saat format kalimat notifikasi di file Listener diubah lalu kodingan baru ditarik (*git pull*) ke server tanpa menjalankan `php artisan queue:restart`, notifikasi baru yang diproses worker ternyata tetap menggunakan kalimat lama. Hal ini terjadi karena worker antrean Laravel berjalan sebagai proses persisten (*long-running process*) yang memuat kode PHP ke RAM saat inisialisasi awal dan tidak membaca ulang perubahan berkas di disk sebelum direstart. Begitu perintah `php artisan queue:restart` dikirimkan, worker melakukan restart secara *graceful* dan langsung memuat kode versi terbaru dari disk. |
| 6 | Mengubah hak akses direktori `storage` menjadi izin baca-eksekusi saja (`555`) | Web server akan memunculkan status galat `HTTP 500 Internal Server Error`, dan sistem bahkan tidak mampu menuliskan penyebab error ke berkas `laravel.log` karena hak tulis ditiadakan. | **Aplikasi seketika lumpuh total dan browser hanya menyajikan layar putih `HTTP 500 Internal Server Error`.** Saat berkas `storage/logs/laravel.log` dibuka untuk memeriksa masalah, tidak ada penambahan baris log sama sekali karena user web server (`www-data`) tidak memiliki hak akses tulis (`w`). Pesan galat fatal baru ditemukan saat mengecek log web server di `/var/log/apache2/error.log` yang menyatakan kegagalan `Permission denied` saat menulis berkas cache view Blade. Eksperimen ini membuktikan bahwa folder `storage` dan `bootstrap/cache` wajib memiliki izin tulis penuh (`chmod 775` dan kepemilikan `chown www-data:www-data`) agar aplikasi Laravel dapat beroperasi dengan normal. |
| 7 | Mengatur konfigurasi `upload_max_filesize=1M` di PHP, lalu mengunggah berkas tugas 3 MB | Pengunggahan berkas tugas akan ditolak atau dipotong langsung oleh modul interpreter PHP sebelum sampai ke tahap validasi FormRequest Laravel, mengakibatkan variabel berkas bernilai kosong. | **Pengunggahan tugas langsung ditolak dengan pesan galat yang membingungkan: "File tugas wajib diunggah".** Meskipun pada aturan validasi Laravel berkas 3 MB masih diperbolehkan (`max:10240` atau 10 MB), namun saat setelan PHP diubah menjadi `1M`, formulir langsung memantul gagal. Saat dicek di backend, objek `$request->file('file')` dan array `$_FILES` bernilai kosong (`null`) karena berkas tersebut telah dicegat dan dibuang oleh runtime PHP sebelum framework Laravel menerimanya. Eksperimen ini menunjukkan bahwa pengaturan `upload_max_filesize` dan `post_max_size` pada `php.ini` server harus selalu sinkron dengan aturan validasi pada kode aplikasi. |
| 8 | Mengakses website melalui protokol `http://` setelah mengaktifkan `SESSION_SECURE_COOKIE=true` | Peramban web akan memblokir pengiriman cookie sesi melalui jaringan tanpa enkripsi (HTTP), mengakibatkan pengguna tidak akan pernah berhasil login (*infinite redirect loop* atau galat 419 Page Expired). | **Pengguna terjebak dalam siklus login berulang tanpa akhir (*infinite login loop*) dan memicu pesan galat `419 Page Expired (CSRF Token Mismatch)`.** Saat aplikasi diakses via `http://` (non-HTTPS) lalu kredensial dimasukkan dengan benar, halaman selalu kembali ke formulir login kosong. Berdasarkan inspeksi tab Network dan Application pada DevTools peramban, browser menolak menyertakan cookie `kampuslms_session` karena cookie tersebut diberi tanda proteksi `Secure`. Akibatnya, server memperlakukan setiap request sebagai pengguna tamu baru yang belum terautentikasi. Eksperimen ini membuktikan keampuhan proteksi secure cookie dalam mencegah pencurian sesi (*session hijacking/sniffing*), sekaligus menekankan perlunya aturan *auto-redirect* HTTP ke HTTPS di tingkat web server. |
