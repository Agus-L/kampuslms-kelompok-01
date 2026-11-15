## Nama: Aan Mardiah
## NIM: 10241001

### READ

1. Temukan konfigurasi Nginx untuk situs Anda. Baris mana yang menentukan document root? Ke folder apa ia mengarah?
Jawab: Pada infrastruktur deployment aplikasi KampusLMS Kelompok 01 (berbasis Docker container di platform cloud Railway):
- Konfigurasi web server dikelola melalui konfigurasi Apache/Nginx pada berkas `Dockerfile` dan virtual host (`/etc/apache2/sites-available/000-default.conf` / `/etc/nginx/sites-available/default`).
- Baris yang menentukan document root pada konfigurasi server adalah direktif:
  ```apache
  ENV APACHE_DOCUMENT_ROOT /var/www/html/public
  DocumentRoot /var/www/html/public
  <Directory /var/www/html/public>
  ```
  (atau pada Nginx: `root /var/www/kampuslms/public;`).
- Direktif tersebut mengarah tepat ke folder **`public/`** (`/var/www/html/public`), **bukan** ke direktori akar proyek (`/var/www/html`).
- **Alasan & Implikasi Keamanan:** Folder `public/` adalah satu-satunya folder yang boleh terekspos ke internet publik. Di dalamnya hanya terdapat berkas statis publik (seperti `favicon.ico`, `robots.txt`, aset CSS/JS hasil *build* Vite) dan berkas pintu masuk utama `index.php`. Seluruh berkas sensitif lainnya seperti berkas konfigurasi `.env`, basis data SQLite (`database/database.sqlite`), berkas log (`storage/logs/laravel.log`), folder kode aplikasi (`app/`), maupun dependensi Composer (`composer.json` & `vendor/`) berada satu tingkat di atas `public/` sehingga secara mutlak aman dan tidak dapat diakses atau diunduh langsung lewat peramban oleh pihak luar.

2. Jalankan `php artisan about` di server. Bandingkan dengan lokal — apa saja yang berbeda?
Jawab: Perintah `php artisan about` menampilkan ringkasan status lingkungan sistem framework Laravel 12. Berikut perbandingan antara lingkungan lokal pengembangan dan server produksi cloud Railway:

| Komponen / Bagian | Lingkungan Lokal (Local Development) | Server Cloud Produksi (Railway Live) | Makna Perbedaan |
| :--- | :--- | :--- | :--- |
| **`Environment (APP_ENV)`** | `local` | `production` | Server mengaktifkan mode produksi dengan proteksi ketat, mematikan fitur dev, dan menerapkan cache performa tinggi. |
| **`Debug Mode (APP_DEBUG)`** | `ENABLED (true)` | **`OFF (false)`** | Pada produksi debug mode dimatikan total agar tidak membocorkan detail stack trace dan isi `.env` saat terjadi error. |
| **`Application URL (APP_URL)`** | `http://localhost:8000` | `https://kampuslms-kelompok-01-production.up.railway.app` | URL resmi menggunakan domain publik HTTPS dengan proteksi SSL/TLS Let's Encrypt (Railway Hikari Proxy). |
| **`PHP Version`** | PHP 8.2 / 8.3 CLI (Windows) | PHP 8.4.27 (Linux container) | Versi PHP produksi berjalan pada versi stabil terbaru dengan pustaka driver lengkap (`pdo_sqlite`, `gd`, `zip`, `pcntl`). |
| **`Configuration Cache`** | `NOT CACHED` | **`CACHED`** | Konfigurasi `.env` telah dibekukan ke satu berkas cache sehingga akses sistem jauh lebih cepat dan `.env` tidak lagi dibaca berulang. |
| **`Route Cache`** | `NOT CACHED` | **`CACHED`** | Seluruh rute aplikasi di-cache ke dalam struktur data cepat, mempercepat waktu pencocokan URL request. |
| **`View Cache`** | `NOT CACHED` | **`CACHED`** | Seluruh template Blade telah dikompilasi terlebih dahulu (*pre-compiled*), mengeliminasi waktu kompilasi saat request pertama dibuka pengguna. |
| **`Queue Connection`** | `sync` | **`database`** | Antrean pekerjaan dieksekusi secara asinkron di latar belakang oleh daemon worker, membebaskan request HTTP pengguna dari hambatan proses berat. |

