# Nama: Aliya Labibah
## NIM: 10241007

---

## READ 

### 1. Temukan berkas fisiknya di sistem berkas. Path lengkapnya apa?

* **Lokasi Penyimpanan:** Tersimpan di dalam disk privat Laravel pada folder `storage/app/private/materials/courses/{course_id}/`.
* **Path Lengkap di Sistem Berkas (Windows):**
  ```text
  D:\kampuslms-kelompok-01\kampuslms-kelompok-01\storage\app\private\materials\courses\1\aB3k9Zx8YpL2mQ0r1tVw.pdf
  ```
  *(Nama file fisik berupa string hash acak yang di-generate otomatis oleh `$file->store()`, bukan nama asli berkas).*

---

### 2. Buka baris database yang bersangkutan. Bandingkan `file_path` dengan lokasi fisiknya.

* **Nilai di Kolom Database (`file_path`):**
  `materials/03dec473-a1aa-3ece-a096-a4e85af509d3.pdf` $\rightarrow$ Merupakan **Path Relatif**.
* **Lokasi Fisik di Sistem Berkas (File Explorer):**
  `D:\kampuslms-kelompok-01\kampuslms-kelompok-01\storage\app\private\materials\03dec473-a1aa-3ece-a096-a4e85af509d3.pdf` $\rightarrow$ Merupakan **Path Absolut**.
