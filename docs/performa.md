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

