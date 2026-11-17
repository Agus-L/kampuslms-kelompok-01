# Catatan Minggu 12 — Deployment & Produksi

## Identitas Mahasiswa

Nama: Anastasya Salsabila Khoirunnisa  
NIM: 10241009  

---

## READ 

Prinsip utama materi Minggu 12 adalah: **"Mengubah aplikasi yang berjalan di laptop lokal menjadi aplikasi yang hidup di internet publik dan tangguh saat digunakan bersamaan tanpa membocorkan rahasia data"**. 

Setelah aplikasi KampusLMS berhasil di-deploy ke lingkungan produksi pada layanan *cloud* Railway (**`https://kampuslms-kelompok-01-production.up.railway.app`**), berikut hasil bedah konfigurasi server dan investigasi komponen produksinya:

---

### 1. Konfigurasi Web Server & Document Root

**Lokasi Konfigurasi:**  
Pada arsitektur container produksi di Railway, konfigurasi web server dikelola melalui file `Dockerfile` berbasis Apache (`php:8.4-apache`) serta file konfigurasi virtual host Apache (`/etc/apache2/sites-available/000-default.conf` dan `/etc/apache2/apache2.conf`).

**Baris Penentu Document Root:**
```dockerfile
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
```
*(Pada konfigurasi Nginx standar, ditentukan pada baris: `root /var/www/kampuslms/public;`)*.

**Ke Folder Apa Mengarah:**  
Mengarah tepat ke folder **`/public`** (`/var/www/html/public`), **BUKAN** ke akar proyek (`/var/www/html`).

**Mengapa Document Root Wajib ke `public/` dan Apa yang Bocor Jika Salah Arah?**
- **Penjelasan Teknis:** File publik yang boleh diakses langsung oleh peramban (*browser*) dari internet hanyalah `index.php`, file gambar aset, berkas CSS, dan JavaScript terkompilasi di dalam folder `public/`.
- **Bahaya Fatal Jika Salah Arah (Mengarahkan ke Akar Proyek):**  
  Jika document root diarahkan ke akar proyek (`/var/www/html`), maka seluruh struktur direktori proyek menjadi terbuka untuk publik. Siapa pun di internet dapat mengunduh berkas sensitif secara langsung lewat URL peramban:
  1. `/.env` $\rightarrow$ Berisi kata sandi basis data produksi, `APP_KEY`, kunci API, dan kredensial rahasia.
  2. `/composer.json` $\rightarrow$ Membocorkan seluruh daftar pustaka vendor beserta versinya (memudahkan penyerang memetakan celah keamanan CVE).
  3. `/storage/logs/laravel.log` $\rightarrow$ Membocorkan jejak kode aplikasi (*stack trace*), struktur direktori server, dan kueri SQL internal.

---

### 2. Tabel Perbandingan `php artisan about` (Lokal vs Produksi)

Perintah `php artisan about` dijalankan untuk membedah perbedaan mendasar antara lingkungan pengembangan (*development*) di laptop dengan lingkungan produksi (*production*) di server *cloud* Railway:

