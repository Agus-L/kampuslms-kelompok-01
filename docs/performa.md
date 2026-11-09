# Optimasi Peforma
---

## 1. Tabel Optimasi peforma sebelum dan sesudah 

Berikut adalah rekapitulasi audit performa pada tujuh halaman utama KampusLMS sebelum dan sesudah seluruh rangkaian optimisasi  diterapkan:

| Halaman                      | URL / Peran                          | Query (Sebelum) | Waktu (Sebelum) | Memori (Sebelum) | Query (Sesudah) | Waktu (Sesudah) | Memori (Sesudah) |
| :--------------------------- | :----------------------------------- | :-------------: | :-------------: | :--------------: | :-------------: | :-------------: | :--------------: |
| **Rekap Nilai Dosen**        | `/dosen/courses/1/grades`            |    9 queries    |    954.00 ms    |       4 MB       | **10 queries**  |  **91.19 ms**   |     **3 MB**     |
| **Dashboard Dosen**          | `/dashboard` (Dosen)                 |   11 queries    |    225.00 ms    |       4 MB       |  **8 queries**  |  **74.20 ms**   |     **3 MB**     |
| **Dashboard Mahasiswa**      | `/dashboard` (Mahasiswa)             |   26 queries    |    163.00 ms    |       4 MB       | **12 queries**  |  **68.40 ms**   |     **3 MB**     |
| **Dashboard Admin**          | `/dashboard` (Admin)                 |   16 queries    |    666.00 ms    |       4 MB       |  **7 queries**  |  **52.10 ms**   |     **3 MB**     |
| **Daftar Mata Kuliah**       | `/dosen/courses`                     |    7 queries    |   1020.00 ms    |       4 MB       |  **4 queries**  |  **85.30 ms**   |     **3 MB**     |
| **Detail Mata Kuliah**       | `/dosen/courses/1`                   |   11 queries    |   1810.00 ms    |       5 MB       |  **6 queries**  |  **110.50 ms**  |     **4 MB**     |
| **Daftar Pengumpulan Tugas** | `/dosen/assignments/1/submissions`   |   10 queries    |   1490.00 ms    |       4 MB       | **11 queries**  |  **160.00 ms**  |     **3 MB**     |

---

## 2. Implementasi Pencegahan N+1 Otomatis (Build 2)

Fitur `Model::preventLazyLoading()` telah diaktifkan secara global pada `app/Providers/AppServiceProvider.php`:

```php
use Illuminate\Database\Eloquent\Model;

public function boot(): void
{
    Model::preventLazyLoading(! app()->isProduction());

    // Konfigurasi rate limiter ...
}
```

### Mekanisme Kerja:
1. **Lingkungan Pengembangan (Local/Testing):** Melempar exception `LazyLoadingViolationException` seketika saat ada relasi Eloquent yang diakses tanpa melalui eager loading (`with()`, `load()`, atau `loadMissing()`). Hal ini memaksa developer memperbaiki N+1 sedini mungkin saat proses penulisan kode.
2. **Lingkungan Produksi (Production):** Secara otomatis dinonaktifkan (`! app()->isProduction()`) agar aplikasi tidak melempar error HTTP 500 ke pengguna akhir jika terdapat relasi tak terduga.
3. **Hasil Audit:** Seluruh alur kerja dan seluruh halaman pada tabel di atas telah diuji dan berjalan lancar tanpa melanggar batasan pencegahan lazy loading.

---

## 3. Catatan Optimisasi Rekap Nilai Dosen (Build 1 & Build 8)

Halaman Rekap Nilai Dosen (`/dosen/courses/{course}/grades`) merupakan halaman matriks nilai kompleks yang menjadi target audit utama modul Minggu 11.

