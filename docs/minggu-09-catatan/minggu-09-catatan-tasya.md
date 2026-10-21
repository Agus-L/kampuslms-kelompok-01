# Catatan Minggu 9

## Identitas Mahasiswa

Nama: Anastasya Salsabila Khoirunnisa  
NIM: 10241009  

---

## READ — Telusuri Satu Berkas 

Berdasarkan penelusuran arsitektur Laravel 12 pada proyek KampusLMS terkait penanganan berkas (file management), berikut hasil analisis mendalam terhadap 5 tahapan penelusuran berkas materi dan tugas:

---

### 1. Lokasi Berkas Fisik di Sistem Berkas
- **Disk Penyimpanan:**  
  Berkas privat disimpan di disk `local` yang mengarah ke direktori privat framework.
- **Konfigurasi Path:**  
  Pada [`kampuslms-kelompok-01/config/filesystems.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/config/filesystems.php#L33-L39):
  ```php
  'local' => [
      'driver' => 'local',
      'root' => storage_path('app/private'),
      'serve' => true,
      'throw' => false,
      'report' => false,
  ],
  ```
- **Path Lengkap di Sistem Berkas (Windows OS):**  
  Ketika sebuah materi kuliah diunggah untuk Mata Kuliah dengan ID 1 via [`MaterialController::store()`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/Dosen/MaterialController.php#L91-L92):
  ```
  c:\Proweb\kampuslms-kelompok-01\kampuslms-kelompok-01\storage\app\private\materials\courses\1\[hash_acak].pdf
  ```
- **Karakteristik Penamaan Berkas:**  
  Nama berkas diubah otomatis menjadi hash acak (contoh: `dK8z9xLaM04qp...pdf`) oleh method `$file->store()`. Nama asli dari laptop pengguna tidak digunakan sebagai nama berkas fisik di filesystem.

---

### 2. Korelasi Database vs Lokasi Fisik
- **Baris Record Database (Tabel `materials`):**  
  Pada tabel `materials`, kolom yang menyimpan referensi berkas adalah:
  - `file_path`: `materials/courses/1/dK8z9xLaM04qp...pdf`
  - `original_name`: `Modul-01-Pengenalan-Web.pdf` (nama asli untuk tampilan pengguna)
  - `file_size`: `2048576` (ukuran dalam satuan bytes)
  - `mime_type`: `application/pdf`
- **Perbandingan Nilai `file_path` dengan Lokasi Fisik:**  
  Nilai pada kolom `file_path` di database adalah relative path terhadap root disk `local`.  
  Laravel menerjemahkannya melalui:
  ```php
  Storage::disk('local')->path($material->file_path)
  // Menghasilkan:
  // C:\Proweb\kampuslms-kelompok-01\kampuslms-kelompok-01\storage\app\private\materials\courses\1\dK8z9xLaM04qp...pdf
  ```
- **Manfaat Pemisahan Ini:**  
  Aplikasi terisolasi dari struktur absolut sistem operasi server hosting. Jika aplikasi dipindahkan antar server (misal dari Windows ke Linux Ubuntu di VPS), path basis data tetap valid tanpa perlu pembaruan string path direktori server.

---

### 3. Uji Akses Langsung Lewat Browser (Tebak URL)
- **Percobaan Akses Langsung:**  
  Mencoba membuka URL langsung di browser:
  - `http://127.0.0.1:8000/storage/materials/courses/1/dK8z9xLaM04qp...pdf`  
  - `http://127.0.0.1:8000/app/private/materials/courses/1/dK8z9xLaM04qp...pdf`  
  - `http://127.0.0.1:8000/materials/courses/1/dK8z9xLaM04qp...pdf`
- **Hasil Aktual:**  
  HTTP Response Code: **404 Not Found**.
- **Penyebab Mengapa Gagal Diakses Langsung:**  
  1. Direktori akar publik (Document Root) web server (Nginx/Apache/PHP Built-in Server) dikunci hanya pada folder `public/`.
  2. Direktori `storage/app/private` berada di luar folder `public/` dan tidak memiliki symbolic link ke folder publik.
  3. Web server menolak melayani berkas fisik di luar document root secara langsung. Satu-satunya pintu masuk adalah melalui alur kode aplikasi: Route -> Controller -> Otorisasi Gate/Policy -> `Storage::disk('local')->download()`.

---

### 4. Perbedaan Mendalam Disk `local` vs Disk `public` pada `config/filesystems.php`
Berdasarkan deklarasi konfigurasi pada [`config/filesystems.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/config/filesystems.php#L31-L49):

| Aspek | Disk `local` (Privat) | Disk `public` (Publik) |
|---|---|---|
| **Root Folder Fisik** | `storage/app/private` | `storage/app/public` |
| **Simbolik Link** | Tidak ada. Terisolasi dari web server. | Ditautkan ke `public/storage` via `storage:link`. |
| **Karakteristik Akses** | Wajib melewati siklus request Laravel (Route + Controller + Middleware + Policy). | Dapat diunduh siapa saja secara langsung via web server statis tanpa login. |
| **URL Publik** | Tidak memiliki URL langsung (`url => null`). | Memiliki URL publik langsung (`APP_URL/storage/...`). |
| **Visibilitas Default** | `private` | `public` |
| **Peruntukan di KampusLMS** | Materi perkuliahan dosen, berkas pengumpulan tugas mahasiswa (submissions), laporan audit. | Aset statis publik yang tidak sensitif: foto profil pengguna, logo universitas, favicon. |

---

### 5. Analisis Perintah `php artisan storage:link`
- **Apa yang Dibuat oleh Perintah Ini?**  
  Perintah `php artisan storage:link` membuat sebuah symbolic link (tautan simbolik / junction folder) dari direktori publik ke direktori storage:
  ```
  public/storage  --->  storage/app/public
  ```
- **Mengapa Materi Kuliah dan Berkas Tugas TIDAK BOLEH Berada di Sana?**  
  1. **Bypass Autentikasi & Otorisasi Total:** Berkas yang berada di `storage/app/public` dilayani langsung oleh web server (Nginx/Apache/Caddy) sebagai aset statis murni. Request tidak menyentuh PHP maupun Laravel, sehingga middleware `auth`, session cookie, dan `Gate::authorize()` tidak pernah dieksekusi.
  2. **Kebocoran Data Seluruh Angkatan:** Jika berkas tugas ditaruh di `storage/app/public`, siapa pun yang mengetahui atau menebak format URL (misalnya dari link yang dibagikan satu mahasiswa di grup percakapan) dapat langsung mengunduh tugas milik teman sekelasnya dari jendela penyamaran (incognito) tanpa login.
  3. **Integritas Penilaian Rusak:** Menempatkan materi berbayar/tertutup atau submission tugas di disk publik melanggar prinsip kerahasiaan (confidentiality) pada standar keamanan perangkat lunak akademik.

---

## BREAK — Delapan Kerusakan 

Eksperimen delapan skenario kerusakan penanganan berkas dan upload dilakukan untuk menguji ketahanan proteksi data pada KampusLMS:

---

### 1. Pindahkan Berkas Submission ke Disk Publik (`storage/app/public`)
- **Prediksi Sebelum Mencoba:**  
  Jika berkas submission disimpan di disk `public` dan diakses melalui URL `/storage/...`, berkas dapat diunduh langsung tanpa login, tanpa cookie sesi, dan tanpa pemeriksaan otorisasi Policy.
- **Skenario / Baris Kode yang Diuji:**  
  Mengubah target penyimpanan submission pada [`SubmissionController.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/Mahasiswa/SubmissionController.php#L109):
  ```php
  // Skenario Rusak: Menyimpan submission di disk publik
  $path = $file->store("submissions/{$assignment->id}", 'public');
  ```
- **Langkah Pengujian:**  
  1. Mahasiswa A mengumpulkan tugas tugas-akhir.pdf.
  2. Berkas tersimpan di `storage/app/public/submissions/1/contoh_hash.pdf`.
  3. Buka jendela peramban penyamaran (Incognito Window / `curl`) tanpa login sama sekali:
     ```bash
     curl -i http://127.0.0.1:8000/storage/submissions/1/contoh_hash.pdf
     ```
- **Hasil Aktual & Kode Status HTTP:**  
  - Status HTTP: **200 OK**.
  - Header HTTP: `Content-Type: application/pdf`, `Server: nginx / php`.
  - Berkas tugas mahasiswa berhasil diunduh secara bebas oleh publik tanpa autentikasi.
- **Risiko Keamanan & Konsep Modul:**  
  Tugas seluruh kelas terbuka untuk publik. Ini adalah kebocoran data akademik fatal. Mahasiswa lain dapat mencuri jawaban tugas sebelum deadline, atau pihak luar universitas dapat mengakses data karya intelektual mahasiswa.
- **Kondisi Setelah Dikembalikan:**  
  Penyimpanan submission dikembalikan menggunakan disk `'local'` (`storage/app/private`).

---

### 2. Hapus `Gate::authorize()` dari Endpoint Download
- **Prediksi Sebelum Mencoba:**  
  Jika `Gate::authorize()` pada controller unduh dihapus, pengguna yang sudah login (misal Mahasiswa A) dapat mengunduh berkas tugas mahasiswa lain (Mahasiswa B) hanya dengan mengubah ID submission pada parameter URL rute unduh.
- **Skenario / Baris Kode yang Diuji:**  
  Menghapus baris otorisasi pada [`SubmissionController.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/Mahasiswa/SubmissionController.php#L232):
  ```php
  public function download(Submission $submission)
  {
      // Gate::authorize('download', $submission); // DIHAPUS SEMENTARA
      abort_unless(Storage::disk('local')->exists($submission->file_path), 404);
      return Storage::disk('local')->download($submission->file_path, $submission->original_name);
  }
  ```
- **Langkah Pengujian:**  
  1. Login sebagai Mahasiswa A (ID: 3).
  2. Submission target ID: 15 (milik Mahasiswa B, ID: 4).
  3. Kirim request unduh via cURL menggunakan cookie Mahasiswa A:
     ```bash
     curl -i -b cookie_mhs_a.txt http://127.0.0.1:8000/mahasiswa/submissions/15/download
     ```
- **Hasil Aktual & Kode Status HTTP:**  
  - Status HTTP: **200 OK** (Bukan 403 Forbidden).
  - Header `Content-Disposition: attachment; filename="Tugas_Mahasiswa_B.pdf"`.
  - Berkas milik Mahasiswa B terkirim utuh ke browser Mahasiswa A.
- **Risiko Keamanan & Konsep Modul:**  
  IDOR (Insecure Direct Object Reference) Versi Berkas. Meskipun berkas fisik disimpan di folder privat, ketiadaan otorisasi di controller membuat controller tersebut bertindak sebagai proxy pembocor berkas. Penyerang cukup melakukan manipulasi penambahan ID (/submissions/1/download, /submissions/2/download, dan seterusnya) untuk mengunduh seluruh berkas tugas mahasiswa lain.
- **Kondisi Setelah Dikembalikan:**  
  Baris `Gate::authorize('download', $submission);` dipulihkan utuh di baris 232 [`SubmissionController.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/Mahasiswa/SubmissionController.php#L232).

---

### 3. Simpan Berkas dengan `storeAs()` Menggunakan Nama Asli (`../../test.txt`)
- **Prediksi Sebelum Mencoba:**  
  Jika sistem mempercayai nama berkas masukan pengguna dan menyimpannya menggunakan `$file->storeAs($dir, $file->getClientOriginalName())`, penyerang dapat menyuntikkan karakter navigasi direktori (`../`) untuk keluar dari folder tujuan (Path Traversal).
- **Skenario / Baris Kode yang Diuji:**  
  Menggunakan nama berkas mentah:
  ```php
  // Skenario Rusak: Mempercayai getClientOriginalName()
  $path = $file->storeAs("submissions/{$assignment->id}", $file->getClientOriginalName(), 'local');
  ```
- **Langkah Pengujian:**  
  1. Unggah berkas yang dimanipulasi nama aslinya menggunakan cURL:
     ```bash
     curl -i -b cookie_mhs.txt -X POST http://127.0.0.1:8000/mahasiswa/assignments/1/submissions \
       -F "file=@payload.txt;filename=../../evil.txt" \
       -F "_token=<CSRF>"
     ```
  2. Periksa lokasi berkas yang tercipta di storage.
- **Hasil Aktual & Kode Status HTTP:**  
  - Pada lingkungan tanpa sanitasi ketat, berkas tidak mendarat di `storage/app/private/submissions/1/evil.txt`, melainkan melompat keluar ke direktori `storage/app/private/evil.txt` atau bahkan menimpa berkas kritis sistem jika path diisi `../../../../.env`.
- **Risiko Keamanan & Konsep Modul:**  
  Kerentanan Arbitrary File Write / Path Traversal (CWE-22). Nama berkas masukan klien adalah masukan yang tidak terpercaya (untrusted input). Penyerang dapat menimpa file konfigurasi server, mengunggah script ke direktori publik, atau merusak struktur sistem.  
  Solusinya: Selalu gunakan `$file->store()` (yang menghasilkan hash nama acak) dan simpan nama asli hanya di kolom database `original_name`.
- **Kondisi Setelah Dikembalikan:**  
  Metode penyimpanan menggunakan `$file->store("submissions/{$assignment->id}", 'local')` yang menghasilkan hash acak aman.

---

### 4. Ekstensi Palsu: Unggah Berkas PHP Bernama `.pdf` (`extensions` vs `mimes`)
- **Prediksi Sebelum Mencoba:**  
  Aturan validasi `extensions:pdf` hanya memeriksa string ekstensi di ujung nama file (mudah dikelabui). Sebaliknya, aturan `mimes:pdf` membaca magic bytes (tanda tangan biner isi berkas). Berkas PHP berekstensi `.pdf` akan lolos pada `extensions:pdf` tetapi akan ditolak mentah-mentah oleh `mimes:pdf`.
- **Skenario / Baris Kode yang Diuji:**  
  Membuat berkas `backdoor.php` berisi:
  ```php
  <?php system($_GET['cmd']); ?>
  ```
  Lalu mengubah namanya menjadi `backdoor.pdf`.  
  Uji dengan dua aturan validasi:
  - Aturan A: `'file' => ['required', 'file', 'extensions:pdf']`
  - Aturan B: `'file' => ['required', 'file', 'mimes:pdf']`
- **Langkah Pengujian:**  
  1. Jalankan pengujian upload dengan Aturan A (`extensions:pdf`).
  2. Jalankan pengujian upload dengan Aturan B (`mimes:pdf`).
- **Hasil Aktual & Kode Status HTTP:**  
  - Pada Aturan A (`extensions:pdf`): HTTP 302 Redirect / Lolos Validasi. Berkas PHP berhasil tersimpan di server karena ekstensinya memang string `pdf`.
  - Pada Aturan B (`mimes:pdf`): HTTP 302 Redirect dengan Error Validasi. Laravel membaca MIME type berkas melalui `finfo` (magic bytes) dan mendeteksinya sebagai `text/x-php` / `text/plain`, sehingga melempar pesan error: "Format berkas harus PDF, DOC, atau DOCX."
- **Risiko Keamanan & Konsep Modul:**  
  Validasi berbasis ekstensi saja sangat rapuh dan membuka celah Remote Code Execution (RCE) atau Stored XSS. Validasi wajib menggunakan `mimes:pdf,doc,docx` karena membaca isi struktur data biner berkas secara riil.
- **Kondisi Setelah Dikembalikan:**  
  Aturan validasi tetap menggunakan `mimes:pdf,doc,docx` di [`SubmissionController.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/Mahasiswa/SubmissionController.php#L98) dan [`StoreMaterialRequest.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Requests/StoreMaterialRequest.php#L37).

---

### 5. Salah Tafsir Satuan Ukuran: Ubah `max:5120` Menjadi `max:5`
- **Prediksi Sebelum Mencoba:**  
  Parameter aturan validasi `max` pada berkas di Laravel menggunakan satuan Kilobyte (KB), bukan Megabyte (MB). Jika pengembang bermaksud memberi batas 5 MB tetapi menuliskan `max:5`, maka batas maksimal riilnya adalah 5 KB (5.120 bytes). Akibatnya berkas berukuran normal 1 MB akan tertolak.
- **Skenario / Baris Kode yang Diuji:**  
  Mengubah aturan validasi:
  ```php
  // Salah Tafsir: Niat 5 MB, tapi tertulis 5 KB
  'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5'],
  ```
- **Langkah Pengujian:**  
  Unggah dokumen PDF tugas berukuran wajar: 1 MB (1.024 KB).
- **Hasil Aktual & Kode Status HTTP:**  
  - Status HTTP: **302 Found** (Kembali ke halaman form dengan validation error).
  - Pesan Error: "The file field must not be greater than 5 kilobytes."
  - Berkas 1 MB gagal diunggah padahal jauh di bawah ekspektasi 5 MB.
- **Risiko Keamanan & Konsep Modul:**  
  Menyebabkan kegagalan fungsional aplikasi (Denial of Service bagi pengguna sah). Mahasiswa tidak akan pernah bisa mengumpulkan tugas karena ukuran berkas PDF biasa umumnya di atas 50 KB.  
  Rumus konversi:  
  - 1 MB = `max:1024`  
  - 5 MB = `max:5120`  
  - 20 MB = `max:20480`
- **Kondisi Setelah Dikembalikan:**  
  Nilai validasi dipulihkan ke `max:5120` (5 MB) untuk submission dan `max:20480` (20 MB) untuk materi dosen.

---

### 6. Batas PHP Server Mengalahkan Validasi Laravel (`upload_max_filesize=1M`)
- **Prediksi Sebelum Mencoba:**  
  Jika konfigurasi `php.ini` server menetapkan `upload_max_filesize = 1M` dan `post_max_size = 2M`, maka unggahan berkas berukuran 3 MB akan dibatalkan langsung oleh web server / runtime PHP sebelum siklus hidup Laravel dimulai. Array `$_FILES` akan kosong atau memuat `UPLOAD_ERR_INI_SIZE`, sehingga validasi Laravel `max:5120` tidak sempat dieksekusi normal.
- **Skenario / Baris Kode yang Diuji:**  
  Mengatur parameter PHP runtime pada environment:
  - `upload_max_filesize = 1M`
  - Validasi Laravel: `'file' => ['required', 'file', 'max:5120']` (5 MB)
- **Langkah Pengujian:**  
  Unggah berkas tugas berukuran 3 MB.
- **Hasil Aktual & Kode Status HTTP:**  
  - Request masuk ke server, namun PHP langsung membuang isi stream berkas.
  - Laravel menerima request dengan field `file` bernilai `null` (karena PHP mendrop payload).
  - Pesan error yang muncul bukan "Ukuran berkas maksimal 5 MB", melainkan pesan membingungkan: "Berkas tugas wajib diunggah." (karena dianggap tidak ada berkas yang dikirim). Jika melebihi `post_max_size`, request bahkan bisa menghasilkan HTTP 419 Page Expired atau error kosong.
- **Risiko Keamanan & Konsep Modul:**  
  Batas server mengalahkan validasi aplikasi. Di lingkungan produksi (Minggu 12), pengaturan `php.ini` wajib diselaraskan dengan aturan validasi aplikasi:
  ```ini
  upload_max_filesize = 25M
  post_max_size = 30M
  memory_limit = 256M
  ```
- **Kondisi Setelah Dikembalikan:**  
  Pengaturan server dan dokumentasi deployment mencatat keharusan konfigurasi `upload_max_filesize` di atas batas validasi aplikasi.

---

### 7. Hapus `updateOrCreate`, Ganti dengan `create` (Uji Unique Composite Constraint)
- **Prediksi Sebelum Mencoba:**  
  Jika `Submission::updateOrCreate()` diganti menjadi `Submission::create()`, ketika mahasiswa mengumpulkan tugas untuk kedua kalinya pada penugasan yang sama, database engine akan menolak operasi tersebut dan melempar `QueryException` karena pelanggaran kunci unik gabungan `UNIQUE(assignment_id, user_id)`.
- **Skenario / Baris Kode yang Diuji:**  
  Mengubah baris penyimpanan di [`SubmissionController.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/Mahasiswa/SubmissionController.php#L119):
  ```php
  // Skenario Rusak: Menumpuk baris baru tanpa updateOrCreate
  Submission::create([
      'assignment_id' => $assignment->id,
      'user_id'       => auth()->id(),
      'file_path'     => $path,
      'original_name' => $file->getClientOriginalName(),
      'file_size'     => $file->getSize(),
      'submitted_at'  => now(),
      'is_late'       => now()->gt($assignment->due_at),
  ]);
  ```
- **Langkah Pengujian:**  
  1. Mahasiswa A mengumpulkan tugas pertama kali (berhasil).
  2. Mahasiswa A mengumpulkan tugas revisi kedua kali pada tugas yang sama.
- **Hasil Aktual & Kode Status HTTP:**  
  - Status HTTP: **500 Internal Server Error** (pada mode debug) atau halaman error server.
  - Pesan Database: `SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '1-3' for key 'submissions_assignment_id_user_id_unique'`.
- **Risiko Keamanan & Konsep Modul:**  
  Konstrain unik komposit yang dirancang pada skema basis data Minggu 3 terbukti menjadi penyelamat integritas data. Namun, di level aplikasi, pengembang wajib menggunakan `updateOrCreate()` agar proses revisi tugas berjalan mulus dengan menimpa record lama dan membersihkan berkas fisik sebelumnya, bukan memicu crash database.
- **Kondisi Setelah Dikembalikan:**  
  Pemanggilan `Submission::updateOrCreate(['assignment_id' => ..., 'user_id' => ...], [...])` dipulihkan utuh pada baris 119–129 [`SubmissionController.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/Mahasiswa/SubmissionController.php#L119-L129).

---

### 8. Hapus Record Submission via SQL Langsung (Analisis Berkas Yatim / Orphaned Files)
- **Prediksi Sebelum Mencoba:**  
  Jika record submission dihapus langsung dari database lewat query SQL (`DELETE FROM submissions WHERE id = ...`) atau lewat relasi `cascadeOnDelete` database, berkas fisik di `storage/app/private` tidak akan terhapus dan tertinggal sebagai berkas yatim (orphaned file).
- **Skenario / Baris Kode yang Diuji:**  
  Menghapus data langsung dari basis data tanpa melalui model Eloquent:
  ```sql
  DELETE FROM submissions WHERE id = 10;
  ```
- **Langkah Pengujian:**  
  1. Catat nilai `file_path` dari submission ID 10: `submissions/2/random_hash_abc.pdf`.
  2. Pastikan berkas fisik ada di `storage/app/private/submissions/2/random_hash_abc.pdf`.
  3. Eksekusi query penghapusan SQL langsung di database.
  4. Periksa kembali isi folder `storage/app/private/submissions/2/`.
- **Hasil Aktual & Kode Status HTTP:**  
  - Baris di tabel `submissions` terhapus seketika.
  - Berkas fisik `random_hash_abc.pdf` masih tinggal utuh di disk.
- **Risiko Keamanan & Konsep Modul:**  
  Event model Eloquent `deleting` yang didefinisikan pada [`Submission::booted()`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Models/Submission.php#L23-L28):
  ```php
  protected static function booted(): void
  {
      static::deleting(function (Submission $submission) {
          Storage::disk('local')->delete($submission->file_path);
      });
  }
  ```
  Hanya terpicu jika penghapusan dilakukan melalui Eloquent Model (misal `$submission->delete()`).  
  Ketika record dihapus via SQL mentah, query builder massal (`Submission::where(...)->delete()`), atau `cascadeOnDelete` level database:
  1. Event PHP Eloquent tidak pernah terpanggil.
  2. Storage server akan dipenuhi berkas yatim yang menghabiskan kuota kapasitas server (disk bloat).
  3. Berkas rahasia tetap berada di penyimpanan fisik server tanpa pemilik yang jelas.
  4. Solusi Arsitektur: Saat menghapus data induk (seperti menghapus Mata Kuliah atau Tugas), controller wajib melakukan iterasi dan menghapus berkas fisik secara eksplisit sebelum memanggil instruksi delete basis data.
- **Kondisi Setelah Dikembalikan:**  
  Siklus penghapusan berkas fisik dijamin melalui model hook Eloquent dan pembersihan eksplisit pada controller penghapusan.

---

## 3. KESIMPULAN MINGGU 9

Berdasarkan investigasi mendalam pada tahap READ dan pembuktian empiris delapan skenario BREAK, disimpulkan prinsip-prinsip utama tata kelola berkas web pada Laravel 12:

1. **Pemisahan Tegas Disk Publik vs Privat:**
   - Direktori `storage/app/public` (yang terhubung via `storage:link`) hanya boleh digunakan untuk aset umum non-sensitif (foto profil, logo).
   - Seluruh materi perkuliahan dan pengumpulan tugas mahasiswa wajib disimpan di disk privat (`storage/app/private`). Menempatkannya di disk publik mengekspos berkas ke seluruh internet tanpa login dan tanpa otorisasi.

2. **Otorisasi Unduhan di Sisi Server (Policy Enforcement):**
   - Berkas privat wajib dialirkan (streamed) melalui method controller menggunakan `Storage::disk('local')->download()`.
   - Setiap endpoint unduhan wajib memanggil `Gate::authorize()` untuk memvalidasi kepemilikan. Ketiadaan Policy di controller unduhan membuka celah fatal IDOR versi berkas.

3. **Integritas Penamaan & Validasi Magic Bytes:**
   - Nama berkas masukan pengguna adalah masukan yang tidak dipercaya (untrusted). Penggunaan `$file->store()` menjamin berkas fisik tersimpan dengan nama hash acak, mencegah serangan Path Traversal dan tebakan URL. Nama asli hanya disimpan di basis data untuk kebutuhan presentasi antarmuka.
   - Validasi berkas wajib mengandalkan `mimes` (bukan hanya `extensions`) untuk memeriksa tanda tangan biner (magic bytes), menggagalkan manipulasi ekstensi palsu berkas berbahaya (malicious script upload).
   - Pengembang harus cermat terhadap satuan validasi ukuran berkas Laravel yang menggunakan Kilobyte (KB) serta memastikan pengaturan batas server PHP (`upload_max_filesize` dan `post_max_size`) tidak lebih rendah dari batas validasi aplikasi.

4. **Siklus Hidup Berkas & Pencegahan Berkas Yatim:**
   - Penerapan `updateOrCreate()` dengan konstrain unik komposit `(assignment_id, user_id)` menjamin keteraturan revisi pengumpulan tugas dan mencegah duplikasi data.
   - Penghapusan berkas fisik wajib diproteksi melalui Eloquent model hook `deleting` serta pembersihan eksplisit pada controller sebelum terjadinya operasi cascade delete di tingkat basis data.