* **Perbandingan:**
  Lokasi fisik berstatus **path absolut** karena memuat alamat lengkap dari akar drive komputer (`D:\...`), sedangkan database hanya mencatat **path relatif** (hanya sub-foldernya saja tanpa `D:\`). Database sengaja hanya menyimpan path relatif agar aplikasi tetap **portabel** (tidak rusak saat folder dipindahkan ke laptop lain atau dideploy ke server Linux yang tidak memiliki drive `D:\`).

---

### 3. Coba akses berkas itu langsung lewat browser dengan menebak URL-nya. Berhasil atau tidak? Kenapa?

* **Cara Mencobanya:**
  Buka browser, lalu coba ketik URL tebakan langsung di address bar:
  ```text
  http://127.0.0.1:8000/storage/app/private/materials/03dec473-a1aa-3ece-a096-a4e85af509d3.pdf
  ```
  *(Atau tebak URL: `http://127.0.0.1:8000/materials/03dec473-a1aa-3ece-a096-a4e85af509d3.pdf`)*.
* **Hasil:** **Tidak berhasil (HTTP 404 Not Found)**.
* **Kenapa:** Folder `storage/app/private` berada di luar folder publik (`public/`) dan **tidak memiliki symlink** ke web server. Berkas privat tidak bisa dibuka langsung lewat URL, melainkan hanya bisa diunduh jika melewati Controller yang memanggil `Gate::authorize()` dan `Storage::download()`.

---

### 4. Buka `config/filesystems.php`. Identifikasi disk `local` dan `public` — apa persis bedanya?

* **Disk `'local'` (Privat):**
  * Root folder: `storage/app/private`.
  * Tidak punya URL publik dan tidak dihubungkan ke `public/`.
  * Akses wajib melalui Controller + Policy (digunakan untuk **materi kuliah & pengumpulan tugas**).
* **Disk `'public'` (Publik):**
  * Root folder: `storage/app/public`.
  * Punya URL publik langsung (`/storage/...`) lewat perintah `storage:link`.
  * Siapa pun bisa mengunduh langsung lewat browser tanpa login (digunakan untuk **foto profil & logo kampus**).

---

### 5. Jalankan `php artisan storage:link`. Apa yang dibuatnya? Kenapa materi Anda **tidak boleh** ada di sana?

* **Apa yang Dibuat:**
  Perintah ini membuat **Symbolic Link (shortcut tautan)** dari folder `public/storage` menuju ke folder `storage/app/public`.
* **Kenapa Materi Tidak Boleh Ada di Sana:**
  Karena berkas apa pun yang ada di disk publik bisa diakses langsung via URL peramban (`http://.../storage/...`) tanpa login dan tanpa dicek oleh Policy. Jika materi atau tugas ditaruh di sana, seluruh kelas dan orang luar bisa menyontek dan mengunduh berkas tersebut secara bebas.

---

## BREAK — Delapan kerusakan (50 menit)

| # | Yang dicoba | Prediksi | Hasil Sebenarnya |
| --- | --- | --- | --- |  
| 1 | Pindahkan berkas submission ke `storage/app/public`, akses URL-nya tanpa login | Seluruh berkas tugas mahasiswa akan bocor dan dapat diakses bebas oleh siapa pun lewat internet tanpa proses autentikasi (login). | **Berkas jawaban tugas langsung terbuka di peramban tanpa memerlukan akun atau sesi login.** Saat disk penyimpanan dialihkan ke `'public'` (`$file->store("submissions/{$assignment->id}", 'public')`), berkas fisik diletakkan pada direktori `storage/app/public/submissions/14/`. Karena folder tersebut terhubung ke `public/storage` via *symlink*, URL `http://localhost:8000/storage/submissions/14/ByWIq3mDdQNlg5Vl5iIplG83oNvtKUqX2OK4lQAC.pdf` langsung disajikan oleh web server sebagai file statis biasa. Akibatnya, gerbang autentikasi Laravel dan aturan otorisasi Policy terlewati (*bypassed*) total, sehingga siapapun yang memiliki link bisa melihat tugas tersebut. |
| 2 | Hapus `Gate::authorize()` dari endpoint download, akses submission orang lain | Terbuka celah kerentanan IDOR pada berkas privat, sehingga seorang mahasiswa dapat mengintip dan mengunduh berkas pengumpulan milik rekan sekelasnya. | **Pengguna dapat mengunduh berkas submission milik orang lain hanya dengan mengganti nomor ID pada URL (*IDOR*).** Ketika perintah `Gate::authorize('download', $submission)` pada method `download()` di [Mahasiswa/SubmissionController.php](file:///d:/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/Mahasiswa/SubmissionController.php#L232) dinonaktifkan, Mahasiswa A yang telah login dapat mengubah URL dari `/mahasiswa/submissions/1/download` menjadi `/mahasiswa/submissions/2/download` dan berkas milik Mahasiswa B langsung terunduh tanpa halangan. Begitu `Gate::authorize` diaktifkan kembali, server langsung menolak dengan status **403 Forbidden** karena [SubmissionPolicy.php](file:///d:/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Policies/SubmissionPolicy.php) memvalidasi kepemilikan secara ketat (`$submission->user_id === $user->id`). |
| 3 | Simpan berkas dengan `storeAs()` memakai nama asli, lalu dua akun mahasiswa berbeda mengunggah `tugas.pdf` dengan isi berbeda ke tugas yang sama | Berkas mahasiswa pertama akan tertindih (*overwritten*) dan hilang saat ada mahasiswa lain mengunggah berkas dengan nama yang sama persis. | **Isi dokumen mahasiswa pertama lenyap karena tertimpa secara permanen oleh unggahan mahasiswa kedua.** Hal ini terjadi karena saat menggunakan `$file->storeAs(...)` dengan nama asli (`$file->getClientOriginalName()`), kedua pengumpulan menargetkan alamat file fisik yang sama (`submissions/{id}/tugas.pdf`). Sistem operasi di server secara otomatis menimpa file lama dengan file baru. Eksperimen ini membuktikan bahwa nama berkas bawaan pengguna tidak aman untuk dijadikan nama file di server. Sistem harus selalu memakai `$file->store()` untuk menghasilkan nama acak (*hash* unik), sedangkan nama asli berkas cukup dicatat pada kolom `original_name` di database. |
| 4 | Unggah berkas PHP yang diganti namanya jadi `.pdf` dengan validasi `extensions`, lalu dengan `mimes` | Aturan validasi `extensions` akan tertipu oleh nama ekstensi palsu, sedangkan aturan `mimes` akan menggagalkan unggahan karena memeriksa konten biner file sesungguhnya. | **Validasi `extensions:pdf` berhasil ditembus oleh skrip berbahaya yang disamarkan, sementara validasi `mimes:pdf` sukses memblokirnya.** Saat skrip PHP berbahaya (`script.php`) diubah namanya menjadi `script.pdf`, rule `extensions` meloloskannya karena pengecekan hanya sebatas membaca string nama berkas. Sebaliknya, saat menggunakan `mimes:pdf`, sistem membaca *magic bytes* berkas menggunakan pustaka PHP Fileinfo dan mendeteksi bahwa berkas aslinya bertipe `text/x-php`, sehingga server langsung menolak dengan error validasi. Ini membuktikan bahwa `mimes` adalah benteng pertahanan krusial untuk mencegah serangan pengunggahan berkas berbahaya (*Malicious File Upload / Web Shell*). |
| 5 | Ubah `max:5120` menjadi `max:5`, unggah berkas 1 MB | Berkas 1 MB akan gagal terunggah akibat kesalahpahaman satuan, di mana `max:5` dibaca sistem sebagai batas 5 Kilobyte (KB). | **Berkas berukuran 1 MB langsung digagalkan oleh validasi Laravel dengan pesan error melebihi ukuran.** Kesalahan umum pengembang adalah mengira angka pada `max` berkas adalah Megabyte. Padahal pada Laravel, validasi ukuran berkas dihitung dalam satuan **Kilobyte (KB)**. Menuliskan `max:5` membatasi ukuran menjadi hanya 5 KB (5.120 byte), sehingga berkas 1 MB (1.024 KB) dianggap jauh melampaui ambang batas. Untuk mengizinkan berkas sampai dengan 5 MB, aturan yang benar wajib dituliskan `max:5120` ($5 \times 1024$). |  
| 6 | Set `upload_max_filesize=1M` di php.ini, unggah berkas 3 MB dengan validasi `max:5120` | Batasan konfigurasi dari lingkungan server PHP (`upload_max_filesize`) akan mencegat berkas terlebih dahulu sebelum kode validasi Laravel sempat dijalankan. | **Berkas 3 MB ditolak langsung oleh runtime PHP sebelum sampai ke tahap validasi Laravel.** Walaupun aturan di FormRequest sudah disetel `max:5120` (5 MB), berkas langsung dipotong di tingkat server karena nilai `upload_max_filesize = 1M` (atau default 2M di `D:\php 8.5\php.ini`). Akibatnya, array `$_FILES` kosong dan Laravel gagal memprosesnya, menghasilkan pesan galat *"The file failed to upload"*. Percobaan ini membuktikan bahwa konfigurasi server (`upload_max_filesize` dan `post_max_size`) bertindak sebagai batas terluar yang selalu mengesampingkan validasi kode aplikasi. |
| 7 | Hapus `updateOrCreate`, ganti `create`, kumpulkan tugas dua kali | Terjadi error pada database (*Integrity Constraint Violation*) saat mahasiswa mengumpulkan tugas untuk kedua kalinya karena adanya indeks unik komposit. | **Server mengalami error penolakan basis data `Integrity constraint violation (Duplicate entry)` pada pengumpulan kedua.** Saat logika diganti menjadi `Submission::create(...)`, aksi pengumpulan ulang gagal karena basis data menolak adanya baris baru dengan kombinasi pasangan `assignment_id` dan `user_id` yang sama (`QueryException: Duplicate entry for key 'submissions_assignment_id_user_id_unique'`). Hal ini membuktikan efektivitas indeks `$table->unique(['assignment_id', 'user_id'])` pada migrasi Minggu 3 yang bertindak sebagai lapis pertahanan terakhir integritas data (*Defense-in-Depth*). Oleh karena itu, controller wajib memanfaatkan `updateOrCreate()` agar pengumpulan tugas ulang memperbarui data yang sudah ada tanpa menyebabkan crash pada aplikasi. |  
| 8 | Hapus satu submission dari database lewat SQL langsung, cek folder storage | File fisik akan tetap tersisa di disk server tanpa pemilik (*orphaned file*) jika record dihapus langsung lewat query database. | **Baris data di database berhasil terhapus, namun file fisik tetap tertinggal di disk privat sebagai berkas yatim (*orphaned file*).** Saat data submission (misal ID 126) dieksekusi langsung lewat perintah `DB::table('submissions')->where('id', 126)->delete()`, record di tabel SQLite lenyap seketika, namun berkas `submissions/8/hDTP2vpRQdGGzVpLOuHbQBnZ9m9sPSNDjdEiMsD3.pdf` masih tersimpan di folder disk privat. Hal ini disebabkan eksekusi SQL mentah maupun `cascadeOnDelete` di basis data tidak pernah memicu event model Eloquent (`static::deleting` di `Submission.php`). Eksperimen ini menegaskan pentingnya menangani pembersihan berkas fisik secara terprogram (misal via `$submission->delete()` atau `Storage::delete()`) agar tidak menimbulkan penumpukan sampah data (*storage leakage*). |

---