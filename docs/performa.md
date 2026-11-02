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


