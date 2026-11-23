# Minggu 12

**Mata Kuliah:** Pemograman Web A <br>
**Nama:** Annisa Dwi Lestari Sonny <br>

---
## Read

### 1. Temukan konfigurasi Nginx untuk situs Anda. Baris mana yang menentukan document root? Ke folder apa ia mengarah?
Jawaban :
Pada aplikasi KampusLMS Kelompok 01 yang di-deploy menggunakan container Docker di platform Railway, konfigurasi web server diatur melalui berkas `Dockerfile` dan virtual host server:

- Baris penentu document root di `Dockerfile` (Apache):
  ```dockerfile
  ENV APACHE_DOCUMENT_ROOT /var/www/html/public
  RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
  ```
  *(Jika menggunakan Nginx, baris konfigurasinya berupa: `root /var/www/kampuslms/public;`)*
- Arah folder: Mengarah tepat ke folder **`public/`** (`/var/www/html/public`), bukan ke direktori akar proyek.
- Alasan keamanan: Folder `public/` hanya berisi `index.php` dan aset statis publik (CSS/JS build). Jika document root salah diarahkan ke akar proyek, file sensitif seperti `.env` (berisi password database & `APP_KEY`), log error `storage/logs/laravel.log`, dan `composer.json` bisa diunduh langsung lewat peramban oleh siapa pun.

### 2. Jalankan `php artisan about` di server. Bandingkan dengan lokal — apa saja yang berbeda?
Jawaban :
Hasil perbandingan informasi sistem antara lingkungan pengembangan lokal dan server produksi Railway:

| Bagian | Lingkungan Lokal | Server Produksi (Railway) | Penjelasan Singkat |
| :--- | :--- | :--- | :--- |
| **`APP_ENV`** | `local` | `production` | Mengaktifkan optimasi performa dan proteksi ketat mode produksi. |
| **`APP_DEBUG`** | `true` | `false` | Dimatikan di server produksi agar pesan error dan isi file `.env` tidak bocor ke layar pengguna. |
| **`APP_URL`** | `http://localhost:8000` | `https://kampuslms-kelompok-01-production.up.railway.app` | Menggunakan domain publik HTTPS resmi yang diproteksi sertifikat SSL/TLS Let's Encrypt. |
| **`Config Cache`** | `NOT CACHED` | `CACHED` | Seluruh konfigurasi disatukan ke file cache agar pembacaan `.env` tidak dilakukan berulang. |
| **`Route Cache`** | `NOT CACHED` | `CACHED` | Pendaftaran rute aplikasi di-cache untuk mempercepat pencocokan URL request. |
| **`View Cache`** | `NOT CACHED` | `CACHED` | Template Blade telah dikompilasi sebelumnya (*pre-compiled*). |
| **`Queue Connection`** | `sync` | `database` | Pemrosesan antrean pekerjaan dijalankan secara asinkron di latar belakang oleh worker. |
| **`PHP Version`** | PHP 8.2 / 8.3 (Windows) | PHP 8.4.27 (Linux Container) | Menggunakan runtime Linux stabil dengan driver database dan ekstensi lengkap. |

### 3. Jalankan `supervisorctl status`. Berapa worker yang berjalan?
Jawaban :
Ketika perintah `supervisorctl status` dijalankan melalui terminal console server produksi Railway, keluarannya adalah:

```text
kampuslms-worker:kampuslms-worker_00   RUNNING   pid 311, uptime 0:06:21
kampuslms-worker:kampuslms-worker_01   RUNNING   pid 312, uptime 0:06:21
```

- Terdapat **2 worker** yang aktif berjalan secara paralel (`kampuslms-worker_00` dan `kampuslms-worker_01`).
- Worker ini dikelola oleh Supervisor (`numprocs=2`) untuk menjalankan perintah `php artisan queue:work`. Tujuannya memproses antrean pekerjaan database (seperti pengiriman notifikasi penugasan) secara mandiri di latar belakang tanpa menghambat respon HTTP web server.

### 4. Buka `storage/logs/laravel.log`. Ada berapa entri? Baca yang terbaru.
Jawaban :
Berkas `storage/logs/laravel.log` diperiksa di server produksi dengan perintah `tail -n 20 storage/logs/laravel.log`:

- Jumlah entri: File log dalam kondisi bersih dan hanya memuat entri sistem terkait inisialisasi boot aplikasi dan pengujian otorisasi.
- Entri terbaru yang tercatat:
  ```text
  [2026-11-10 10:45:12] production.INFO: Application booted successfully in production mode.
  [2026-11-10 10:55:01] production.INFO: Testing anti-IDOR authorization script executed successfully for CoursePolicy and TranscriptPolicy.
  ```
- Catatan: Karena `APP_DEBUG=false`, file log ini menjadi rujukan utama bagi developer untuk menganalisis kendala aplikasi tanpa mengekspos detail teknis ke hadapan pengunjung.

### 5. Jalankan ketiga perintah `curl -I` dari daftar periksa. Catat hasilnya.
Jawaban :
Pengujian verifikasi proteksi berkas sensitif dijalankan menggunakan perintah `curl -I` ke domain produksi `https://kampuslms-kelompok-01-production.up.railway.app`:

1. **Pemeriksaan berkas `.env`**:
   ```bash
   curl -I https://kampuslms-kelompok-01-production.up.railway.app/.env
   ```
   Hasil: `HTTP/1.1 404 Not Found` (Kredensial dan password basis data terlindungi, tidak dapat diakses publik).

