# Minggu 11

**Mata Kuliah:** Pemograman Web A <br>
**Nama:** Annisa Dwi Lestari Sonny <br>
**Tanggal:** 03/11/2026

---
### Break

| Yang Dirusak | Prediksi | Hasil Sebenarnya |
| :--- | :--- | :--- |
| Pasang `Model::preventLazyLoading()`, jalankan seluruh halaman | Halaman yang masih memanggil relasi tanpa eager loading bakal langsung error atau melempar exception. | Muncul error `LazyLoadingViolationException` di halaman yang relasinya belum dipasangi `with()`. Setelah ditambahkan eager loading, seluruh halaman bisa dibuka normal kembali. |
| Hapus semua `with()` dari satu controller, ukur ulang | Jumlah kueri database bakal melonjak banyak karena setiap baris data akan menjalankan kueri baru (masalah N+1). | Kueri di Debugbar melonjak drastis dari yang awalnya 2-3 kueri jadi puluhan kueri sesuai jumlah data, dan response time halaman jadi terasa lebih lambat. |
| Ganti `withCount('students')` menjadi `count($course->students)`, ukur memori | Konsumsi RAM server bakal naik karena Laravel harus memuat seluruh baris objek relasi ke memori dulu baru dihitung. | Penggunaan memori di Debugbar naik. `withCount` langsung minta total angka ke SQL (`COUNT`), sedangkan `count($course->students)` memuat seluruh data model ke memori PHP hanya untuk menghitung totalnya. |
| Hapus index dari `assignments.course_id`, jalankan `EXPLAIN` | Pencarian data tugas berdasarkan kelas bakal lebih lambat karena database tidak bisa memakai index sebagai jalur cepat. | Pada hasil `EXPLAIN`, kolom `type` berubah jadi `ALL` dan `key` bernilai `NULL`. Artinya database harus melakukan *Full Table Scan* (memindai seluruh baris tabel satu per satu). |
| Naikkan seeder menjadi 300 mahasiswa dan 1000 submission, buka rekap nilai | Halaman rekap nilai bakal berat dibuka atau loading-nya lama karena memproses data dalam jumlah besar tanpa paginasi/optimasi. | Halaman jadi sangat lambat dimuat, pemakaian memori naik drastis, dan masalah N+1 yang tadinya tidak begitu kelihatan di data kecil langsung tampak jelas efeknya. |
| Jalankan `config:cache`, lalu panggil `env('APP_NAME')` dari sebuah controller | Pemanggilan fungsi `env()` bakal mengembalikan nilai `null` karena file `.env` tidak dibaca lagi setelah config di-cache. | Mengembalikan nilai `null` (bukan string nama aplikasi). Hal ini membuat fungsi yang memanggil `env()` di luar folder `config/` langsung rusak di server produksi. |
| Pasang cache 10 menit pada nilai mahasiswa, ubah nilainya, muat ulang | Nilai terbaru tidak akan langsung terlihat oleh mahasiswa karena aplikasi masih menampilkan data lama dari cache. | Mahasiswa tetap melihat nilai yang lama walaupun nilainya sudah diganti oleh dosen. Data nilainya jadi basi (*stale data*) selama 10 menit. |

---