### Rincian Perbaikan yang Diterapkan:
1. **Raw Database Aggregate (Penggantian In-Memory Loop):**
   - *Sebelum:* Melakukan iterasi ganda (nested loop) atas seluruh koleksi mahasiswa dan penugasan di memori PHP untuk menghitung rata-rata kelas, nilai tertinggi, dan nilai terendah.
   - *Sesudah:* Perhitungan didelegasikan langsung ke database engine menggunakan query agregasi terfokus:
     ```php
     $aggregate = Grade::whereHas('submission', function ($query) use ($assignmentIds, $studentIds) {
         $query->whereIn('assignment_id', $assignmentIds)
               ->whereIn('user_id', $studentIds);
     })
     ->selectRaw('AVG(score) as avg_score, MAX(score) as max_score, MIN(score) as min_score, COUNT(score) as graded_count')
     ->first();
     ```
   - *Dampak:* Mengeliminasi lonjakan CPU saat jumlah baris besar serta memangkas pemakaian RAM server dari 4 MB menjadi 3 MB.
2. **Specific Column Selection (Menghindari SELECT *):**
   - Menghindari pemuatan kolom sensitif dan teks besar (`password`, `remember_token`, `description` panjang) dengan membatasi kolom:
     - `assignments`: `id`, `course_id`, `title`, `max_score`
     - `users`: `id`, `name`, `nim_nip`, `email`
     - `submissions`: `id`, `assignment_id`, `user_id`
     - `grades`: `id`, `submission_id`, `score`
3. **Pencapaian Target:** Jumlah kueri stabil di **10 queries**, memenuhi target modul yaitu **di bawah 15 query**, dengan waktu respons 91.19 ms (terpangkas dari 954 ms).

---

## 4. Eliminasi N+1 Query Lintas Controller dengan Eager Loading (Build 3)

Melalui audit codebase, seluruh controller utama diperbaiki untuk memastikan tidak ada relasi yang dimuat secara parsial (lazy):

### A. CourseController (Admin & Dosen)
- Pada `Admin\CourseController@index`, query mata kuliah ditambahkan agregasi dan eager loading:
  ```php
  $courses = Course::with('lecturer')
      ->withCount(['students', 'materials', 'assignments'])
      ->latest()
      ->paginate(15);
  ```
- Pada `Dosen\CourseController@show`, relasi penugasan dan pengumpulan dimuat secara bersarang:
  ```php
  $course->loadMissing(['lecturer', 'materials', 'assignments.submissions.student']);
  ```

### B. AssignmentController & MaterialController
- Pada controller penugasan dan materi (baik peran Dosen maupun Mahasiswa), pemanggilan `loadMissing('lecturer')` ditambahkan pada model `$course` induk, serta `$course->assignments()->with('course')` diterapkan agar breadcrumb dan relasi balik tidak memicu query tambahan.

### C. SubmissionController (Mahasiswa)
- Pada method `create()` dan `edit()`, ditambahkan `loadMissing('course')` dan `loadMissing('assignment.course')` sehingga layout form tidak memicu lazy loading saat memeriksa atribut mata kuliah.

---

## 5. Perancangan Migrasi Index Tambahan (Build 4)

Berkas migrasi tambahan: `database/migrations/2026_11_02_000001_add_performance_indexes_to_tables.php`

Indeks dirancang berdasarkan pola query yang paling sering dijalankan pada aplikasi:

| Tabel         | Nama Index                            | Kolom Index                    | Query yang Diuntungkan                                                                                              | Analisis Biaya Tulis (Write Cost)                                                     |
| :------------ | :------------------------------------ | :----------------------------- | :------------------------------------------------------------------------------------------------------------------ | :------------------------------------------------------------------------------------ |
| `courses`     | `idx_courses_lecturer_status`         | `['lecturer_id', 'status']`    | `Course::where('lecturer_id', ...)->where('status', 'active')` (Dashboard Dosen & Filter MK)                        | Tambahan minor pada operasi INSERT/UPDATE mata kuliah.                                |
| `courses`     | `idx_courses_status`                  | `'status'`                     | `Course::where('status', 'active')->count()` (Dashboard Admin) & filter katalog kursus                               | Overhead kecil saat pembaruan status draft ke active.                                 |
| `assignments` | `idx_assignments_course_status`       | `['course_id', 'status']`      | `Assignment::whereIn('course_id', ...)->where('status', 'published')` (Dashboard Mahasiswa & counter tugas terbit) | Overhead saat dosen mempublikasikan tugas.                                            |
| `submissions` | `idx_submissions_user_id`             | `'user_id'`                    | `Submission::where('user_id', ...)` (Dashboard Mahasiswa `total_submitted`, `mySubmissions`, & riwayat mahasiswa)   | Menghilangkan Full Table Scan (type: ALL) saat mencari submission mahasiswa tertentu. |
| `submissions` | `idx_submissions_assignment_is_late` | `['assignment_id', 'is_late']` | Filter keterlambatan pengumpulan di halaman dosen (`where('assignment_id', ...)->where('is_late', true)`)          | Overhead minor saat insert pengumpulan tugas.                                         |
| `grades`      | `idx_grades_graded_at`                | `'graded_at'`                  | `Grade::latest('graded_at')->take(5)` (Dashboard Mahasiswa - nilai terbaru)                                         | Menghilangkan filesort di memori saat pengurutan riwayat nilai.                         |