| Parameter Metrik | Lingkungan Lokal (Laptop) | Lingkungan Produksi (Server Railway) | Implikasi & Alasan Teknis |
| :--- | :--- | :--- | :--- |
| **Application Environment (`APP_ENV`)** | `local` | **`production`** | Di produksi, Laravel mengaktifkan lapisan pengamanan ketat; menonaktifkan fitur berbahaya seperti auto-reload dan generator dev. |
| **Debug Mode (`APP_DEBUG`)** | `ENABLED` (`true`) | **`DISABLED` (`false`)** | **Aturan mutlak:** Di produksi wajib `false`. Jika terjadi error, pengunjung hanya melihat halaman error 500 generik tanpa membocorkan kredensial `.env`. |
| **URL Aplikasi (`APP_URL`)** | `http://localhost:8000` | `https://kampuslms-kelompok-01-production.up.railway.app` | Digunakan untuk generasi tautan berkas resmi, surat transkrip, email notifikasi, dan memastikan rute HTTPS valid. |
| **Versi PHP & Server** | `PHP 8.5.10` (Windows CLI) | `PHP 8.4.27` (Linux Debian Container Apache) | Lingkungan Linux container Apache menjamin konsistensi *runtime* pustaka GD, SQLite, dan modul rewrite URL. |
| **Konfigurasi Cache (`Config Cache`)** | `NOT CACHED` | **`CACHED`** (`php artisan config:cache`) | Di produksi, seluruh berkas konfigurasi digabung menjadi satu file memori cepat. Fungsi `env()` di luar `config/` tidak boleh dipanggil lagi. |
| **Rute Cache (`Route Cache`)** | `NOT CACHED` | **`CACHED`** (`php artisan route:cache`) | Pendaftaran rute di-serialize menjadi array cepat, mempercepat waktu pencocokan URL hingga 5–10× lipat. |
| **Tampilan Cache (`View Cache`)** | `CACHED` | **`CACHED`** (`php artisan view:cache`) | Seluruh template Blade sudah di-compile menjadi kode PHP mentah, menghemat komputasi CPU server. |
| **Event Cache (`Event Cache`)** | `NOT CACHED` | **`CACHED`** (`php artisan event:cache`) | Pemetaan listener event penugasan dan penilaian langsung termuat tanpa pemindaian ulang direktori. |
| **Driver Basis Data (`DB_CONNECTION`)** | `sqlite` | `sqlite` (`/var/www/html/database/database.sqlite`) | Menggunakan basis data SQLite berkinerja tinggi yang dikunci izin aksesnya oleh user `www-data`. |
| **Driver Antrean (`QUEUE_CONNECTION`)** | `database` | `database` (Dikelola oleh Supervisor) | Pemrosesan tugas latar belakang (notifikasi lonceng/email) dijalankan secara mandiri oleh *queue worker*. |
| **Tautan Berkas (`Storage Link`)** | `NOT LINKED` | **`LINKED`** (`public/storage` $\rightarrow$ `storage/app/public`) | Menghubungkan aset publik statis agar dapat diakses langsung oleh peramban web tanpa membebani PHP. |

---

### 3. Pengawasan Proses Antrean Latar Belakang (Supervisor)

Pemeriksaan status manajer proses Supervisor dilakukan langsung dari terminal server (`Console`) pada container Railway:

```bash
root@a14d021bfe1f:/var/www/html# supervisorctl status
kampuslms-worker:kampuslms-worker_00   RUNNING   pid 311, uptime 0:06:21
kampuslms-worker:kampuslms-worker_01   RUNNING   pid 312, uptime 0:06:21
```

**Temuan Lapangan:**
- **Jumlah Worker yang Berjalan:** Ada **2 worker** (`numprocs=2`), yaitu `kampuslms-worker_00` dan `kampuslms-worker_01`. Keduanya dalam status **`RUNNING`** secara stabil.
- **Mengapa Supervisor Mutlak Dibutuhkan?**  
  Jika perintah `php artisan queue:work` dijalankan secara manual melalui sesi terminal SSH, worker tersebut akan **otomatis mati begitu kita menutup jendela terminal atau logout**. Akibatnya, notifikasi penugasan dan pengumpulan tugas mahasiswa akan berhenti terkirim tanpa jejak (*silent failure*).
- **Peran Supervisor:**  
  Bertindak sebagai pengawas (*daemon*) 24 jam nonstop. Supervisor otomatis menyalakan worker saat server *booting* (`autostart=true`), dan **otomatis membangkitkan kembali worker jika mengalami crash / mati kehabisan memori** (`autorestart=true`).

---

### 4. Audit Berkas Catatan Kesalahan (`storage/logs/laravel.log`)

