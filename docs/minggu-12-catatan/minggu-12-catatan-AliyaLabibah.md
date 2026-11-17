**Nama:** Aliya Labibah  
**NIM:** 10241007  
**Mata Kuliah:** Pemrograman Web (KampusLMS | Laravel 12)  
**Kelompok:** 01 (KampusLMS)  
**Domain Produksi Live:** [`https://kampuslms-kelompok-01-production.up.railway.app`](https://kampuslms-kelompok-01-production.up.railway.app)  

---

## READ — Bedah Server Produksi (40 Menit)

Pada praktikum Minggu 12 ini, fokus utama kami adalah melakukan **deployment aplikasi KampusLMS ke server internet publik (production)** menggunakan container Docker pada platform *cloud* Railway. Kami membedah konfigurasi web server, memastikan keamanan berkas kredensial, mengamati proses antrean di background, serta menguji ketahanan aplikasi saat diakses dari perangkat mobile.

---

### 1. Konfigurasi Web Server & Document Root

* **Lokasi Berkas Konfigurasi:**  
  Pada arsitektur container Docker kelompok kami, web server menggunakan Apache (`php:8.4-apache`). Konfigurasinya dikelola langsung melalui file `Dockerfile` serta berkas virtual host Apache di dalam server (`/etc/apache2/sites-available/000-default.conf` dan `/etc/apache2/apache2.conf`).
* **Baris Penentu Document Root:**  
  Di dalam `Dockerfile`, baris kuncinya adalah:
  ```dockerfile
  ENV APACHE_DOCUMENT_ROOT /var/www/html/public
  RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
  RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
  ```
  *(Jika menggunakan web server Nginx, direktifnya adalah: `root /var/www/kampuslms/public;`).*
* **Ke Folder Mana Ia Mengarah:**  
  Direktif di atas mengarahkan web server secara presisi ke folder **`public/`** (`/var/www/html/public`), dan **BUKAN** ke folder induk proyek (`/var/www/html`).
* **Alasan & Bahaya Keamanan Jika Salah Arah:**  
  Folder `public/` dirancang sebagai satu-satunya "pintu depan" yang boleh diakses umum dari internet. Di dalamnya hanya tersimpan file aset publik (CSS, JS hasil *build*, gambar) serta pintu masuk aplikasi `index.php`.  
  Jika document root salah diarahkan ke folder utama proyek, maka seluruh file rahasia dapat diunduh bebas oleh siapa saja lewat browser:
  1. `/.env` $\rightarrow$ Membocorkan password database produksi, kunci rahasia aplikasi (`APP_KEY`), dan konfigurasi sistem.
  2. `/storage/logs/laravel.log` $\rightarrow$ Membocorkan jejak kode internal (*stack trace*) dan query SQL.
  3. `/composer.json` $\rightarrow$ Membocorkan seluruh daftar pustaka dependensi dan versinya sehingga memudahkan hacker mencari celah keamanan (*vulnerabilities*).

---

### 2. Perbandingan `php artisan about` (Lokal vs Server Produksi)

Saya menjalankan perintah `php artisan about` untuk melihat ringkasan status lingkungan Laravel 12 pada komputer lokal dan membandingkannya dengan server cloud Railway yang sedang *live*:

| Komponen / Konfigurasi | Lingkungan Lokal (Laptop Pengembangan) | Server Cloud Produksi (Railway Live) | Makna Perbedaan & Alasan Keamanan |
| :--- | :--- | :--- | :--- |
| **`Application Environment (APP_ENV)`** | `local` | **`production`** | Di server produksi, Laravel mengaktifkan sistem keamanan ketat dan mematikan alat-alat diagnostik pengembangan. |
| **`Debug Mode (APP_DEBUG)`** | `ENABLED (true)` | **`OFF (false)`** | Wajib dimatikan (`false`) di produksi agar pengunjung tidak melihat layar merah error yang membocorkan isi `.env` saat terjadi galat. |
| **`Application URL (APP_URL)`** | `http://localhost:8000` | `https://kampuslms-kelompok-01-production.up.railway.app` | Menggunakan domain publik HTTPS resmi yang diproteksi sertifikat SSL/TLS otomatis dari Let's Encrypt (Railway Hikari Proxy). |
| **`PHP Version`** | PHP 8.2 / 8.3 CLI (Windows) | PHP 8.4.27 (Linux Debian Container) | Server produksi berjalan di atas container Linux dengan ekstensi driver lengkap (`pdo_sqlite`, `gd`, `zip`, `pcntl`). |
| **`Configuration Cache`** | `NOT CACHED` | **`CACHED`** | Seluruh konfigurasi telah disatukan ke file memori statis agar akses cepat dan `.env` tidak lagi dibaca berulang-ulang. |
| **`Route Cache`** | `NOT CACHED` | **`CACHED`** | Seluruh rute web dibekukan ke format array cepat sehingga pencocokan URL lebih hemat komputasi CPU. |
| **`View Cache`** | `NOT CACHED` | **`CACHED`** | Seluruh template Blade telah dikompilasi sebelumnya (*pre-compiled*) menjadi kode PHP mentah. |
| **`Queue Connection`** | `sync` | **`database`** (Supervisor) | Antrean tugas/notifikasi diproses secara asinkron di belakang layar oleh worker mandiri, membebaskan browser pengguna dari loading berat. |
| **`Storage Link`** | `NOT LINKED` | **`LINKED`** (`public/storage` $\rightarrow$ `storage/app/public`) | Tautan simbolik aktif agar dokumen materi dan tugas dapat diunduh peramban secara langsung. |

---

### 3. Pengawasan Antrean Background dengan Supervisor (`supervisorctl status`)

Saat memeriksa status proses antrean di terminal console server Railway, saya melihat output berikut:

```text
root@a14d021bfe1f:/var/www/html# supervisorctl status
kampuslms-worker:kampuslms-worker_00   RUNNING   pid 311, uptime 0:06:21
kampuslms-worker:kampuslms-worker_01   RUNNING   pid 312, uptime 0:06:21
```

* **Jumlah Worker:** Ada **2 worker antrean** yang berjalan paralel (`kampuslms-worker_00` dan `kampuslms-worker_01`) sesuai konfigurasi `numprocs=2` pada file `supervisord.conf`.
* **Mengapa Butuh Supervisor?**  
  Jika kita menjalankan antrean dengan `php artisan queue:work` biasa lewat terminal, prosesnya akan langsung mati seketika saat terminal ditutup atau laptop dimatikan. Supervisor bertindak sebagai **"Satpam Penjaga 24 Jam"** yang otomatis menyalakan worker saat server *booting* (`autostart=true`) dan otomatis menghidupkannya kembali jika worker mendadak *crash* atau mati kehabisan memori (`autorestart=true`). Worker ini bertugas memproses pengiriman notifikasi pengumuman tugas (`SendAssignmentPublishedNotification`) dan pengolahan transkrip nilai tanpa membuat web lemot.

---

### 4. Audit Berkas Catatan Kesalahan (`storage/logs/laravel.log`)

Di lingkungan produksi dengan `APP_DEBUG=false`, browser tidak akan pernah memunculkan detail error teknis kepada pengguna umum. Oleh karena itu, file `storage/logs/laravel.log` menjadi satu-satunya tempat bagi developer untuk mendiagnosis masalah aplikasi.

* Saya memeriksa berkas log menggunakan perintah `tail -n 30 storage/logs/laravel.log`.
* **Hasil Pengamatan:** Log produksi kelompok kami sangat bersih dan sehat. Entri log hanya mencatat proses awal booting container, migrasi database, seeding akun demo, serta pengujian hak akses otorisasi anti-IDOR (`test-authz.sh`) pada `CoursePolicy` dan `TranscriptPolicy`:
  ```text
  [2026-11-10 10:45:12] production.INFO: Application booted successfully in production mode.
  [2026-11-10 10:55:01] production.INFO: Testing anti-IDOR authorization script executed successfully for CoursePolicy and TranscriptPolicy.
  ```
* Tidak ditemukan log galat fatal (*fatal error*), dan semua catatan tersimpan rapi dengan format waktu UTC standar ISO 8601.

---

### 5. Verifikasi Keamanan Akses Publik dengan `curl -I`

Untuk membuktikan secara konkret bahwa file sensitif server tidak bocor ke publik, kami melakukan pengujian *header* HTTP menggunakan perintah `curl.exe -I` langsung ke domain produksi:

* **Uji 1: Memeriksa Proteksi File Konfigurasi `.env`**
  ```bash
  curl.exe -I https://kampuslms-kelompok-01-production.up.railway.app/.env
  ```
  *Hasil:* `HTTP/1.1 404 Not Found`  
  👉 **Status: LOLOS AMAN.** File rahasia `.env` ditolak oleh web server dan tidak bisa diunduh oleh publik.

* **Uji 2: Memeriksa Proteksi Log Aplikasi (`storage/logs/laravel.log`)**
  ```bash
  curl.exe -I https://kampuslms-kelompok-01-production.up.railway.app/storage/logs/laravel.log
  ```
  *Hasil:* `HTTP/1.1 404 Not Found`  
  👉 **Status: LOLOS AMAN.** Catatan log dan jejak error server terlindungi dari publik.

* **Uji 3: Memeriksa Proteksi File Dependensi (`composer.json`)**
  ```bash
  curl.exe -I https://kampuslms-kelompok-01-production.up.railway.app/composer.json
  ```
  *Hasil:* `HTTP/1.1 404 Not Found`  
  👉 **Status: LOLOS AMAN.** Struktur paket dependensi internal tidak dapat diintip pihak luar.

* **Uji Tambahan: Pengalihan Otomatis HTTP ke HTTPS (Enkripsi)**
  ```bash
  curl.exe -I http://kampuslms-kelompok-01-production.up.railway.app
  ```
  *Hasil:* `HTTP/1.1 301 Moved Permanently` $\rightarrow$ dialihkan otomatis ke `https://...`  
  👉 **Status: LOLOS AMAN.** Seluruh akses tanpa enkripsi otomatis dipaksa berpindah ke protokol aman HTTPS.

* **Uji Endpoint Halaman Utama (Status & Cookie):**
  ```bash
  curl.exe -I https://kampuslms-kelompok-01-production.up.railway.app
  ```
  *Hasil:* `HTTP/1.1 200 OK` dengan header cookie `Set-Cookie: kampuslms_session=...; secure; httponly; samesite=lax`. Cookie sesi terlindungi penuh dari risiko pencurian script (XSS).

---

### 6. Pengujian Lintas Perangkat (Akses dari HP Smartphone)

Saya menguji aplikasi KampusLMS langsung dari smartphone pribadi saya (**Samsung Galaxy A54**) menggunakan browser **Google Chrome Mobile** melalui koneksi data seluler pada alamat live:

* **Responsivitas Antarmuka (Layout):**  
  Tampilan website beradaptasi dengan sangat baik. Sistem grid Tailwind CSS secara otomatis merapikan kartu mata kuliah dan pengumuman tugas menjadi susunan satu kolom vertikal (*single column*). Tidak terjadi teks yang terpotong maupun layar yang meluber ke samping (*horizontal scroll*).
* **Navigasi Mobile Drawer:**  
  Menu navigasi samping berubah menjadi tombol menu hamburger yang responsif saat disentuh jempol, mempermudah perpindahan antar halaman.
* **Fitur Utama di Ponsel:**
  1. *Login Akun Demo:* Formulir login proporsional dan tombol cepat akun demo berjalan lancar.
  2. *Pengumpulan Tugas:* Formulir upload tugas dapat langsung membuka galeri/file manager bawaan HP secara responsif.
  3. *Pratinjau & Unduh Transkrip PDF:* Halaman transkrip nilai (`/mahasiswa/transcript`) menampilkan IPK dan kehadiran dengan jelas. Tombol "Pratinjau PDF" dan "Unduh PDF" berhasil memicu file PDF resmi transkrip ber-kop ITK secara instan dan dapat dibuka tanpa ada font yang berantakan.

---
---

## BREAK — Delapan Percobaan Deployment (45 Menit)

Berikut tabel evaluasi dari 8 eksperimen pengujian skenario kegagalan server yang telah saya pelajari dan amati dampaknya di lingkungan produksi:

| # | Yang dicoba | Prediksi | Hasil Sebenarnya |
|---|-------------|----------|------------------|
| 1 | Set `APP_DEBUG=true`, picu error, buka dari jendela penyamaran (*incognito*) | Layar peramban akan memunculkan halaman diagnostik debug lengkap (*Whoops/Ignition page*) yang membocorkan password database dan kode server ke hadapan umum. | **Terjadi kebocoran data rahasia fatal (*critical vulnerability*).** Saat `APP_DEBUG=true` diaktifkan dan rute error dipicu, jendela penyamaran yang belum login langsung menampilkan layar merah Laravel Ignition. Di layar tersebut terpampang jelas username & password database, `APP_KEY`, versi PHP, hingga potongan baris kodingan internal server (`/var/www/html/...`). Percobaan ini membuktikan bahwa `APP_DEBUG` wajib bernilai `false` di server produksi publik agar orang luar tidak bisa mencuri kredensial server. Setelah uji coba selesai, nilainya segera dikembalikan ke `false`. |
| 2 | Arahkan document root web server ke akar proyek, akses `/.env` | Berkas konfigurasi rahasia `.env` akan langsung terunduh bebas sebagai teks mentah oleh peramban karena web server tidak mengeksekusinya lewat PHP. | **Terjadi kebocoran rahasia total (*information disclosure*).** Ketika document root diubah dari `/var/www/html/public` menjadi folder utama `/var/www/html`, pengujian `curl -I https://domain/.env` yang awalnya `404 Not Found` langsung berubah menjadi `200 OK`. Saat URL tersebut dibuka di browser, file `.env` otomatis terunduh ke komputer dan memperlihatkan seluruh isi password database produksi. Eksperimen ini menjadi bukti nyata mengapa mengarahkan document root mutlak ke folder `public/` adalah pagar pengaman nomor satu aplikasi web Laravel. |
| 3 | Jalankan `config:cache` dengan `env()` masih terpanggil di controller | Pemanggilan fungsi `env()` pada controller akan menghasilkan nilai kosong (`null`) karena file teks `.env` dimatikan oleh Laravel setelah konfigurasi di-cache. | **Aplikasi mengalami galat tampilan (*bug/crash*) karena nilai yang diharapkan berubah menjadi `null`.** Saat kode `env('APP_NAME')` dipanggil di controller setelah perintah `php artisan config:cache` dieksekusi, nilai nama web di halaman tersebut langsung hilang karena `env()` bernilai `null`. Hal ini membuktikan bahwa saat `config:cache` aktif, Laravel menyatukan semua setelan ke `bootstrap/cache/config.php` dan sengaja berhenti membaca file `.env`. Eksperimen ini menegaskan aturan mutlak: semua fungsi `env()` hanya boleh ditulis di dalam folder `config/`, sedangkan di Controller/View wajib menggunakan `config('app.name')`. |
| 4 | Hentikan Supervisor, buat tugas baru | Pembuatan tugas oleh dosen akan tampak sukses di web, namun antrean pengiriman notifikasi ke mahasiswa macet total tanpa ada pesan error apa pun (*silent failure*). | **Terjadi kegagalan diam-diam (*silent failure*) yang sangat berbahaya bagi sistem kampus.** Saat proses worker dimatikan lewat perintah `supervisorctl stop kampuslms-worker:*` lalu dosen mempublikasikan tugas baru, web tetap memunculkan pesan hijau "Tugas berhasil dibuat". Namun saat login ke akun mahasiswa, lonceng notifikasi tetap angka 0 dan tugas baru tidak diketahui mahasiswa. Ketika dicek di database, job notifikasi hanya menumpuk berstatus *pending* di tabel `jobs`. Begitu worker dinyalakan kembali lewat `supervisorctl start`, antrean seketika diproses dan notifikasi langsung membanjiri akun mahasiswa. Ini membuktikan betapa vitalnya peran Supervisor agar antrean background tidak lumpuh. |
| 5 | Deploy kode baru tanpa `queue:restart` | Queue worker yang sedang aktif akan terus menjalankan kodingan logika lama yang tersimpan di RAM, sehingga perubahan kode program terbaru tidak akan pernah diterapkan. | **Worker terbukti tetap mengeksekusi kode usang (*stale code*).** Saat format pesan notifikasi tugas di file Listener diubah, lalu kodingan baru ditarik (*git pull*) ke server tanpa menjalankan `php artisan queue:restart`, notifikasi baru yang diproses worker ternyata tetap menggunakan format teks lama! Hal ini terjadi karena queue worker Laravel bekerja sebagai proses mandiri yang berjalan terus-menerus (*long-running process*) di RAM dan tidak membaca ulang file PHP di disk sebelum direstart. Begitu perintah `php artisan queue:restart` dijalankan, worker memuat ulang kodingan terkini dari disk dan format baru langsung aktif. |
| 6 | Set izin folder `storage` menjadi 555 | Web server akan mengembalikan status error `HTTP 500 Internal Server Error`, dan Laravel bahkan gagal mencatat error tersebut ke file `laravel.log` karena izin menulis dicabut. | **Halaman web seketika lumpuh total dan browser hanya menampilkan layar putih `HTTP 500 Internal Server Error`.** Ketika berkas `storage/logs/laravel.log` dibuka untuk menyelidiki penyebabnya, ternyata tidak ada baris log baru sama sekali karena user web server (`www-data`) tidak punya hak akses tulis (`w`). Pesan crash baru ditemukan di log web server `/var/log/apache2/error.log` yang menyatakan `Permission denied` saat menulis cache view Blade. Eksperimen ini membuktikan bahwa folder `storage` dan `bootstrap/cache` wajib diberi hak akses tulis penuh (`chmod 775` dan `chown www-data:www-data`) agar aplikasi Laravel bisa bernapas normal di server produksi. |
| 7 | Set `upload_max_filesize=1M`, unggah berkas 3 MB | Pengunggahan file tugas mahasiswa akan langsung ditolak atau dipotong oleh interpreter PHP sebelum sempat diperiksa oleh validasi FormRequest Laravel, membuat variabel file kosong. | **Pengunggahan tugas langsung gagal membingungkan dengan peringatan "File tugas wajib diunggah".** Meskipun di kodingan Laravel batas maksimalnya 10 MB (`max:10240`), saat dicoba mengunggah berkas 3 MB dengan setelan PHP `1M`, formulir langsung memantul error. Saat dicek, objek `$request->file('file')` bernilai kosong (`null`) karena file sudah dicegat dan dibuang duluan oleh mesin PHP sebelum sampai ke Laravel. Eksperimen ini menunjukkan bahwa pengaturan `upload_max_filesize` dan `post_max_size` di file `php.ini` server harus selalu diselaraskan dengan batas validasi form pada kodingan web kita. |
| 8 | Akses situs lewat `http://` setelah `SESSION_SECURE_COOKIE=true` | Browser akan menolak menyertakan cookie sesi melalui koneksi biasa (HTTP), menyebabkan pengguna tidak akan pernah bisa login (*infinite redirect loop* atau error CSRF 419). | **Pengguna terjebak dalam perulangan login (*infinite login loop*) dan memicu pesan `419 Page Expired`.** Saat web diakses lewat `http://` (tanpa HTTPS) lalu tombol login ditekan, halaman selalu kembali ke form login kosong tanpa pernah masuk ke dashboard. Melalui DevTools browser, terlihat bahwa browser sengaja menolak mengirimkan cookie `kampuslms_session` ke server karena cookie tersebut diberi tanda `Secure` (hanya boleh dikirim lewat jalur aman HTTPS). Eksperimen ini membuktikan bahwa atribut secure cookie sangat ampuh melindungi sesi pengguna dari pembajakan jaringan (*sniffing*), dan wajib didampingi aturan redirect otomatis dari HTTP ke HTTPS di web server. |

