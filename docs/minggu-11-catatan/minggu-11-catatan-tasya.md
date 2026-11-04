# Catatan Minggu 11 

## Identitas Mahasiswa

Nama: Anastasya Salsabila Khoirunnisa  
NIM: 10241009  

---

## READ 

Prinsip utama optimisasi performa adalah: **"Ukur dulu, baru perbaiki"**. Jangan menebak-nebak di mana letak masalahnya. Pengukuran dilakukan pada aplikasi KampusLMS setelah seeder penuh dijalankan (`php artisan migrate:fresh --seed`) dengan memanfaatkan alat **Laravel Debugbar**.

---

### 1. Tabel Hasil Pengukuran Awal Halaman Utama

Pengukuran metrik jumlah kueri SQL, waktu respons, dan konsumsi memori RAM sebelum dilakukan perbaikan:

| Halaman | URL / Peran | Jumlah Query | Waktu Respons | Penggunaan RAM |
| :--- | :--- | :---:| :---:| :---:|
| Detail Mata Kuliah | `/dosen/courses/1` (Dosen) | 11 kueri | 1.810 ms (1,8 detik) | 5 MB |
| Daftar Pengumpulan Tugas | `/dosen/assignments/1/submissions` (Dosen) | 10 kueri | 1.490 ms (1,5 detik) | 4 MB |
| Daftar Mata Kuliah | `/dosen/courses` (Dosen) | 7 kueri | 1.020 ms (1,0 detik) | 4 MB |
| Rekap Nilai Dosen | `/dosen/courses/1/grades` (Dosen) | 9 kueri | 954 ms (0,9 detik) | 4 MB |
| Dashboard Admin | `/dashboard` (Admin) | 16 kueri | 666 ms | 4 MB |
| Dashboard Dosen | `/dashboard` (Dosen) | 11 kueri | 225 ms | 4 MB |
| Dashboard Mahasiswa | `/dashboard` (Mahasiswa) | 26 kueri | 163 ms | 4 MB |

---

### 2. Urutan Halaman dari yang Terburuk & Penjelasannya

1. **Detail Mata Kuliah (1.810 ms / 5 MB) — Paling Lambat:**  
   Halaman ini paling boros memori karena controller memuat semua relasi sekaligus dalam satu halaman (data dosen pengampu, daftar seluruh mahasiswa terdaftar, modul materi, dan daftar tugas).
2. **Daftar Pengumpulan Tugas (1.490 ms / 4 MB):**  
   Sangat lambat karena mengambil seluruh mahasiswa dan tugas yang masuk menggunakan `->get()` tanpa sistem halaman (*pagination*).
3. **Daftar Mata Kuliah (1.020 ms / 4 MB):**  
   Menghitung jumlah mahasiswa dan tugas di setiap kelas tanpa pengindeksan basis data yang optimal.
4. **Rekap Nilai Dosen (954 ms / 4 MB):**  
   Memiliki struktur kueri bersarang (*nested loops*) yang kompleks antara mahasiswa, tugas, berkas pengumpulan, dan nilai.
5. **Dashboard Admin (666 ms / 16 kueri):**  
   Menghitung berbagai statistik global sistem secara berulang langsung ke database tanpa bantuan cache.
6. **Dashboard Dosen (225 ms / 11 kueri):**  
   Menampilkan ringkasan kelas dan daftar tugas yang perlu diperiksa.
7. **Dashboard Mahasiswa (163 ms / 26 kueri):**  
   Waktunya cepat, namun jumlah kuerinya paling banyak (26 kueri) karena mengecek status tugas dan pengumpulan pada setiap mata kuliah yang diambil.

---

### 3. Menemukan Masalah N+1 Kueri (Pola Kueri Berulang)

Sesuai panduan **Prompt Pack 11.2 A (*Prompt Analisis, Bukan Perbaikan*)**, kita tidak boleh langsung menyuruh AI memperbaiki kode. Kita harus menemukan dulu kueri yang berulang pada tab **Queries** di Debugbar.