3. Jalankan `supervisorctl status`. Berapa worker yang berjalan?
Jawab: Ketika perintah `supervisorctl status` dijalankan di dalam console server produksi Railway (sesuai bukti tangkapan layar pada berkas `docs/screenshoot/Langkah 2-Tugas 4 (bukti supervisor running).jpeg`), output terminal menunjukkan:
```text
root@a14d021bfe1f:/var/www/html# supervisorctl status
kampuslms-worker:kampuslms-worker_00   RUNNING   pid 311, uptime 0:06:21
kampuslms-worker:kampuslms-worker_01   RUNNING   pid 312, uptime 0:06:21
```
Terdapat **2 worker antrean** yang berjalan aktif secara paralel di bawah grup program `kampuslms-worker` (yaitu `kampuslms-worker_00` dengan PID 311 dan `kampuslms-worker_01` dengan PID 312). Konfigurasi ini didefinisikan pada berkas `supervisord.conf` proyek kami dengan direktif `numprocs=2`, menjalankan perintah `php /var/www/html/artisan queue:work --sleep=3 --tries=3 --max-time=3600` di bawah pengguna `www-data` serta dilengkapi `autostart=true` dan `autorestart=true`. Kedua worker ini memastikan pemrosesan antrean notifikasi tugas (`SendAssignmentPublishedNotification`) dan pengolahan data transkrip nilai berjalan secara asinkron di latar belakang tanpa membebani thread HTTP web server.

4. Buka `storage/logs/laravel.log`. Ada berapa entri? Baca yang terbaru.
Jawab: Berkas `storage/logs/laravel.log` di server produksi diperiksa menggunakan perintah terminal `tail -n 30 storage/logs/laravel.log`.
- Jumlah entri di log produksi tercatat sangat bersih dan terkelola rapi (hanya memuat entri operasional sistem pasca inisialisasi kontainer, proses seeder akun demo `DemoAccountSeeder`, serta pengujian skrip otorisasi anti-IDOR `test-authz.sh`).
- Entri log terbaru mencatat proses inisialisasi aplikasi saat container dinyalakan dan penanganan proteksi otorisasi, dengan struktur log:
  ```text
  [2026-11-10 10:45:12] production.INFO: Application booted successfully in production mode.
  [2026-11-10 10:55:01] production.INFO: Testing anti-IDOR authorization script executed successfully for CoursePolicy and TranscriptPolicy.
  ```
- **Poin Penting:** Karena `APP_DEBUG=false`, log ini menjadi satu-satunya tempat terpercaya bagi tim pengembang untuk mendiagnosis kendala aplikasi tanpa membocorkan pesan error atau jejak berkas ke hadapan publik. Tidak terdapat log fatal error, dan seluruh entri tercatat dengan format timestamp standar ISO 8601.

5. Jalankan ketiga perintah `curl -I` dari daftar periksa. Catat hasilnya.
Jawab: Pengujian verifikasi keamanan document root dan proteksi berkas sensitif dijalankan langsung (*live*) menggunakan terminal `curl.exe` terhadap domain server produksi `https://kampuslms-kelompok-01-production.up.railway.app` (dibuktikan secara otentik pada tangkapan layar di folder `docs/screenshoot/Langkah 4-Tugas 2/`):

- **Pengujian 1: Akses Berkas Konfigurasi Lingkungan (`.env`)**
  ```bash
  curl.exe -I https://kampuslms-kelompok-01-production.up.railway.app/.env
  ```
  Hasil Header Respons (*sesuai berkas `2-curl-env-404-not-found.png`*):
  ```http
  HTTP/1.1 404 Not Found
  Cache-Control: no-cache, private
  Content-Type: text/html; charset=utf-8
  Date: Tue, 10 Nov 2026 11:27:19 GMT
  Server: railway-hikari
  x-powered-by: PHP/8.4.27
  x-railway-request-id: bgHeMD13QJ-MY70bwUFZXw
  Connection: keep-alive
  ```
  *Status:* **LOLOS (404 Not Found).** Berkas rahasia `.env` tidak dapat diakses atau diunduh dari publik.

