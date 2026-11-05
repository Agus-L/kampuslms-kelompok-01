# Catatan Praktikum Minggu 11: Optimisasi Performa
**Nama:** Aliya Labibah  
**NIM:** 10241007  
**Mata Kuliah:** Pemrograman Web (KampusLMS | Laravel 12)  

---

## READ — Ukur Aplikasi Sendiri (40 Menit)

Sebelum melakukan optimisasi apa pun, saya mengukur performa awal seluruh halaman utama KampusLMS menggunakan Laravel Debugbar untuk mencatat jumlah query, waktu eksekusi (latency), dan alokasi memori RAM.

### Tabel Pengukuran Sebelum Optimisasi (Baseline)

| Halaman | URL / Peran | Jumlah Query | Waktu Eksekusi | Alokasi Memori |
| :--- | :--- | :---:| :---:| :---:|
| **Rekap Nilai Dosen** | `/dosen/courses/1/grades` | 9 queries | 954 ms | 4 MB |
| **Dashboard Dosen** | `/dashboard` (Dosen) | 11 queries | 225 ms | 4 MB |
| **Dashboard Mahasiswa** | `/dashboard` (Mahasiswa) | 26 queries | 163 ms | 4 MB |
| **Dashboard Admin** | `/dashboard` (Admin) | 16 queries | 666 ms | 4 MB |
| **Daftar Mata Kuliah** | `/dosen/courses` | 7 queries | 1.02 s | 4 MB |
| **Detail Mata Kuliah** | `/dosen/courses/1` | 11 queries | 1.81 s | 5 MB |
| **Daftar Pengumpulan Tugas** | `/dosen/assignments/1/submissions` | 10 queries | 1.49 s | 4 MB |

* **Halaman Paling Berat:** Halaman **Rekap Nilai Dosen** dan **Detail Mata Kuliah** adalah yang paling lambat (mencapai ~1 hingga 1.8 detik). Pada Rekap Nilai, hal ini terjadi karena sistem harus merangkum seluruh mahasiswa terhadap seluruh tugas sekaligus.

---
---

## BREAK — Tujuh Percobaan (45 Menit)