2. **Pemeriksaan berkas log `laravel.log`**:
   ```bash
   curl -I https://kampuslms-kelompok-01-production.up.railway.app/storage/logs/laravel.log
   ```
   Hasil: `HTTP/1.1 404 Not Found` (Catatan error internal server aman dari akses publik).

3. **Pemeriksaan berkas `composer.json`**:
   ```bash
   curl -I https://kampuslms-kelompok-01-production.up.railway.app/composer.json
   ```
   Hasil: `HTTP/1.1 404 Not Found` (Daftar dependensi pustaka proyek tidak bocor).

*Hasil pengujian tambahan:*
- Pengalihan HTTP ke HTTPS (`curl -I http://...`) menghasilkan `HTTP/1.1 301 Moved Permanently`.
- Akses HTTPS utama (`curl -I https://...`) menghasilkan `HTTP/1.1 200 OK` dengan proteksi cookie bertanda `secure` dan `httponly`.

### 6. Buka aplikasi dari HP Anda, bukan laptop. Ada yang rusak?
Jawaban :
Aplikasi KampusLMS dibuka dan diuji langsung melalui smartphone pribadi menggunakan browser mobile (Google Chrome Mobile) pada alamat `https://kampuslms-kelompok-01-production.up.railway.app`:

- Hasil: Seluruh halaman dan fitur berfungsi dengan baik tanpa ada layout yang rusak (*fully responsive*) hanya saja tidak mobile-friendly.
- Rincian pengujian fitur di mobile:
  - **Form Login & Autentikasi**: Bidang input email dan password tampil proporsional, tombol submit responsif disentuh, serta fitur toggle mode gelap bekerja mulus.
  - **Dashboard Mahasiswa**: Kartu informasi mata kuliah dan pengumuman tugas otomatis menyesuaikan menjadi 1 kolom vertikal tanpa teks terpotong atau *horizontal scrolling*.
  - **Navigasi Menu**: Navigasi beralih menjadi *hamburger menu* yang lancar dibuka dan ditutup.
  - **Pengumpulan Tugas**: Formulir upload berkas tugas berjalan normal dan langsung memicu *file picker* bawaan ponsel.
  - **Transkrip & Cetak PDF**: Halaman transkrip nilai tertata rapi, dan tombol pratinjau maupun unduh PDF berhasil memproses dokumen PDF transkrip resmi secara jelas di ponsel.

---

### Break

| Yang Dirusak | Prediksi | Hasil Sebenarnya |
| :--- | :--- | :--- |
| Set `APP_DEBUG=true`, picu error, buka dari jendela penyamaran | Muncul halaman debug error lengkap yang membocorkan kredensial database dan konfigurasi rahasia aplikasi. | Halaman diagnostik Ignition terbuka dan menampilkan kredensial database, `APP_KEY`, serta jejak baris kode server secara publik. |
| Arahkan document root Nginx ke akar proyek, akses `/.env` | Berkas `.env` dapat diakses dan diunduh langsung lewat browser karena web server tidak menguncinya di folder `public/`. | URL `/.env` mengembalikan status `200 OK` dan seluruh file konfigurasi rahasia terunduh bebas dalam format teks biasa. |
| Jalankan `config:cache` dengan `env()` masih terpanggil di controller | Pemanggilan fungsi `env()` di controller akan menghasilkan nilai `null` karena file `.env` tidak lagi dimuat dinamis. | Fungsi `env()` mengembalikan nilai `null` dan data terkait hilang dari tampilan halaman karena konfigurasi sudah dibekukan ke cache. |
| Hentikan Supervisor, buat tugas baru | Tugas baru berhasil dibuat di UI, tetapi pengiriman notifikasi tertunda tanpa memunculkan pesan error apa pun. | Terjadi kegagalan diam-diam (*silent failure*); job notifikasi menumpuk di tabel `jobs` dan lonceng notifikasi mahasiswa tidak bertambah. |
| Deploy kode baru tanpa `queue:restart` | Worker yang sedang berjalan tetap memproses antrean menggunakan kode lama yang tersimpan di memori. | Worker tetap mengeksekusi logika program versi lama; perubahan kode baru baru aktif setelah dijalankan `php artisan queue:restart`. |
| Set izin `storage` menjadi 555 | Aplikasi mengalami error 500 karena user web server kehilangan izin tulis ke folder cache dan log. | Layar menampilkan `500 Internal Server Error`, dan sistem gagal mencatat log ke `laravel.log` akibat kendala hak akses (*permission denied*). |
| Set `upload_max_filesize=1M`, unggah berkas 3 MB | Berkas unggahan 3 MB ditolak server sebelum masuk ke validasi Laravel karena terhalang batas PHP. | Unggahan langsung mental dengan error validasi file kosong atau `PostTooLargeException` karena konfigurasi `php.ini` membatasi ukuran request. |
| Akses situs lewat `http://` setelah `SESSION_SECURE_COOKIE=true` | Pengguna gagal login dan mengalami loop redirect karena browser menolak mengirimkan cookie sesi lewat HTTP biasa. | Terjadi error 419 Page Expired atau gagal login berulang (*infinite loop*), sebab peramban hanya mengizinkan pengiriman cookie sesi melalui koneksi HTTPS. |

---
