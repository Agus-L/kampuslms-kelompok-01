# Catatan Minggu 3
## Identitas Mahasiswa
Nama    : Agus Liberty Purba

NIM : 10241005

## Break
| Yang Dirusak | Prediksi | Hasil Sebenarnya |
| :--- | :--- | :--- |
| Hapus `unique(['course_id','user_id'])` dari `course_user`, lalu daftarkan mahasiswa yang sama dua kali | Data duplikat akan bisa masuk karena batasan unik di level database sudah hilang. | Mahasiswa yang sama berhasil terdaftar dua kali pada mata kuliah yang sama di tabel `course_user` tanpa muncul error apapun (data ganda lolos tanpa keluhan). |
| Tambahkan `role` ke `$fillable` model `User`, lalu kirim request pembuatan user dengan `role=admin` lewat form yang **tidak punya field role** | Kolom role akan ikut tersimpan sebagai admin karena sudah didaftarkan di `$fillable` dan controller memakai mass assignment. | Terjadi celah keamanan *Mass Assignment* nyata. User baru langsung berhasil terdaftar dengan `role=admin` dan punya hak akses admin, padahal di tampilan form aslinya tidak ada inputan pilihan role. |
| Ganti seluruh `$fillable` dengan `protected $guarded = [];` lalu ulangi nomor 2 | Semua kolom bisa diisi secara bebas tanpa proteksi mass assignment dari Laravel. | Input `role=admin` tetap lolos dan user langsung jadi admin. Ini memperlihatkan kenapa `$guarded = []` dilarang, karena membuka celah injeksi ke semua atribut tabel tanpa pengaman. |
| Kosongkan isi `down()` di satu migrasi, lalu jalankan `php artisan migrate:refresh` | Perintah rollback akan gagal atau tabel tidak ter-drop sehingga proses refresh bermasalah. | Proses rollback tidak berjalan sebagaimana mestinya dan terjadi error saat migrasi mencoba membuat tabel yang masih ada. Migrasi jadi tidak *reversible* (tidak bisa di-rollback), yang pada alur kerja CI/CD akan menyebabkan status build gagal (CI merah). |
| Ubah `restrictOnDelete` pada `lecturer_id` menjadi `cascadeOnDelete`, lalu hapus satu dosen | Seluruh mata kuliah yang diajar dosen tersebut akan ikut terhapus otomatis dari database. | Terjadi kehilangan data berantai (*cascading loss*). Begitu user dosen dihapus, semua data mata kuliah yang diampu dosen tersebut otomatis terhapus dari tabel `courses`, dan ikut menyeret materi maupun tugas di dalamnya. |
