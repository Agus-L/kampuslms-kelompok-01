# Minggu 3

**Mata Kuliah:** Pemograman Web A <br>
**Nama:** Annisa Dwi Lestari Sonny <br>
**Tanggal:** 21/09/2026

---

### Break
| Yang Dirusak | Prediksi | Hasil Sebenarnya |
| :--- | :--- | :--- |
| Hapus `unique(['course_id', 'user_id'])` dari `course_user`, lalu daftarkan mahasiswa yang sama dua kali | Data pendaftaran ganda akan berhasil tersimpan ke database tanpa error karena batasan unik di level database telah dihapus. | Data ganda lolos tanpa keluhan. Mahasiswa yang sama berhasil terdaftar dua kali pada mata kuliah yang sama di tabel `course_user` sehingga merusak integritas dan kalkulasi jumlah peserta. |
| Tambahkan `role` ke `$fillable` model `User`, lalu kirim request pembuatan user dengan `role=admin` lewat form yang tidak punya field role | Kolom `role` akan ikut tersimpan sebagai `admin` di database karena atribut `role` telah didaftarkan ke dalam `$fillable` model. | Misal, kamu baru saja jadi admin. Meskipun formulir di frontend tidak memiliki input field role, request dengan payload `role=admin` tetap diproses dan user baru tersimpan sebagai admin di database. Membuktikan frontend bukan pembatas keamanan. |
| Ganti seluruh `$fillable` dengan `protected $guarded = [];` lalu ulangi nomor 2 | Seluruh kolom pada model `User` menjadi terbuka tanpa perlindungan mass assignment, sehingga kolom apa pun yang dikirimkan penyerang akan langsung tersimpan. | Kenapa `$guarded` kosong dilarang. Seluruh proteksi mass assignment mati total. Payload `role=admin` maupun kolom sensitif lainnya dapat disusupi dengan bebas tanpa ada penyaringan atribut di model Eloquent. |
| Kosongkan isi `down()` di satu migrasi, lalu jalankan `php artisan migrate:refresh` | Perintah rollback tidak akan menghapus (drop) tabel terkait, sehingga ketika proses migrasi masuk ke tahap `up()` kembali akan terjadi konflik tabel yang sudah ada. | Migrasi tidak reversible = CI merah. Saat `php artisan migrate:refresh` dijalankan, tabel lama tidak dihapus karena `down()` kosong. Akibatnya saat `up()` dijalankan ulang, muncul error SQL *Table already exists*, yang akan menggagalkan pipeline CI otomatis. |
| Ubah `restrictOnDelete` pada `lecturer_id` menjadi `cascadeOnDelete`, lalu hapus satu dosen | Penghapusan data dosen akan diizinkan oleh database dan memicu penghapusan berantai terhadap semua data mata kuliah yang diampunya. | Kehilangan data berantai. Saat satu data dosen dihapus, database otomatis menghapus seluruh mata kuliah yang diampu dosen tersebut (`courses`), yang selanjutnya juga menghapus materi, tugas, dan nilai mahasiswa di dalamnya. |

---