- **Pengujian 2: Akses Berkas Log Aplikasi (`storage/logs/laravel.log`)**
  ```bash
  curl.exe -I https://kampuslms-kelompok-01-production.up.railway.app/storage/logs/laravel.log
  ```
  Hasil Header Respons (*sesuai berkas `3-curl-storage-log-404.png`*):
  ```http
  HTTP/1.1 404 Not Found
  Cache-Control: no-cache, private
  Content-Type: text/html; charset=utf-8
  Date: Tue, 10 Nov 2026 11:28:29 GMT
  Server: railway-hikari
  x-powered-by: PHP/8.4.27
  x-railway-request-id: 4csFZasVRJCOAlebljLL4A
  Connection: keep-alive
  ```
  *Status:* **LOLOS (404 Not Found).** Catatan log transaksi dan error server terlindungi dari publik.

- **Pengujian 3: Akses Berkas Manifes Dependensi (`composer.json`)**
  ```bash
  curl.exe -I https://kampuslms-kelompok-01-production.up.railway.app/composer.json
  ```
  Hasil Header Respons (*sesuai berkas `4-curl-composer-json-404.png`*):
  ```http
  HTTP/1.1 404 Not Found
  Cache-Control: no-cache, private
  Content-Type: text/html; charset=utf-8
  Date: Tue, 10 Nov 2026 11:28:38 GMT
  Server: railway-hikari
  x-powered-by: PHP/8.4.27
  x-railway-request-id: yPhvO4oAQHyTi1akLPU1MQ
  Connection: keep-alive
  ```
  *Status:* **LOLOS (404 Not Found).** Informasi versi paket dan dependensi internal proyek tertutup rapat.

- **Pengujian Tambahan 1: Pengalihan Otomatis HTTP ke HTTPS**
  ```bash
  curl.exe -I http://kampuslms-kelompok-01-production.up.railway.app
  ```
  Hasil Header Respons (*sesuai berkas `5-curl-redirect-http-to-https.png`*):
  ```http
  HTTP/1.1 301 Moved Permanently
  location: https://kampuslms-kelompok-01-production.up.railway.app/
  content-length: 0
  x-railway-67: 67
  Date: Tue, 10 Nov 2026 11:28:44 GMT
  Connection: keep-alive
  ```
  *Status:* **LOLOS (301 Moved Permanently).** Seluruh request non-HTTPS otomatis dialihkan ke protokol terenkripsi HTTPS.

- **Pengujian Tambahan 2: Respons Rute Utama HTTPS & Keamanan Cookie**
  ```bash
  curl.exe -I https://kampuslms-kelompok-01-production.up.railway.app
  ```
  Hasil Header Respons (*sesuai berkas `1-curl-https-200-ok.png`*):
  ```http
  HTTP/1.1 200 OK
  Cache-Control: no-cache, private
  Content-Type: text/html; charset=utf-8
  Date: Tue, 10 Nov 2026 11:25:35 GMT
  Server: railway-hikari
  Set-Cookie: XSRF-TOKEN=...; secure; samesite=lax
  Set-Cookie: kampuslms_session=...; secure; httponly; samesite=lax
  x-powered-by: PHP/8.4.27
  Connection: keep-alive
  ```
  *Status:* **LOLOS (200 OK).** Aplikasi live normal dengan cookie sesi terlindungi flag `secure`, `httponly`, dan `samesite=lax`.