Di lingkungan produksi dengan `APP_DEBUG=false`, layar pengunjung hanya akan menampilkan halaman error 500 generik tanpa petunjuk apa pun. Oleh karena itu, **satu-satunya sumber informasi valid bagi pengembang adalah membaca berkas catatan server**:

```bash
tail -n 50 storage/logs/laravel.log
```

**Hasil Pengamatan Berkas Log:**
- **Jumlah Entri:** Pada siklus awal deployment terdapat beberapa entri log pengujian, seperti pencatatan migrasi tabel, pengisian seeder, serta penanganan error validasi berkas.
- **Karakteristik Entri Log Produksi:**  
  Setiap baris mencatat stempel waktu UTC, tingkat urgensi (`INFO`, `WARNING`, `ERROR`), konteks pesan error, dan baris kode penyebab tanpa menampilkan rincian tersebut ke layar peramban publik.
- **Pelajaran Krusial:** Kita **dilarang keras menyalakan `APP_DEBUG=true` di server produksi** hanya untuk mencari penyebab error. Cara profesional dan aman adalah dengan memantau langsung aliran log server (`tail -f storage/logs/laravel.log`).

---

### 5. Uji Akses Keamanan dengan Perintah `curl -I`

Sesuai daftar periksa keamanan produksi pada subbab 12.1, pengujian verifikasi akses publik dilakukan terhadap domain resmi:

```bash
# Uji 1: Memastikan file rahasia .env ditolak server
curl.exe -I https://kampuslms-kelompok-01-production.up.railway.app/.env
# Hasil: HTTP/1.1 404 Not Found (LOLOS - Berkas Aman)

# Uji 2: Memastikan catatan log sistem tidak dapat diintip publik
curl.exe -I https://kampuslms-kelompok-01-production.up.railway.app/storage/logs/laravel.log
# Hasil: HTTP/1.1 404 Not Found (LOLOS - Berkas Aman)

# Uji 3: Memastikan file dependensi composer tidak dapat diunduh
curl.exe -I https://kampuslms-kelompok-01-production.up.railway.app/composer.json
# Hasil: HTTP/1.1 404 Not Found (LOLOS - Berkas Aman)

# Uji 4: Memverifikasi endpoint publik utama
curl.exe -I https://kampuslms-kelompok-01-production.up.railway.app/login
# Hasil: HTTP/1.1 200 OK (Koneksi HTTPS Terenkripsi, Cookie Secure & HttpOnly Terpasang)
```

**Analisis:** Seluruh berkas sensitif berhasil terlindungi dan menghasilkan respon **404 Not Found**, membuktikan konfigurasi document root Apache telah membatasi akses publik hanya pada direktori `public/`.

---

### 6. Pengujian Lintas Perangkat (Smartphone / HP Anggota Kelompok)

Pengujian akses nyata dilakukan menggunakan peramban mobile (smartphone) anggota kelompok:
- **Tampilan Antarmuka:** Tata letak (*layout*) responsif Tailwind CSS v4 bekerja dengan presisi; form login, navigasi menu samping, dan kartu statistik menyesuaikan ukuran layar ponsel tanpa adanya komponen yang terpotong (*broken layout*).
- **Fungsi Autentikasi:** Fitur *"Akses Cepat Akun Demo"* berfungsi mulus, cookie sesi berhasil terbentuk via protokol HTTPS, dan navigasi peran (Admin, Dosen, Mahasiswa) dapat digunakan secara normal.

---

## BREAK 

> ⛔ **Peringatan Keamanan Produksi:**  
> Percobaan 1 dan 2 membuka celah rahasia server ke internet. Pemindaian bot internet mencari halaman debug dan file `.env` berlangsung otomatis 24 jam nonstop. Oleh karena itu, pengujian dilakukan di lingkungan terkontrol dengan basis data percobaan, dan segera dinonaktifkan kembali (*reverted*) setelah pengamatan selesai.

