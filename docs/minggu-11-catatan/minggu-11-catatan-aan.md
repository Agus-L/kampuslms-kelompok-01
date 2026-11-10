## Nama: Aan Mardiah
## NIM: 10241001

### READ

| Halaman | Jumlah query | Waktu (ms) | Memori |
| :--- | :---: | :---: | :---: |
| Daftar mata kuliah | 5 queries | 1020 ms | 4 MB |
| Detail mata kuliah | 9 queries | 1810 ms | 5 MB |
| Daftar tugas | 6 queries | 210 ms | 3 MB |
| Daftar pengumpulan (dosen) | 10 queries | 1490 ms | 4 MB |
| **Rekap nilai** | 9 queries | 954 ms | 4 MB |
| Dashboard mahasiswa | 26 queries | 163 ms | 4 MB |
| Dashboard dosen | 11 queries | 225 ms | 4 MB |

1. Urutkan dari yang terburuk.
Jawab: Berdasarkan tabel pengukuran di atas menggunakan Laravel Debugbar pada data seeder penuh, urutan halaman dari yang terburuk adalah:
- **Berdasarkan waktu eksekusi (response time terlama):**
  1. Detail mata kuliah (1810 ms / 1.81 s)
  2. Daftar pengumpulan tugas dosen (1490 ms / 1.49 s)
  3. Daftar mata kuliah (1020 ms / 1.02 s)
  4. Rekap nilai dosen (954 ms)
  5. Dashboard dosen (225 ms)
  6. Daftar tugas (210 ms)
  7. Dashboard mahasiswa (163 ms)

- **Berdasarkan jumlah query terbanyak (indikasi masalah N+1 paling berat):**
  1. Dashboard mahasiswa (26 queries)
  2. Dashboard dosen (11 queries)
  3. Daftar pengumpulan tugas dosen (10 queries)
  4. Detail mata kuliah & Rekap nilai dosen (9 queries)
  5. Daftar tugas (6 queries)
  6. Daftar mata kuliah (5 queries)

2. Untuk halaman terburuk, buka Debugbar tab Queries. Temukan query yang berulang dengan pola sama. Itu N+1 Anda.
Jawab: Pada halaman Dashboard Mahasiswa (`/dashboard`) dan Rekap Nilai Dosen (`/dosen/courses/1/grades`):
- Pada Dashboard Mahasiswa, ditemukan query relasi yang berulang dengan pola identik:
  ```sql
  SELECT * FROM courses WHERE courses.id = ? LIMIT 1;
  SELECT * FROM grades WHERE grades.submission_id = ? LIMIT 1;
  ```
  Query ini terpanggil berulang kali di dalam loop Blade saat menampilkan kartu tugas dan riwayat pengumpulan tugas mahasiswa.
- Pada halaman Rekap Nilai Dosen sebelum optimasi, query yang berulang adalah:
  ```sql
  SELECT * FROM submissions WHERE assignment_id = ? AND user_id = ?;
  SELECT * FROM grades WHERE submission_id = ?;
  ```
  Di mana setiap baris mahasiswa pada setiap tugas memanggil query individual ke database untuk mengambil data submission dan nilai secara berulang. Masalah ini diselesaikan dengan menerapkan eager loading bersarang `with(['submissions.grade'])` dan kalkulasi agregat langsung di database (`AVG`, `MAX`, `MIN`).

3. Salin satu query terlambat, jalankan EXPLAIN di database. Catat kolom type dan rows.
Jawab: Query terlambat yang diuji pada tabel `submissions`:
```sql
EXPLAIN SELECT * FROM submissions WHERE user_id = 5;
```
Hasil sebelum optimasi index:
- `type`: `ALL` (Full Table Scan / pada SQLite: `SCAN submissions`)
- `rows`: `128` (database memindai seluruh baris tabel submissions dari awal hingga akhir karena index bawaan sebelumnya hanya komposit `[assignment_id, user_id]`, sehingga filter mandiri pada `user_id` tidak dapat menggunakan index komposit tersebut).

Setelah ditambahkan index tunggal `idx_submissions_user_id` melalui berkas migrasi performa:
- `type`: `ref` (Index Lookup terarah / pada SQLite: `SEARCH submissions USING INDEX idx_submissions_user_id (user_id=?)`)
- `rows`: `4` (database langsung melompat ke baris yang sesuai tanpa memindai seluruh tabel).



### BREAK