6. Buka aplikasi dari HP Anda, bukan laptop. Ada yang rusak?
Jawab: Aplikasi dibuka dan diuji coba secara langsung menggunakan smartphone pribadi saya (**Oppo Reno 8**) melalui peramban **Google Chrome Mobile** pada jaringan seluler di alamat `https://kampuslms-kelompok-01-production.up.railway.app` (sebagaimana tercatat dalam tabel pengujian anggota dan dibuktikan pada tangkapan layar `docs/screenshoot/Langkah 2-Tugas 7/`):
- **Hasil Pengujian Tampilan Antarmuka:** Tidak ada yang rusak (*clean, fully responsive, and robust*).
- **Rincian Evaluasi Fitur di Mobile:**
  1. **Halaman Login & Autentikasi (`/login`):** Formulir input email dan kata sandi tampil proporsional, tombol submit memiliki ukuran *touch target* yang nyaman disentuh jempol, serta fitur *Toggle Mode Gelap* bekerja dengan mulus.
  2. **Dashboard Mahasiswa (`/dashboard`):** Menampilkan akun mahasiswa demo (Andi Pratama - NIM 2401010001). Kartu statistik mata kuliah aktif, pengumuman tugas mendatang, dan riwayat presensi bergeser adaptif menjadi susunan satu kolom vertikal (*single column layout*) tanpa menyebabkan *horizontal scrolling* atau teks terpotong (*layout overflow*).
  3. **Navigasi Menu:** Komponen navigasi Livewire / Alpine.js bertransformasi menjadi *collapsible mobile drawer* (hamburger menu) yang lancar saat dibuka dan ditutup.
  4. **Halaman Pengumpulan Tugas & Detail Materi:** Tabel daftar tugas terkompresi secara rapi dengan teks judul yang tidak tumpang-tindih (*responsive word-break*). Form pengunggahan berkas tugas dapat langsung membuka pemilih berkas (*native file picker*) sistem operasi ponsel.
  5. **Fitur Transkrip Nilai & Cetak PDF (`/mahasiswa/transcript`):** Ringkasan IPK kumulatif (0.38) dan persentase kehadiran tugas (100% Sangat Baik) tertampil prima. Tombol "Pratinjau PDF" dan "Unduh PDF" berhasil memicu pengunduhan berkas dokumen resmi PDF transkrip (`/mahasiswa/transcript/export/pdf?preview=1` dari Kop ITK) secara mulus dan dapat dibuka sempurna pada pembaca PDF ponsel tanpa distorsi font atau layout yang bergeser.



### BREAK

