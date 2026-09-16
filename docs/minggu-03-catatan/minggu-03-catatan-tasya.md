# Catatan Minggu 3

## Identitas Mahasiswa
Nama: Anastasya Salsabila Khoirunnisa
NIM: 10241009

## BREAK

| Yang Dirusak | Prediksi | Hasil Sebenarnya |
| :--- | :--- | :--- |
| Hapus `unique(['course_id','user_id'])` dari `course_user`, lalu daftarkan mahasiswa yang sama dua kali | Data pendaftaran ganda diperkirakan bisa masuk ke database tanpa error karena batasan unik di tabel pivot dihilangkan, sehingga merusak jumlah total peserta. | Mahasiswa berhasil terdaftar berulang kali di mata kuliah yang sama (lolos tanpa error), dan pada relasi mahasiswa muncul duplikat sehingga total peserta membengkak dari 20 menjadi 22. |
| Tambahkan `role` ke `$fillable` model `User`, lalu kirim request pembuatan user dengan `role=admin` lewat form yang **tidak punya field role** | Kolom role akan ikut terisi sebagai admin di database karena atribut role diizinkan untuk mass assignment. | Terjadi celah *mass assignment* nyata di mana user baru (`x@x.test`) langsung tersimpan dengan `role=admin` di database, membuktikan form antarmuka web bukan batas keamanan. |
| Ganti seluruh `$fillable` dengan `protected $guarded = [];` lalu ulangi nomor 2 | Seluruh kolom pada model User terbuka tanpa proteksi mass assignment sehingga role admin dan data sensitif lain bisa dimasukkan secara bebas. | User berhasil dibuat dengan role admin dan kolom sensitif lain (`email_verified_at`) ikut lolos tersimpan ke database, membuktikan bahaya mematikan proteksi model dengan guarded kosong. |
| Kosongkan isi `down()` di satu migrasi, lalu jalankan `php artisan migrate:refresh` | Perintah rollback tidak akan menghapus tabel terkait sehingga saat migrasi dijalankan ulang akan terjadi benturan tabel yang sudah ada. | Saat `migrate:refresh` dijalankan, proses rollback tidak menghapus tabel `notifications`, dan saat tahap `up()` terminal melempar error *table "notifications" already exists* (migrasi tidak reversible, CI merah). |
| Ubah `restrictOnDelete` pada `lecturer_id` menjadi `cascadeOnDelete`, lalu hapus satu dosen | Penghapusan dosen akan diizinkan dan memicu penghapusan otomatis secara berantai pada seluruh data mata kuliah yang diampunya. | Dosen berhasil dihapus dan terjadi kehilangan data berantai (*cascading loss*), di mana mata kuliah, materi, tugas, dan submission ikut terhapus otomatis dari database, berbeda dengan `restrictOnDelete` yang memblokir penghapusan tersebut. |

## Kesimpulan

Eksperimen ini menunjukkan bahwa aturan pada database dan model penting untuk menjaga keamanan dan data aplikasi. `Unique constraint` dapat mencegah data ganda, `$fillable` membantu membatasi data yang dapat diubah, dan `restrictOnDelete` mencegah data lain ikut terhapus. Selain itu, `down()` pada migrasi perlu dibuat dengan benar agar migrasi dapat dibatalkan dan dijalankan kembali.