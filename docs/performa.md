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
| **Dashboard Admin** | `/dashboard` (Admin) | 16 queries | 666 ms | 4 MB | *(oleh tim)* | *(oleh tim)* | *(oleh tim)* | ⏳ Dalam pengerjaan tim |
| **Daftar Mata Kuliah** | `/dosen/courses` | 7 queries | 1.02 s | 4 MB | *(oleh tim)* | *(oleh tim)* | *(oleh tim)* | ⏳ Dalam pengerjaan tim |
| **Detail Mata Kuliah** | `/dosen/courses/1` | 11 queries | 1.81 s | 5 MB | *(oleh tim)* | *(oleh tim)* | *(oleh tim)* | ⏳ Dalam pengerjaan tim |
| **Daftar Pengumpulan Tugas**| `/dosen/assignments/1/submissions` | 10 queries | 1.49 s | 4 MB | *(oleh tim)* | *(oleh tim)* | *(oleh tim)* | ⏳ Dalam pengerjaan tim |

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