| # | Yang dicoba | Prediksi | Hasil Sebenarnya |
|---|-------------|----------|------------------|
| 1 | Pasang `Model::preventLazyLoading()`, jalankan seluruh halaman | Halaman web akan langsung memunculkan pesan error kalau ada data relasi yang lupa diambil di awal menggunakan `with()`. | **Saat fitur `preventLazyLoading` aktif, seluruh halaman web kami awalnya berjalan normal tanpa error sama sekali.** Lalu untuk membuktikan fungsinya, saya sengaja menghapus tulisan `->with('lecturer')` di file `CourseController`. Begitu halamannya dibuka kembali, browser langsung menampilkan layar merah error bertuliskan *`Attempted to lazy load [lecturer]... but lazy loading is disabled`*. Hal ini membuktikan bahwa fitur ini langsung memberitahu jika ada data relasi yang lupa dimuat di controller, sehingga kita bisa segera memperbaikinya agar halaman web nantinya tidak menjadi lambat. |
| 2 | Hapus semua `with()` dari satu controller, ukur ulang | Jumlah query database akan bertambah jauh lebih banyak karena data pendukungnya diambil bolak-balik satu per satu. | **Saat saya mencoba menghapus tulisan `with()` di `GradeController` pada halaman Rekap Nilai, jumlah query yang tadinya cuma 8 langsung melonjak jadi puluhan query di Debugbar.** Hal ini terjadi karena Laravel harus bolak-balik ke database: 1 kali untuk mengambil data 18 mahasiswa, lalu 18 kali bolak-balik lagi cuma untuk mencari data tugas dan nilai masing-masing anak. ini membuktikan bahwa tanpa `with()`, aplikasi jadi sangat boros query dan bisa membuat halaman web terasa berat saat mahasiswanya bertambah banyak. |
| 3 | Ganti `withCount('students')` menjadi `count($course->students)`, ukur memori | Memakai `count()` biasa akan memakan lebih banyak memori RAM daripada `withCount()`, karena sistem harus menarik seluruh data orangnya ke memori hanya untuk menghitung totalnya. | **Saat saya mengganti `withCount('students')` menjadi `count($course->students)` pada Dashboard Dosen, pemakaian memori RAM di Debugbar langsung naik.** Hal ini terjadi karena kalau pakai `withCount()`, database cuma mengirimkan angka totalnya saja (misalnya angka 18). Sedangkan kalau pakai `count($course->students)`, sistem terpaksa mengambil semua data 18 mahasiswa ke memori laptop cuma untuk dihitung jumlahnya. Hal ini membuktikan bahwa `withCount()` jauh lebih hemat memori saat kita hanya butuh menampilkan jumlah data. |
| 4 | Hapus index dari `assignments.course_id`, jalankan `EXPLAIN` | Database tidak bisa lagi mencari data lewat jalan pintas (indeks), sehingga terpaksa memeriksa seluruh isi tabel dari awal sampai akhir. | **Saat saya mengetik perintah `EXPLAIN` di terminal Tinker saat indeks masih ada, database mencari tugas lewat jalur cepat (*SEARCH USING INDEX*). Tapi begitu indeksnya saya hapus, statusnya langsung berubah menjadi *SCAN* (atau *type: ALL*).** Artinya, database tidak punya 'daftar isi' lagi sehingga terpaksa membaca seisi tabel dari baris pertama sampai terakhir satu per satu. Hal ini membuktikan bahwa indeks pada kolom `course_id` sangat penting agar pencarian tugas di kelas tetap cepat dan tidak membebani server saat jumlah tugas sudah banyak. |
| 5 | Naikkan seeder menjadi 300 mahasiswa dan 1000 submission, buka rekap nilai | Halaman Rekap Nilai akan terasa jauh lebih berat dan kuerinya meledak jika kodingannya tidak dioptimasi untuk menangani banyak data. | **Saat saya mencoba menambahkan data menjadi 330 mahasiswa dan hampir 1.000 tugas lalu mematikan optimasi `with()`, perbedaannya langsung terlihat sangat jelas.** Jumlah query di Debugbar yang tadinya cuma 10 langsung meledak menjadi **1.326 query**, pemakaian memori naik 4 kali lipat jadi **12 MB**, dan waktu buka halaman menjadi jauh lebih lambat yaitu **1,25 detik**. Eksperimen ini membuktikan bahwa kesalahan kodingan yang tadinya tidak terasa saat datanya sedikit (18 orang), langsung menjadi masalah besar dan membuat website lemot parah saat dipakai oleh ratusan mahasiswa di kampus sungguhan. |
| 6 | Jalankan `config:cache`, lalu panggil `env('APP_NAME')` dari sebuah controller | Fungsi `env()` akan menjadi kosong (`null`) karena Laravel berhenti membaca file teks `.env` setelah konfigurasinya di-cache. | **Saat saya mengetik `dd(env('APP_NAME'), config('app.name'))` lalu menjalankan perintah `php artisan config:cache`, layar browser menampilkan tulisan `null` untuk `env()`, sedangkan `config('app.name')` tetap muncul nama aplikasinya.** Hal ini terjadi karena file `.env` cuma file teks biasa di harddisk yang lambat jika dibaca berulang-ulang, sehingga perintah `config:cache` merangkum semuanya ke memori RAM agar website bisa berjalan lebih cepat. Begitu cache aktif, Laravel sengaja mengunci dan tidak membaca file `.env` lagi. Dari percobaan ini, saya belajar bahwa di dalam controller kita tidak boleh memanggil `env()` secara langsung, melainkan harus selalu mengambil setelan lewat `config()`. |
| 7 | Pasang cache 10 menit pada nilai mahasiswa, ubah nilainya, muat ulang | Nilai di web tidak akan mau berubah dan tetap menampilkan angka lama karena sistem masih membaca contekan dari memori cache (terjadi data basi / *stale data*). | **Saat saya mencoba menyimpan nilai ke dalam cache selama 10 menit menggunakan `cache()->remember` lalu mengubah nilainya, tampilan di layar ternyata tetap menampilkan nilai yang lama dan tidak mau ter-update.** Hal ini terjadi karena sistem masih membaca 'contekan' lama yang tersimpan di memori cache selama 10 menit. Dari eksperimen ini dan melihat kodingan proyek kami di Dashboard Admin, saya jadi paham bahwa cache itu sangat bagus untuk data yang berat tapi jarang berubah (seperti menghitung total mahasiswa/dosen). Namun untuk data yang sering diedit atau harus selalu tepat seperti nilai ujian, cache tidak boleh dipasang sembarangan kecuali kita menambahkan perintah pembersih otomatis (`Cache::forget`) setiap kali datanya disimpan. |

