# Dokumentasi Optimisasi Performa KampusLMS (Minggu 11)

**Kelompok:** 01  
**Target:** Mengukur performa KampusLMS, mengeliminasi N+1 query, optimisasi alokasi memori, dan mendokumentasikan perbandingan angka Sebelum vs Sesudah.

---

## 1. Tabel Komparasi Pengukuran: Sebelum vs Sesudah

| Halaman | URL / Peran | Query (Sebelum) | Waktu (Sebelum) | Memori (Sebelum) | Query (Sesudah) | Waktu (Sesudah) | Memori (Sesudah) | Status |
| :--- | :--- | :---:| :---:| :---:| :---:| :---:| :---:| :---:|
| **Rekap Nilai Dosen** | `/dosen/courses/1/grades` | 9 queries | 954 ms | 4 MB | **10 queries** | **91.19 ms** | **3 MB** | ✅ **Selesai (Build #1, #2, #8)** |
| **Dashboard Dosen** | `/dashboard` (Dosen) | 11 queries | 225 ms | 4 MB | *(oleh tim)* | *(oleh tim)* | *(oleh tim)* | ⏳ Dalam pengerjaan tim |
| **Dashboard Mahasiswa** | `/dashboard` (Mahasiswa) | 26 queries | 163 ms | 4 MB | *(oleh tim)* | *(oleh tim)* | *(oleh tim)* | ⏳ Dalam pengerjaan tim |
| **Dashboard Admin** | `/dashboard` (Admin) | 16 queries | 666 ms | 4 MB | **7 queries** | **~52 ms** | **3 MB** | ✅ **Selesai (Build #6)** |
| **Daftar Mata Kuliah** | `/dosen/courses` | 7 queries | 1.02 s | 4 MB | *(oleh tim)* | *(oleh tim)* | *(oleh tim)* | ⏳ Dalam pengerjaan tim |
| **Detail Mata Kuliah** | `/dosen/courses/1` | 11 queries | 1.81 s | 5 MB | *(oleh tim)* | *(oleh tim)* | *(oleh tim)* | ⏳ Dalam pengerjaan tim |
| **Daftar Pengumpulan Tugas**| `/dosen/assignments/1/submissions` | 10 queries | 1.49 s | 4 MB | **11 queries** | **160 ms** | **3 MB** | ✅ **Selesai (Build #5)** |

---

## 2. Implementasi Pencegahan N+1 Otomatis (Build #2)

Fitur `Model::preventLazyLoading()` telah diaktifkan secara global di `app/Providers/AppServiceProvider.php`:
```php
Model::preventLazyLoading(! app()->isProduction());
```
- **Lingkungan Lokal/Testing:** Melempar `LazyLoadingViolationException` seketika jika terdapat query relasi yang diakses tanpa eager loading (`with()`).
- **Lingkungan Produksi:** Otomatis dinonaktifkan demi stabilitas pengguna.
- **Hasil Verifikasi:** Halaman Rekap Nilai Dosen berjalan lancar tanpa melanggar aturan lazy loading.

---

## 3. Catatan Optimisasi Rekap Nilai Dosen (Poin 8 Modul)

1. **Raw Database Aggregate:** Perhitungan statistik kelas (`avg`, `max`, `min`, `graded_count`) dipindahkan dari nested-loop PHP in-memory ke database aggregate query (`AVG()`, `MAX()`, `MIN()`).
   - Dampak: Mengurangi beban CPU dan memangkas penggunaan RAM dari 4 MB menjadi 3 MB.
2. **Specific Column Selection:** Menghindari `SELECT *` dengan hanya mengambil kolom yang dibutuhkan view Blade (`id`, `name`, `nim_nip`, `email`, `title`, `max_score`).
3. **Target Kueri:** Halaman berhasil stabil di **10 queries**, memenuhi target modul yaitu **di bawah 15 query**.

---

## 4. Perancangan Migrasi Index Tambahan (Build #4)

Berkas migrasi tambahan: `database/migrations/2026_11_02_000001_add_performance_indexes_to_tables.php`

| Tabel | Nama Index | Kolom Index | Query yang Diuntungkan | Analisis Biaya Tulis (Write Cost) |
| :--- | :--- | :--- | :--- | :--- |
| `courses` | `idx_courses_lecturer_status` | `['lecturer_id', 'status']` | `Course::where('lecturer_id', ...)->where('status', 'active')` (Dashboard Dosen & Filter MK) | Sedikit overhead saat admin membuat mata kuliah atau mengubah status kursus. |
| `courses` | `idx_courses_status` | `'status'` | `Course::where('status', 'active')->count()` (Dashboard Admin) & filter katalog kursus | Overhead minor saat pembaruan status kursus. |
| `assignments` | `idx_assignments_course_status` | `['course_id', 'status']` | `Assignment::whereIn('course_id', ...)->where('status', 'published')` (Dashboard Mahasiswa & counter tugas terbit) | Overhead saat dosen membuat/mempublikasikan tugas. |
| `submissions` | `idx_submissions_user_id` | `'user_id'` | `Submission::where('user_id', ...)` (Dashboard Mahasiswa `total_submitted`, `mySubmissions`, & riwayat mahasiswa) | Overhead saat mahasiswa submit tugas. Menghilangkan **Full Table Scan (type: ALL)** karena index awal hanya komposit `[assignment_id, user_id]`. |
| `submissions` | `idx_submissions_assignment_is_late` | `['assignment_id', 'is_late']` | Filter keterlambatan pengumpulan di halaman dosen (`where('assignment_id', ...)->where('is_late', true)`) | Overhead minor saat insert pengumpulan. |
| `grades` | `idx_grades_graded_at` | `'graded_at'` | `Grade::latest('graded_at')->take(5)` (Dashboard Mahasiswa - nilai terbaru) | Menghilangkan *filesort* (in-memory sort) saat pengurutan riwayat nilai. |

### Catatan Index yang Sengaja Tidak Ditambahkan (Sesuai Ketentuan Modul 11.2 Pack C):
1. `users.name` / `courses.name`: Menggunakan pencarian wildcard `LIKE '%keyword%'` yang tidak dapat menggunakan indeks B-Tree standar.
2. `materials.description` / `assignments.instructions`: Kolom `TEXT` panjang yang tidak efisien diindeks tanpa full-text search engine.
3. `submissions.is_late` (berdiri sendiri): Kolom boolean dengan kardinalitas sangat rendah (0 atau 1) tidak efektif jika diindeks mandiri; digabungkan dalam komposit bersama `assignment_id`.

---

## 5. Penerapan Pagination di Seluruh Halaman Daftar (Build #5)

Sesuai ketentuan Poin 5 modul Minggu 11: **"Pagination di seluruh halaman daftar, tidak ada yang memuat seluruh tabel."**

### Analisis & Audit Halaman Daftar
Sebelum optimisasi ini diterapkan, sebagian besar halaman daftar utama (Daftar Mata Kuliah, Daftar Tugas, Daftar Materi, Daftar Pengguna, dan Pusat Notifikasi) sudah memiliki pagination dasar. Namun terdapat halaman penting yang masih melanggar:
1. **Daftar Pengumpulan Tugas Dosen (`/dosen/assignments/{assignment}/submissions`):**
   - Mengambil seluruh mahasiswa terdaftar via `students()->orderBy('name')->get()` dan seluruh submission via `submissions()->get()`.
   - Menggabungkan dan memfilter status di memori PHP (in-memory Collection).
   - Seluruh baris di-render langsung ke Blade tanpa kontrol paginasi (`{{ $studentList->links() }}`).
   - **Risiko:** Menimbulkan lonjakan RAM (*Memory Leak / Out of Memory*) dan response time membengkak saat kelas memiliki ratusan mahasiswa (sesuai skenario pengujian BREAK #5 modul).

2. **Daftar Submission Mahasiswa (`/mahasiswa/assignments/{assignment}/submissions`):**
   - Masih menggunakan pemanggilan `->get()`.

3. **Komponen Enrollment (`EnrollmentManager` & `EnrollmentController`):**
   - Terdapat pemanggilan `User::where('role', 'mahasiswa')->...->get()` yang tidak dipakai dan widget yang memuat seluruh data sebelum di-`take(10)` di memori.

### Solusi & Implementasi Optimisasi (Poin 5):
1. **Paginasi Database di `Dosen\SubmissionController@index`:**
   - Mengganti `->get()` dengan pagination database: `->paginate(15)->withQueryString()`.
   - Menghitung agregat ringkasan statistik (`total`, `submitted`, `unsubmitted`, `late`) langsung di engine database menggunakan query `count()` terindeks tanpa memuat row ke memori PHP.
   - Memindahkan pemfilteran tab status (`?status=submitted|unsubmitted|late`) ke query builder menggunakan `whereHas()` dan `whereDoesntHave()`.
   - Menerapkan eager loading terarah (`submissions` hanya untuk tugas terkait beserta relasi `grade`), menjaga aplikasi bebas dari pelanggaran lazy loading (`Model::preventLazyLoading()`).
   - Mentransformasikan item menggunakan `$paginator->through()`, mempertahankan fungsionalitas dan metadata pagination Laravel.

2. **Navigasi Paginasi di View Blade (`resources/views/submissions/index.blade.php`):**
   - Menambahkan navigasi link `{{ $studentList->links() }}` di bawah daftar kartu mahasiswa pada tampilan dosen.
   - Navigasi halaman membawa query string parameter aktif secara konsisten (`withQueryString()`).

3. **Konsistensi Paginasi Pengumpulan Mahasiswa (`Mahasiswa\SubmissionController@index`):**
   - Mengubah query menjadi `->paginate(15)` sehingga konsisten dengan view yang sudah menyediakan wadah `links()`.

4. **Eliminasi Full-Table Load pada Modul Enrollment:**
   - Membatasi query pengambilan data pada `EnrollmentManager` di level SQL menggunakan `take(10)->get()`.
   - Menghapus query `availableStudents` yang tidak terpakai di `Admin\EnrollmentController@index` dan `Dosen\EnrollmentController@index`.

### Hasil Verifikasi & Dampak:
- **Penggunaan Memori:** Terpangkas dari 4 MB menjadi **3 MB** pada halaman rekap pengumpulan.
- **Waktu Eksekusi:** Terpangkas dari 1.49 s menjadi **~160 ms**.
- **Integritas Sistem:** Seluruh 55 pengujian otomatis (*automated feature tests*) berhasil lolos (**PASS**) tanpa ada regresi fitur.
- **Skalabilitas:** Tidak ada lagi endpoint halaman daftar yang memuat seluruh tabel ke memori server.

---

## 6. Strategi Caching Dashboard & Pembersihan Otomatis (Build #6)

Sesuai ketentuan Poin 6 modul Minggu 11: **"Cache pada minimal satu bagian dashboard, lengkap dengan pembersihan otomatis saat data berubah — dan penjelasan di `docs/performa.md` tentang bagian mana yang sengaja tidak di-cache dan mengapa."**

### A. Implementasi Caching pada Dashboard Admin
Pada `app/Http/Controllers/DashboardController.php`, method `adminDashboard()` sebelumnya mengeksekusi 9 query agregat `COUNT()` terpisah setiap kali halaman dimuat:
- `User::count()`, `User::where('role', 'admin')->count()`, `User::where('role', 'dosen')->count()`, `User::where('role', 'mahasiswa')->count()`
- `Course::count()`, `Course::where('status', 'active')->count()`
- `Material::count()`, `Assignment::count()`, `Submission::count()`

Sembilan query ini dibungkus menggunakan facade cache dengan durasi kedaluwarsa 30 menit:
```php
$stats = Cache::remember('admin.dashboard.stats', now()->addMinutes(30), function () {
    return [
        'total_users'       => User::count(),
        'total_admin'       => User::where('role', 'admin')->count(),
        'total_dosen'       => User::where('role', 'dosen')->count(),
        'total_mahasiswa'   => User::where('role', 'mahasiswa')->count(),
        'total_courses'     => Course::count(),
        'active_courses'    => Course::where('status', 'active')->count(),
        'total_materials'   => Material::count(),
        'total_assignments' => Assignment::count(),
        'total_submissions' => Submission::count(),
    ];
});
```

### B. Mekanisme Pembersihan Otomatis (Cache Invalidation)
Agar statistik admin tidak basi secara permanen saat terjadi perubahan data pada sistem, diterapkan hook event Eloquent di dalam method `booted()` pada masing-masing model sumber:
- **`App\Models\User`:** Memicu `Cache::forget('admin.dashboard.stats')` saat event `saved`, `deleted`, dan `restored`.
- **`App\Models\Course`:** Memicu `Cache::forget('admin.dashboard.stats')` saat event `saved` dan `deleted`.
- **`App\Models\Material`:** Memicu `Cache::forget('admin.dashboard.stats')` saat event `saved` dan `deleted`.
- **`App\Models\Assignment`:** Memicu `Cache::forget('admin.dashboard.stats')` saat event `saved` dan `deleted`.
- **`App\Models\Submission`:** Memicu `Cache::forget('admin.dashboard.stats')` saat event `saved` dan `deleted`.

Dengan strategi ini, cache hanya bertahan selama data stabil, dan akan seketika di-*flush* saat ada penambahan, pembaruan status, atau penghapusan data.

### C. Bagian yang Sengaja TIDAK Di-cache dan Alasannya (Ketentuan Modul 11 & Checkpoint 11.4)
Sesuai prinsip modul: *"Yang harus Anda pikirkan sebelum memakai cache: kapan data ini menjadi basi, dan apa akibatnya?"* dan evaluasi Break #7, bagian-bagian berikut sengaja **TIDAK** di-cache:
1. **Nilai Mahasiswa (`Grade`) & Riwayat Nilai (`recentGrades` di Dashboard Mahasiswa):**
   - *Alasan:* Nilai adalah data akademik yang sensitif dan memiliki konsekuensi langsung bagi mahasiswa. Meng-cache nilai bahkan untuk 10 menit saja dapat merugikan mahasiswa yang baru saja diperbaiki nilainya oleh dosen namun masih melihat nilai lama (sesuai skenario BREAK #7).
2. **Status Pengumpulan Tugas Mahasiswa (`mySubmissions` di Dashboard Mahasiswa):**
   - *Alasan:* Setelah mahasiswa mengunggah tugas, mereka membutuhkan konfirmasi visual seketika bahwa status tugas telah berubah menjadi "Sudah Dikumpulkan". Jika data ini di-cache, mahasiswa akan panik mengira pengumpulan tugasnya gagal.
3. **Daftar Tugas yang Memerlukan Penilaian (`pendingSubmissions` di Dashboard Dosen):**
   - *Alasan:* Merupakan daftar tugas tindakan (*action items*) bagi dosen. Begitu mahasiswa mengumpulkan tugas, antrean ini harus langsung memuat berkas baru tanpa menunggu waktu kedaluwarsa cache.

### D. Hasil Pengukuran Sebelum vs Sesudah:
- **Jumlah Query:** Turun dari **16 queries** menjadi **7 queries** (pemangkasan ~56% beban query database).
- **Waktu Eksekusi:** Terpangkas dari 666 ms menjadi **~52 ms**.
- **Penggunaan Memori:** Stabil di **3 MB**.

---

## 7. Eliminasi Pemanggilan `env()` di Luar `config/` (Build #7)

Sesuai ketentuan Poin 7 modul Minggu 11: **"Seluruh pemanggilan `env()` di luar `config/` dihapus, diganti `config()`."**

### A. Bahaya Pemanggilan `env()` di Luar `config/`
Saat perintah optimasi produksi `php artisan config:cache` dijalankan:
1. Seluruh konfigurasi di folder `config/*.php` digabungkan menjadi satu file statis di `bootstrap/cache/config.php`.
2. Laravel **berhenti memuat dan mengurai file `.env`**.
3. Pemanggilan fungsi `env('KEY')` di luar folder `config/` akan **selalu mengembalikan `null`**, yang menyebabkan kegagalan autentikasi, hilangnya koneksi, atau error deployment fatal yang sulit dilacak (persiapan penting sebelum deployment VPS Minggu 12).

### B. Audit dan Perbaikan Codebase
- **Audit:** Dilakukan pemindaian terhadap seluruh direktori proyek (`app/`, `database/`, `resources/`, `routes/`, `bootstrap/`).
- **Temuan:** Ditemukan pemanggilan `env('DEMO_PASSWORD', 'password')` pada `database/seeders/DemoAccountSeeder.php:27`.
- **Perbaikan:**
  1. Menambahkan kunci konfigurasi terpusat pada `config/auth.php`:
     ```php
     'demo_password' => env('DEMO_PASSWORD', 'password'),
     ```
  2. Mengganti pemanggilan di `DemoAccountSeeder.php` menjadi:
     ```php
     $password = config('auth.demo_password', 'password');
     ```
- **Verifikasi:**
  - Pemindaian regex `/\benv\s*\(/` di seluruh file PHP di luar folder `config/` menghasilkan **0 temuan** (*clean*).
  - Eksekusi `php artisan config:cache` berhasil dijalankan tanpa ada konfigurasi yang *broken*.
  - Dibuat automated test `DashboardCacheAndEnvOptimizationTest::test_no_env_calls_outside_config_directory` untuk mencegah regresi ke depannya.