Hasil investigasi dan pengujian 8 skenario eksperimen dari subbab 12.3 modul:

| # | Skenario yang Dicoba | Apa Prediksinya? | Apa yang Terjadi Sebenarnya? (Penjelasan Analisis & Dampak) |
|:---:|:---|:---|:---|
| **1** | Set `APP_DEBUG=true`, picu error 500, lalu buka dari jendela penyamaran (*incognito*) | Halaman detail error akan tampil ke layar dan dapat dibaca oleh publik. | **Seluruh rahasia server terpapar telanjang di layar!** Muncul halaman debug Laravel (Whoops) yang menampilkan isi lengkap file `.env` (termasuk kata sandi basis data produksi, `APP_KEY`, konfigurasi sesi), jejak baris kode program, dan versi PHP/paket vendor. Jika penyerang melihat ini, seluruh database kampus dapat diambil alih dalam hitungan menit. |
| **2** | Arahkan document root web server ke akar proyek, lalu buka URL `/.env` | Berkas konfigurasi `.env` dapat diakses langsung oleh peramban. | **Berkas `.env` langsung terunduh secara utuh ke komputer penyerang!** Karena web server tidak lagi mengisolasi folder `public/`, semua berkas di dalam server dianggap sebagai berkas statis biasa. Penyerang cukup mengetik `domain.com/.env` untuk membaca seluruh kunci rahasia aplikasi. |
| **3** | Jalankan `php artisan config:cache` sementara fungsi `env()` masih terpanggil di Controller/Model | Fungsi `env()` akan gagal membaca nilai dan mengembalikan nilai kosong (`null`). | **Aplikasi meledak error karena nilai konfigurasi berubah menjadi `null`!** Saat `config:cache` dieksekusi, Laravel menggabungkan seluruh file konfigurasi dan berhenti memuat file `.env` ke memori PHP. Akibatnya, fungsi `env('KUNCI')` di luar folder `config/` akan mengembalikan `null`. Inilah sebabnya mengapa di controller wajib memanggil `config('services.kunci')`. |
| **4** | Hentikan proses Supervisor (`supervisorctl stop all`), lalu buat penugasan baru di web | Tugas berhasil dibuat oleh dosen, namun proses di latar belakang akan macet. | **Terjadi *Silent Failure* (kegagalan tanpa pesan error).** Dosen merasa penugasan berhasil diunggah, namun antrean notifikasi lonceng ke seluruh mahasiswa terhenti permanen di tabel `jobs`. Mahasiswa tidak pernah menerima notifikasi karena tidak ada worker yang memproses antrean tersebut. |
| **5** | Deploy kodingan baru ke server tanpa menjalankan `php artisan queue:restart` | Queue worker akan tetap menjalankan kodingan versi lama. | **Perubahan kode baru tidak diterapkan pada antrean!** Proses worker yang berjalan di memori RAM server masih memegang kode program versi sebelumnya. Notifikasi atau pemrosesan tugas masih mengikuti logika lama hingga worker di-restart menggunakan perintah `php artisan queue:restart`. |
| **6** | Ubah hak izin folder penyimpanan menjadi read-only: `chmod -R 555 storage` | Aplikasi akan langsung mengalami error saat mencoba menulis sesi, cache, atau log. | **Layar langsung menampilkan Error 500, dan sistem log lumpuh!** Laravel membutuhkan izin tulis (*write permission*) pada folder `storage/` dan `bootstrap/cache/` untuk membuat file session, cache blade, dan mencatat log. Karena izinnya 555 (hanya baca), PHP melempar exception fatal dan bahkan tidak bisa menuliskan pesan error tersebut ke file `laravel.log`. |
| **7** | Atur batas `upload_max_filesize=1M` di `php.ini`, lalu unggah berkas tugas berukuran 3 MB | Pengunggahan berkas ditolak oleh server sebelum mencapai kode validasi Laravel. | **Pengunggahan langsung gagal dengan pesan error samar / HTTP 413 (Payload Too Large).** Batas konfigurasi server PHP mengalahkan validasi `max:5120` milik Laravel. Berkas ditolak mentah-mentah di gerbang web server sebelum framework Laravel sempat memeriksa isi berkas (*oleh-oleh konsep Minggu 9*). |
| **8** | Akses situs melalui protokol tidak aman `http://` setelah `SESSION_SECURE_COOKIE=true` | Peramban menolak mengirimkan cookie sesi pengguna. | **Pengguna tidak akan pernah bisa login (terlempar kembali ke form login)!** Pengaturan `SESSION_SECURE_COOKIE=true` memerintahkan peramban hanya mengirimkan cookie autentikasi melalui koneksi terenkripsi HTTPS. Saat diakses lewat HTTP biasa, peramban memblokir pengiriman cookie sesi, sehingga setiap klik akan dianggap sebagai pengguna baru yang belum login. |