### Index yang Sengaja Tidak Ditambahkan (Evaluasi Modul 11.2 Pack C):
1. Kolom teks pencarian nama (`users.name`, `courses.name`): Menggunakan query wildcard `LIKE '%keyword%'` yang secara arsitektural tidak dapat memanfaatkan index B-Tree.
2. Kolom deskripsi (`materials.description`, `assignments.instructions`): Tipe `TEXT` panjang yang boros tempat dan tidak efisien diindeks tanpa full-text index engine.
3. Kolom `submissions.is_late` mandiri: Kardinalitas sangat rendah (hanya bernilai 0 atau 1), sehingga hanya efektif saat digabungkan sebagai indeks komposit dengan `assignment_id`.

---

## 6. Penerapan Pagination di Seluruh Halaman Daftar (Build 5)

Sesuai ketentuan Build 5: *"Pagination di seluruh halaman daftar, tidak ada yang memuat seluruh tabel."*

### A. Audit Temuan Awal
Sebelumnya, halaman daftar pengumpulan tugas dosen (`/dosen/assignments/{assignment}/submissions`) memuat seluruh mahasiswa terdaftar menggunakan `students()->get()` dan seluruh pengumpulan via `submissions()->get()` tanpa paginasi.

### B. Implementasi Perbaikan
1. **Paginasi di Dosen\SubmissionController@index:**
   - Mengganti `->get()` dengan `->paginate(15)->withQueryString()`.
   - Ringkasan statistik dihitung di database menggunakan `count()` terindeks tanpa memuat row mahasiswa ke PHP.
   - Menggunakan `$paginator->through()` untuk transformasi tampilan kartu mahasiswa.
2. **Navigasi Blade:**
   - Menambahkan komponen navigasi `{{ $studentList->links() }}` pada `resources/views/submissions/index.blade.php`.
3. **Paginasi di Mahasiswa\SubmissionController@index:**
   - Mengubah query riwayat submission mahasiswa menjadi `->paginate(15)`.

### C. Hasil Pengukuran
- Memori halaman pengumpulan terpangkas dari 4 MB menjadi **3 MB**.
- Waktu respons terpangkas dari 1.49 s menjadi **160 ms**.

---

## 7. Strategi Caching Dashboard dan Pembersihan Otomatis (Build 6)

Sesuai ketentuan Build 6: *"Cache pada minimal satu bagian dashboard, lengkap dengan pembersihan otomatis saat data berubah."*

### A. Implementasi Caching pada Dashboard Admin
Pada `app/Http/Controllers/DashboardController.php`, method `adminDashboard()` sebelumnya menjalankan 9 query agregat `COUNT()` terpisah setiap kali dimuat. Sembilan query tersebut dibungkus dengan facade cache:

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

### B. Pembersihan Otomatis (Cache Invalidation)
Hook event model pada method `booted()` dipasang pada lima model terkait:
- `App\Models\User`: Memicu `Cache::forget('admin.dashboard.stats')` saat event `saved`, `deleted`, `restored`.
- `App\Models\Course`: Memicu `Cache::forget('admin.dashboard.stats')` saat event `saved`, `deleted`.
- `App\Models\Material`: Memicu `Cache::forget('admin.dashboard.stats')` saat event `saved`, `deleted`.
- `App\Models\Assignment`: Memicu `Cache::forget('admin.dashboard.stats')` saat event `saved`, `deleted`.
- `App\Models\Submission`: Memicu `Cache::forget('admin.dashboard.stats')` saat event `saved`, `deleted`.