| # | Yang dicoba | Prediksi | Hasil Sebenarnya |
|---|-------------|----------|------------------|
| 1 | Pasang `Model::preventLazyLoading()`, jalankan seluruh halaman | Laravel akan melempar exception error jika mendeteksi adanya relasi database yang dipanggil di Blade/Controller tanpa eager loading (`with()`). | Ketika `Model::preventLazyLoading(! app()->isProduction())` dipasang di `AppServiceProvider.php`, seluruh halaman aplikasi kami berhasil berjalan normal tanpa error. Namun, saat saya sengaja menguji dengan menghapus `->with('lecturer')` pada `CourseController`, browser seketika memunculkan layar merah error `Attempted to lazy load [lecturer] on model [App\Models\Course] but lazy loading is disabled.` Eksperimen ini membuktikan bahwa `preventLazyLoading` bekerja sangat efektif sebagai *early detection* untuk memaksa developer membersihkan N+1 query tersembunyi selama masa pengembangan di komputer lokal sebelum kode dirilis ke produksi. |
| 2 | Hapus semua `with()` dari satu controller, ukur ulang | Jumlah query di halaman tersebut akan meledak bertambah banyak karena relasi dipanggil berulang-ulang satu per satu (N+1 Query). | Saat saya menghapus relasi `with(['submissions' => ...])` pada method `rekap()` di `GradeController`, jumlah query yang sebelumnya hanya 8 query melonjak menjadi 45 query berulang di Debugbar. Database terpaksa melakukan 1 query untuk mengambil 18 mahasiswa, lalu mengeksekusi 18 query terpisah untuk mengambil submission masing-masing mahasiswa, disusul query terpisah lagi untuk mengambil nilai (`grade`). Eksperimen ini memperlihatkan secara nyata bahaya N+1 query, di mana satu halaman bisa membebani server dengan puluhan permintaan database jika relasi tidak dimuat sekaligus (*eager loading*). |
| 3 | Ganti `withCount('students')` menjadi `count($course->students)`, ukur memori | Menggunakan `count()` koleksi akan memboroskan RAM karena seluruh data model mahasiswa di-load ke memori PHP, sedangkan `withCount()` hanya mengambil angka hitungannya saja dari database. | Saat saya mengubah `withCount('students')` menjadi `count($course->students)` pada halaman dashboard dosen, alokasi memori RAM di Debugbar langsung mengalami kenaikan dan waktu respon menjadi lebih lambat. Dengan `withCount()`, database hanya menjalankan `SELECT COUNT(*)`, sehingga yang dikirim ke PHP hanyalah sebuah angka integer sederhana (hemat RAM). Sebaliknya, dengan `count($course->students)`, Laravel terpaksa memuat seluruh baris 18 mahasiswa beserta seluruh atribut kolomnya menjadi objek Eloquent ke dalam memori RAM PHP hanya untuk dihitung jumlahnya. |
| 4 | Hapus index dari `assignments.course_id`, jalankan `EXPLAIN` | Query pencarian tugas berdasarkan mata kuliah akan kehilangan jalur indeks cepat, sehingga tipe eksekusi database berubah menjadi Full Table Scan (`type: ALL`). | Saat saya menguji perintah `EXPLAIN QUERY PLAN SELECT * FROM assignments WHERE course_id = 1;` di database saat indeks terpasang, database menggunakan jalur `SEARCH assignments USING INDEX`. Namun saat indeks dihapus, rencana eksekusi langsung berubah menjadi `SCAN assignments` (atau `type: ALL` pada MySQL). Hal ini membuktikan bahwa tanpa indeks pada kolom foreign key `course_id`, mesin database terpaksa membaca seluruh isi tabel baris demi baris dari awal sampai akhir (*Full Table Scan*), yang akan sangat memperlambat aplikasi ketika jumlah tugas sudah mencapai ribuan data. |
| 5 | Naikkan seeder menjadi 300 mahasiswa dan 1000 submission, buka rekap nilai | Halaman rekap nilai akan terasa sangat lambat (loading lama) dan memori melonjak drastis karena harus memproses matriks ratusan mahasiswa dan ribuan submission. | Saat data seeder ditingkatkan menjadi 300 mahasiswa dan 1000 submission, halaman Rekap Nilai yang tadinya berdurasi di bawah 1 detik melonjak menjadi sangat berat dan memakan waktu loading beberapa detik jika seluruh data dimuat tanpa optimisasi. Eksperimen ini membuktikan bahwa kode yang tampak 'cepat' saat diuji dengan data kecil (18 mahasiswa) bisa langsung roboh saat dipakai di skala kampus sungguhan. Hal ini menegaskan pentingnya optimisasi perhitungan agregat langsung di level SQL (`AVG()`, `MAX()`, `MIN()`) serta penerapan paginasi tabel. |
| 6 | Jalankan `config:cache`, lalu panggil `env('APP_NAME')` dari sebuah controller | Pemanggilan fungsi `env()` akan mengembalikan nilai `null` karena file `.env` tidak lagi dibaca oleh Laravel setelah konfigurasi di-cache. | Saat saya menambahkan kode uji `dd(env('APP_NAME'), config('app.name'))` lalu menjalankan `php artisan config:cache`, layar browser langsung menampilkan nilai `null` untuk `env('APP_NAME')`, sementara `config('app.name')` tetap terbaca `"Laravel"`. Hal ini terjadi karena ketika `config:cache` dieksekusi, Laravel menggabungkan seluruh konfigurasi ke dalam satu file cache dan secara sengaja berhenti membaca file `.env`. Eksperimen ini menjadi pelajaran krusial bahwa fungsi `env()` sama sekali tidak boleh dipanggil di luar folder `config/`, melainkan wajib menggunakan fungsi pembantu `config()`. |
| 7 | Pasang cache 10 menit pada nilai mahasiswa, ubah nilainya, muat ulang | Mahasiswa dan dosen akan melihat nilai lama (data basi / stale data) selama 10 menit meskipun nilai sebenarnya sudah diubah dan disimpan di database. | Saat saya memasang `Cache::remember` selama 10 menit pada pengambilan nilai tugas tanpa memasang fungsi pembersih cache (`Cache::forget`), terjadi masalah data basi (*stale data*). Ketika dosen memperbarui nilai mahasiswa dari 75 menjadi 95 di form lalu menyimpannya, halaman rekap dan dashboard mahasiswa tetap menampilkan angka 75 karena sistem masih membaca data lama dari cache memori. Eksperimen ini membuktikan bahwa data yang bersifat dinamis, sensitif, dan transaksional seperti nilai ujian tidak boleh di-cache sembarangan tanpa mekanisme invalidasi otomatis saat data diperbarui. |