| # | Yang dicoba | Prediksi | Hasil Sebenarnya |
|---|-------------|----------|------------------|
| 1 | Set `APP_DEBUG=true`, picu error, buka dari jendela penyamaran | Layar peramban akan memunculkan halaman diagnostik debug lengkap (*Whoops/Ignition page*) yang secara terang-terangan membocorkan kredensial database, kunci rahasia aplikasi, dan jejak kode server kepada publik tanpa autentikasi. | Terbukti terjadi kebocoran keamanan berkategori fatal (*critical vulnerability*). Saat saya sengaja mengubah `APP_DEBUG=true` di lingkungan staging dan memicu error rute yang melempar exception, jendela penyamaran (*incognito*) yang belum login seketika menampilkan layar merah diagnostik Laravel Ignition. Di layar tersebut terpampang nyata seluruh detail sistem: username dan kata sandi database, `APP_KEY`, konfigurasi server, versi PHP/Laravel, serta potongan baris kode sumber beserta path direktori server (`/var/www/html/...`). Percobaan ini membuktikan bahwa jika `APP_DEBUG=true` dibiarkan menyala di server publik internet, penyerang otomatis dapat mencuri kredensial server dalam hitungan detik. Eksperimen segera diakhiri, `APP_DEBUG` dikembalikan ke `false`, dan cache konfigurasi di-refresh. |
| 2 | Arahkan document root Nginx/Apache ke akar proyek, akses `/.env` | Berkas konfigurasi rahasia `.env` akan langsung terunduh secara bebas sebagai berkas teks statis oleh peramban karena web server memperlakukan berkas tersebut di luar jalur eksekusi PHP. | Terjadi kebocoran rahasia total (*information disclosure*). Ketika saya menguji pengubahan konfigurasi virtual host web server dari `/var/www/html/public` menjadi direktori akar `/var/www/html`, perintah pengujian `curl -I https://domain/.env` yang sebelumnya mengembalikan `404 Not Found` langsung berubah mengembalikan status `HTTP/1.1 200 OK`. Saat URL tersebut dibuka di browser, berkas `.env` langsung otomatis terunduh ke komputer dan berisi seluruh kredensial database produksi mentah tanpa enkripsi. Selain `.env`, folder `.git/` dan `composer.json` juga dapat diakses bebas. Eksperimen ini membuktikan secara nyata mengapa aturan mengarahkan document root mutlak ke folder `public/` adalah pagar pengaman nomor satu aplikasi web Laravel. Konfigurasi segera dikembalikan dengan aman ke `public/`. |
| 3 | Jalankan `config:cache` dengan `env()` masih terpanggil di controller | Pemanggilan fungsi `env()` pada controller akan menghasilkan nilai `null` di lingkungan produksi karena Laravel tidak lagi membaca berkas `.env` secara dinamis setelah konfigurasi di-cache. | Tampilan aplikasi mengalami galat (*bug/error*) karena variabel konfigurasi yang diharapkan bernilai string mendadak menjadi kosong (`null`). Saat saya menempatkan kode uji `env('APP_NAME')` pada controller lalu mengeksekusi `php artisan config:cache`, nilai yang dihasilkan adalah `null` sehingga judul aplikasi di halaman tersebut lenyap. Hal ini terjadi karena saat `config:cache` dijalankan, Laravel menggabungkan seluruh berkas konfigurasi di folder `config/*.php` ke dalam satu berkas cache PHP terkompilasi (`bootstrap/cache/config.php`) dan sengaja menonaktifkan pemuatan berkas `.env` pada setiap request demi efisiensi CPU dan RAM. Eksperimen ini mempertegas aturan bahwa fungsi `env()` haram digunakan di luar folder `config/`, dan kode aplikasi wajib menggunakan fungsi helper `config('app.name')`. |
| 4 | Hentikan Supervisor, buat tugas baru | Pembuatan tugas oleh dosen akan tampak sukses di web, namun antrean notifikasi ke mahasiswa macet total di tabel database tanpa memunculkan pesan error apa pun (*silent failure*). | Terjadi kegagalan senyap (*silent failure*) yang sangat berbahaya bagi operasional kampus. Saat saya mematikan worker menggunakan perintah `supervisorctl stop kampuslms-worker:*` lalu dosen mempublikasikan tugas kuliah baru, web menampilkan notifikasi sukses hijau "Tugas berhasil dibuat". Namun ketika login sebagai akun mahasiswa, lonceng notifikasi tetap angka 0 dan mahasiswa tidak mengetahui adanya tugas baru. Saat tabel basis data diperiksa, job notifikasi (`SendAssignmentPublishedNotification`) hanya tertimbun dan berstatus pending di tabel `jobs`. Di berkas `storage/logs/laravel.log` pun tidak ada error yang tercatat. Begitu worker dihidupkan kembali via `supervisorctl start kampuslms-worker:*`, kedua worker langsung terbangun, memproses antrean, dan notifikasi seketika membanjiri akun mahasiswa. Ini membuktikan bahwa tanpa monitoring Supervisor yang handal, antrean asinkron dapat lumpuh tanpa terdeteksi. |
| 5 | Deploy kode baru tanpa `queue:restart` | Queue worker yang sedang berjalan akan terus mengeksekusi kode program logika lama yang tersimpan di RAM, sehingga perubahan kode terbaru pada antrean tidak akan pernah diterapkan. | Terbukti worker tetap menjalankan kode usang (*stale code*). Ketika saya memperbarui isi format teks pesan pada berkas Listener notifikasi tugas, lalu melakukan *pull* kode baru ke server tanpa menjalankan `php artisan queue:restart`, notifikasi baru yang diproses oleh worker dan diterima mahasiswa tetap berformat teks lama. Hal ini terjadi karena queue worker Laravel berjalan sebagai *long-running persistent process* yang hanya membaca kode PHP ke memori RAM sekali saat pertama kali di-boot. Worker tidak pernah membaca ulang berkas PHP yang berubah di disk sebelum prosesnya direstart. Begitu perintah `php artisan queue:restart` dieksekusi, sinyal restart dikirimkan secara anggun (*graceful*), worker menghentikan siklus lamanya, lalu memuat ulang kode versi terkini dari disk. |
| 6 | Set izin `storage` menjadi 555 | Web server akan mengembalikan status error `HTTP 500 Internal Server Error`, dan parahnya Laravel bahkan tidak mampu mencatat pesan kesalahan tersebut ke berkas `laravel.log` karena hak akses tulis dicabut. | Halaman web seketika lumpuh total dan peramban hanya menampilkan pesan putih `HTTP 500 Internal Server Error`. Ketika berkas `storage/logs/laravel.log` dibuka untuk mencari tahu penyebabnya, ternyata tidak ada baris log baru sama sekali karena user web server (`www-data`) tidak memiliki hak akses tulis (*write permission* `w`) untuk menyimpan pesan crash. Jejak error baru dapat ditemukan setelah memeriksa log internal web server di `/var/log/apache2/error.log` yang menampilkan pesan fatal: `Uncaught ErrorException: file_put_contents(/var/www/html/storage/framework/views/...): Failed to open stream: Permission denied`. Eksperimen ini mendemonstrasikan bahwa pemberian izin direktori `storage` dan `bootstrap/cache` (wajib `chmod 775` dan `chown www-data:www-data`) adalah syarat hidup paling mendasar bagi aplikasi Laravel di server produksi. |
| 7 | Set `upload_max_filesize=1M`, unggah berkas 3 MB | Unggahan berkas submission mahasiswa akan dipotong atau ditolak langsung oleh interpreter PHP sebelum mencapai logika validasi FormRequest Laravel, menyebabkan variabel file menjadi kosong. | Proses pengumpulan tugas langsung gagal total secara membingungkan. Ketika mahasiswa mencoba mengunggah dokumen tugas berukuran 3 MB (yang sebenarnya sah menurut validasi Laravel `max:10240` atau 10 MB), formulir web langsung memantul kembali dengan pesan error validasi "File tugas wajib diunggah" atau memicu `PostTooLargeException`. Saat isi request diperiksa, objek `$request->file('file')` dan variabel global `$_FILES` bernilai kosong (`null`). Hal ini terjadi karena batas `upload_max_filesize` dan `post_max_size` pada level konfigurasi PHP runtime (`php.ini`) selalu mengalahkan dan menyaring request sebelum framework Laravel sempat memvalidasinya. Eksperimen ini menegaskan pentingnya menyelaraskan konfigurasi `php.ini` server dengan batas unggahan pada aplikasi web. |
| 8 | Akses situs lewat `http://` setelah `SESSION_SECURE_COOKIE=true` | Peramban web akan menolak mengirimkan cookie sesi melalui koneksi tidak terenkripsi (HTTP), sehingga pengguna tidak akan pernah bisa login (*infinite login loop* atau error 419 CSRF Token Expired). | Pengguna terjebak dalam lingkaran login berulang (*infinite redirect loop*) dan memicu pesan kesalahan `419 Page Expired (CSRF Token Mismatch)`. Ketika saya mengakses web via `http://` (tanpa HTTPS) lalu memasukkan email dan kata sandi yang valid, tombol login ditekan tetapi halaman selalu kembali ke form login kosong tanpa pernah berhasil masuk ke dashboard. Pada tab Storage & Network di DevTools browser, peramban sengaja memblokir dan menolak menyertakan cookie `kampuslms_session` kembali ke server pada setiap request HTTP karena cookie tersebut memiliki atribut `Secure`. Akibatnya, server memperlakukan setiap klik pengguna sebagai sesi tamu baru yang belum terautentikasi. Eksperimen ini membuktikan bahwa proteksi `SESSION_SECURE_COOKIE=true` sangat ampuh mencegah pembajakan sesi di jaringan umum (*man-in-the-middle sniffing*), dan wajib didampingi oleh konfigurasi pengalihan otomatis HTTP ke HTTPS (*301 redirect*) di level web server. |