### C. Data yang Sengaja TIDAK Di-cache (Evaluasi Checkpoint 11.4)
1. **Nilai Mahasiswa (`Grade`) dan Riwayat Nilai:** Meng-cache nilai dapat merugikan mahasiswa yang baru saja diperbaiki nilainya oleh dosen namun masih melihat data lama.
2. **Status Pengumpulan Tugas (`mySubmissions` Mahasiswa):** Mahasiswa memerlukan konfirmasi seketika bahwa tugasnya berhasil terunggah.
3. **Daftar Tugas yang Membutuhkan Penilaian (`pendingSubmissions` Dosen):** Dosen memerlukan daftar antrean penilaian yang selalu mutakhir saat tugas baru masuk.

### D. Hasil Pengukuran
- Query Dashboard Admin terpangkas dari **16 queries** menjadi **7 queries** (penurunan beban database sebesar ~56%).
- Waktu eksekusi turun dari 666 ms menjadi **52.10 ms**.

---

## 8. Eliminasi Pemanggilan env() di Luar config/ (Build 7)

Sesuai ketentuan Build 7: *"Seluruh pemanggilan env() di luar config/ dihapus, diganti config()."*

### A. Urgensi
Ketika perintah produksi `php artisan config:cache` dieksekusi, berkas `.env` tidak lagi dibaca oleh runtime framework. Semua pemanggilan `env()` di luar folder `config/*.php` akan mengembalikan nilai `null`.

### B. Audit & Perbaikan
- **Temuan:** Ditemukan pemanggilan `env('DEMO_PASSWORD', 'password')` di dalam `database/seeders/DemoAccountSeeder.php`.
- **Perbaikan:**
  1. Menambahkan entri konfigurasi di `config/auth.php`:
     ```php
     'demo_password' => env('DEMO_PASSWORD', 'password'),
     ```
  2. Mengganti pemanggilan di `DemoAccountSeeder.php` menjadi:
     ```php
     $password = config('auth.demo_password', 'password');
     ```
- **Hasil:** Pemindaian fungsi `env()` di luar folder `config/` menghasilkan **0 temuan**. Perintah `php artisan config:cache` dapat berjalan dengan aman tanpa resiko kehilangan variabel konfigurasi.

---

## 9. Rangkuman Pemenuhan Seluruh Checklist Modul Minggu 11

| Poin Build  | Ketentuan Modul                                                                              | Status Implementasi                                          |
| :---------: | :------------------------------------------------------------------------------------------- | :----------------------------------------------------------- |
| **Build 1** | Berkas `docs/performa.md` memuat tabel pengukuran sebelum dan sesudah seluruh halaman utama  | Terpenuhi pada Bagian 1 dokumen ini                          |
| **Build 2** | `Model::preventLazyLoading()` aktif di non-produksi dan aplikasi berjalan tanpa melanggarnya | Terpenuhi pada `app/Providers/AppServiceProvider.php`        |
| **Build 3** | Seluruh N+1 diperbaiki dengan `with()`, `withCount()`, dan `loadMissing()`                   | Terpenuhi pada seluruh Controller utama                      |
| **Build 4** | Migrasi tambahan berisi rancangan index dengan komentar query yang diuntungkan               | Terpenuhi pada migrasi `2026_11_02_000001`                   |
| **Build 5** | Paginasi pada seluruh halaman daftar tanpa memuat seluruh tabel                              | Terpenuhi pada seluruh halaman daftar pengumpulan dan entitas |
| **Build 6** | Caching pada dashboard dengan auto-invalidation dan penjelasan data yang tidak di-cache      | Terpenuhi pada Dashboard Admin via `Cache::remember`          |
| **Build 7** | Seluruh pemanggilan `env()` di luar folder `config/` diganti dengan `config()`               | Terpenuhi 100% bebas dari pemanggilan `env()` langsung       |
| **Build 8** | Halaman rekap nilai dosen turun menjadi di bawah 15 query                                    | Terpenuhi (stabil pada 10 queries, waktu 91.19 ms)           |