**Contoh kueri yang berulang:**
```sql
SELECT * FROM users WHERE users.id = 1;
SELECT * FROM users WHERE users.id = 2;
SELECT * FROM users WHERE users.id = 3;
... (berulang terus sebanyak baris data mahasiswa)
```

**Penjelasan untuk Pemula:**
- **Apa itu N+1?**  
  Bayangkan kita disuruh belanja 15 barang ke pasar. Alih-alih mencatat semuanya di satu kertas lalu belanja sekaligus (**1 kali perjalanan**), kita malah bolak-balik: ambil 1 barang pulang, pergi lagi ambil barang ke-2 pulang, dst. (**1 + 15 = 16 kali perjalanan**). Tentu ini sangat melelahkan dan membuat aplikasi lelet!
- **Akar Masalahnya di Kode:**  
  Di dalam tampilan Blade, ada perulangan `@foreach ($students as $student)` yang memanggil data relasi `$student->submissions` atau nama dosen `$course->lecturer->name`, padahal controller lupa memanggil relasi itu di awal menggunakan `with()` (*Eager Loading*).

---

### 4. Menjalankan Perintah `EXPLAIN` di Database

Mengikuti panduan **Prompt Pack 11.2 C (*Perancangan Index*)**, kita menguji kueri lambat langsung ke database:

```sql
EXPLAIN SELECT * FROM assignments WHERE course_id = 5 ORDER BY due_at;
```

**Cara Membaca Hasilnya (Sederhana):**
- **Kolom `type = ALL` (Buruk):**  
  Artinya terjadi *Full Table Scan*. Database terpaksa membaca buku dari halaman 1 sampai halaman terakhir baris demi baris hanya untuk mencari satu tugas. Sangat lambat jika data sudah ribuan.
- **Kolom `type = ref` atau `range` (Bagus):**  
  Database langsung menuju ke halaman yang dituju karena ada **Index**.
- **Analogi Index:**  
  Index di database itu persis seperti **Daftar Isi pada sebuah buku tebal**. Kalau mau cari bab tertentu, kita tinggal lihat nomor halamannya di daftar isi tanpa perlu membaca seluruh isi buku dari awal.

---

## BREAK 

Hasil uji coba 7 skenario eksperimen performa dari subbab 11.3 modul:

| # | Yang Dicoba | Apa Prediksinya? | Apa yang Terjadi Sebenarnya? (Penjelasan Jelas) |
|:---:|:---|:---|:---|
| **1** | Pasang `Model::preventLazyLoading(! app()->isProduction())` di `AppServiceProvider` | Aplikasi akan menampilkan pesan error jika ada relasi yang lupa di-*eager load* (`with()`). | **Muncul error `LazyLoadingViolationException`.** Fitur ini bertindak seperti "satpam otomatis". Begitu ada kode yang memicu N+1 kueri di komputer lokal, aplikasi langsung berhenti dan memberi tahu baris mana yang salah. Tapi di server produksi (`production`), satpam ini otomatis mati agar pengguna tidak melihat layar error. |
| **2** | Hapus semua `with()` dari controller, lalu ukur ulang | Jumlah kueri akan melonjak tajam mengikuti jumlah data. | **Kueri melonjak dari 2 kueri menjadi 16 kueri.** Waktu respon langsung melambat karena aplikasi bolak-balik menanyakan data ke database 16 kali untuk 15 data baris mata kuliah. (*Sesuai konsep Prompt Pack 11.2 B*). |
| **3** | Ganti `withCount('students')` jadi `count($course->students)` | Penggunaan memori RAM server akan melonjak drastis. | **RAM membengkak lebih dari 2× lipat.** `withCount()` itu menyuruh database menghitung (database cuma mengirimkan satu angka, misalnya: 30). Sedangkan `count($course->students)` memaksa seluruh data lengkap 30 mahasiswa dimuat dan dibuatkan objeknya ke dalam RAM PHP baru dihitung jumlahnya. |
| **4** | Hapus index dari kolom `assignments.course_id`, lalu jalankan `EXPLAIN` | Kolom `type` pada database akan berubah menjadi `ALL`. | **Benar, kolom `type` langsung berubah jadi `ALL`.** Karena "daftar isi"-nya dicabut, database terpaksa membaca semua baris tugas satu per satu. Waktu pencarian menjadi lambat seiring data bertambah banyak. |
| **5** | Naikkan data seeder jadi 300 mahasiswa dan 1.000 submission, buka rekap nilai | Aplikasi yang tadinya terasa cepat saat data sedikit akan mulai macet parah. | **Halaman mengalami *freezing* / loading >6 detik!** Masalah performa yang tidak kelihatan saat data cuma 5 baris langsung meledak saat datanya ratusan. Inilah alasan mutlak kenapa seluruh halaman daftar wajib memakai **Pagination** (*Build #5 yang Tasya kerjakan*). |
| **6** | Jalankan `php artisan config:cache`, lalu panggil `env('APP_NAME')` di Controller | Fungsi `env()` akan mengembalikan nilai kosong (`null`). | **Mengembalikan `null` (kosong)!** Ini jebakan nomor satu saat aplikasi di-upload ke hosting/server. Saat cache aktif, file `.env` tidak lagi dibaca. Solusinya: fungsi `env()` hanya boleh ditulis di dalam folder `config/*.php`, sedangkan di controller wajib memakai `config('app.name')`. |
| **7** | Pasang cache 10 menit pada nilai mahasiswa, ubah nilainya, lalu muat ulang | Mahasiswa akan melihat nilai lama karena datanya tertahan di cache. | **Terjadi *Stale Data* (data basi).** Dosen sudah mengganti nilai jadi 90, tapi mahasiswa masih melihat nilai lama 60 selama 10 menit ke depan. Ini membuktikan bahwa **cache tidak boleh asal dipasang** pada data penting/akademik tanpa pembersihan otomatis (*cache forget*) saat data diubah (*Prompt Pack 11.2 D*). |

---

## Keterkaitan dengan Prompt Pack Minggu 11

Eksperimen di atas membuktikan 4 pedoman penting dari modul:

1. **Prompt Pack A (Analisis Dulu, Jangan Langsung Minta Kode):**  
   Cari tahu dulu dari mana kueri yang banyak itu berasal lewat Debugbar sebelum buru-buru menyuruh AI memperbaiki kode.
2. **Prompt Pack B (Perbaikan Bertahap):**  
   Perbaiki masalah satu per satu (misal: pasang `with()` dulu, ukur hasilnya, baru pasang pagination) agar kita paham dampak dari setiap perbaikan.
3. **Prompt Pack C (Perancangan Index yang Bijak):**  
   Hanya pasang index pada kolom yang sering dicari/difilter. Jangan pasang index di semua kolom karena membuat proses simpan (*insert/update*) jadi lebih lambat.
4. **Prompt Pack D (Strategi Cache yang Aman):**  
   Cache sangat bagus untuk data yang jarang berubah (seperti angka statistik dashboard), tapi berbahaya jika dipasang pada data yang sering diubah seperti nilai mahasiswa.

---

## Kesimpulan Singkat untuk Dipahami

1. **N+1 Kueri:** Selesaikan selalu dengan menambahkan `with()` pada controller agar data diambil sekaligus, bukan bolak-balik.
2. **Hitung vs Muat:** Gunakan `withCount()` untuk menghitung jumlah data di database, jangan pakai `count($model->relasi)` yang memboroskan RAM PHP.
3. **Pagination itu Wajib:** Halaman daftar tidak boleh menampilkan seluruh tabel secara mentah memakai `->get()`. Wajib gunakan `->paginate(15)` agar server tidak kehabisan memori saat mahasiswa bertambah banyak.
4. **Aturan Produksi:** Jangan gunakan `env()` di controller/view, selalu gunakan `config()`.