---

## Keterkaitan dengan Prompt Pack & Checkpoint Minggu 12

Praktek Read dan Break di atas berhubungan langsung dengan 4 pedoman Prompt Pack pada Modul 12:

1. **Keterkaitan dengan Prompt Pack A (Rencana Deployment Sistematis):**  
   Membuktikan bahwa deployment bukan sekadar mengunggah file lewat FTP, melainkan rangkaian konfigurasi berurutan: penyiapan dependensi produksi (`--no-dev`), kompilasi aset frontend Vite, migrasi basis data terproteksi (`--force`), dan pembuatan cache produksi.
2. **Keterkaitan dengan Prompt Pack B (Audit Keamanan Pasca-Deployment):**  
   Menjadi dasar verifikasi checklist keamanan: memastikan seluruh pengujian `curl -I` pada `.env` menghasilkan 404, HTTPS aktif, dan `SESSION_SECURE_COOKIE=true`.
3. **Keterkaitan dengan Prompt Pack C (Strategi Debugging Tanpa `APP_DEBUG`):**  
   Melatih mahasiswa untuk mendiagnosis kendala di server produksi menggunakan pemeriksaan log (`storage/logs/laravel.log`) dan status worker secara profesional tanpa tergoda menyalakan mode debug di server publik.
4. **Keterkaitan dengan Prompt Pack D (Otomatisasi Skrip `deploy.sh`):**  
   Membuktikan pentingnya baris `set -e` agar skrip berhenti saat ada kegagalan, keharusan perintah `php artisan down` selama migrasi, serta kewajiban menyertakan `php artisan queue:restart` setelah pembaruan kode.

---

## Kesimpulan Singkat untuk Dipahami (Bahan Interview Dosen)

1. **Tiga Pelanggaran Fatal Diskualifikasi Otomatis:**
   - Menyalakan `APP_DEBUG=true` di server publik.
   - Membiarkan file rahasia `.env` dapat diunduh melalui internet (document root salah arah).
   - Membiarkan berkas pengumpulan tugas (*submission*) privat dapat diunduh tanpa login.
2. **Document Root adalah Benteng Utama:** Web server wajib mengarah ke folder `/public` agar folder sistem lainnya terlindungi dari akses publik.
3. **Supervisor Penjaga Nyawa Antrean:** Queue worker tidak boleh dijalankan manual di terminal SSH, melainkan wajib diawasi oleh Supervisor agar otomatis bangkit jika terjadi crash.
4. **Pentingnya `queue:restart`:** Setiap kali ada pembaruan kode di server, worker wajib di-restart agar memuat logika program yang baru.
5. **Kinerja vs Keamanan:** Optimisasi cache (`config:cache`, `route:cache`, `view:cache`) membuat aplikasi berkinerja tinggi, namun menuntut disiplin koding (tidak boleh memanggil `env()` sembarangan di luar file config).

