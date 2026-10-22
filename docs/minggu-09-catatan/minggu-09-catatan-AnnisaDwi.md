# Minggu 9

**Mata Kuliah:** Pemograman Web A <br>
**Nama:** Annisa Dwi Lestari Sonny <br>
**Tanggal:** 20/10/2026

---

## Read

### 1. Di mana lokasi fisik berkas materi yang baru diunggah tersimpan dalam sistem berkas, dan bagaimana path lengkapnya?

Berkas materi fisik tersimpan pada direktori privat aplikasi, tepatnya di path lengkap `storage\app\private\materials\courses\{course_id}\[hash_acak].pdf` (pada lingkungan lokal: `D:\Dwi\Kuliah\Semester 5\Pemrograman Web\Kelompok\kampuslms-kelompok-01\kampuslms-kelompok-01\storage\app\private\materials\courses\1\[hash_acak].pdf`). Nama fisik berkas digenerasi secara acak oleh method `$file->store()` demi mencegah konflik nama file serta menangkal ancaman keamanan seperti *path traversal*.

### 2. Bagaimana perbandingan antara data kolom `file_path` pada tabel database dengan alamat fisik berkas di komputer?

Pada tabel `materials` di database, kolom `file_path` hanya menyimpan path relatif terhadap root disk penyimpanan lokal, contohnya `materials/courses/1/[hash_acak].pdf`. Perbedaan dengan path fisik absolut sistem operasi ini sengaja diterapkan sebagai *best practice* agar data aplikasi tetap fleksibel dan portabel saat dipindahkan antar lingkungan server tanpa harus mengubah isi database.

### 3. Apa hasil yang diperoleh saat mencoba membuka berkas tersebut secara langsung melalui pengetikan URL di browser, dan apa alasannya?

Percobaan akses langsung dengan menebak URL di browser menghasilkan respon `404 Not Found`. Hal ini terjadi karena berkas ditempatkan pada disk privat (`storage/app/private/`) yang tidak memiliki rute langsung dari web server publik, sehingga pengunduhan berkas wajib melalui controller resmi yang dilindungi oleh otorisasi `MaterialPolicy`.

### 4. Berdasarkan berkas `config/filesystems.php`, apa perbedaan mendasar antara konfigurasi disk `local` dan disk `public`?

Disk `local` memiliki direktori root di `storage_path('app/private')` dan tidak memiliki konfigurasi `url`, menjadikannya murni privat sehingga seluruh akses harus dialirkan melalui controller backend. Sebaliknya, disk `public` berakar pada `storage_path('app/public')` dengan entri `url => env('APP_URL').'/storage'`, yang memang disiapkan agar berkasnya dapat diakses secara statis dan langsung oleh browser umum melalui symbolic link.

### 5. Apa fungsi yang dihasilkan oleh perintah `php artisan storage:link`, dan mengapa berkas materi kuliah dilarang disimpan di dalamnya?

Perintah `php artisan storage:link` berfungsi membuat symbolic link dari direktori `public/storage` menuju `storage/app/public` agar aset publik dapat dimuat oleh peramban. Berkas materi kuliah dilarang keras disimpan di folder tersebut karena berkas di dalamnya dapat diunduh bebas oleh siapa pun tanpa proses login maupun validasi hak akses Policy, yang berisiko membocorkan dokumen hak cipta dosen maupun materi pembelajaran ke pihak luar.

---

### Break

| Yang Dirusak | Prediksi | Hasil Sebenarnya |
| :--- | :--- | :--- |
| Pindahkan berkas submission ke `storage/app/public`, akses URL-nya tanpa login | Berkas tugas mahasiswa dapat diunduh bebas oleh siapa saja tanpa perlu login ke sistem. | Berkas berhasil diakses langsung via URL browser publik (`/storage/submissions/...`) tanpa melewati autentikasi maupun Policy. |
| Hapus `Gate::authorize()` dari endpoint download, akses submission orang lain | Mahasiswa dapat mengunduh submission milik rekan sekelasnya hanya dengan mengganti parameter ID pada URL. | Terjadi celah IDOR; file tugas mahasiswa lain berhasil terunduh (200 OK) karena tidak adanya verifikasi hak kepemilikan. |
| Simpan berkas dengan `storeAs()` memakai nama asli, unggah berkas bernama `../../test.txt` | File berpotensi tersimpan keluar dari subfolder tujuan dan menembus struktur folder lain (*path traversal*). | File keluar dari folder semestinya, membuktikan nama berkas input pengguna tidak boleh dipercaya dan wajib diacak. |
| Unggah berkas PHP yang diganti namanya jadi `.pdf` dengan validasi `extensions`, lalu dengan `mimes` | Aturan `extensions` meloloskan file berbahaya tersebut, sedangkan aturan `mimes` menolaknya berdasarkan isi. | Validasi `extensions:pdf` tertipu oleh nama berkas, sementara `mimes:pdf` sukses memblokir file karena membaca *magic bytes*. |
| Ubah `max:5120` menjadi `max:5`, unggah berkas 1 MB | Unggahan berkas 1 MB langsung ditolak karena parameter `max` pada validasi Laravel bersatuan kilobyte. | Muncul pesan error validasi ukuran berkas; nilai `max:5` membatasi ukuran maksimal hanya 5 KB (bukan 5 MB). |
| Set `upload_max_filesize=1M` di php.ini, unggah berkas 3 MB dengan validasi `max:5120` | Berkas langsung ditolak di tingkat server web/PHP sebelum sempat diproses oleh validasi form Laravel. | Unggahan langsung gagal di level runtime PHP tanpa pesan validasi aplikasi, membuktikan batas server mengalahkan batas framework. |
| Hapus `updateOrCreate`, ganti `create`, kumpulkan tugas dua kali | Sistem menolak pengumpulan kedua karena melanggar batasan kunci unik composite di tabel database. | Terjadi crash database *Integrity constraint violation (Duplicate entry)* pada kolom kombinasi `(assignment_id, user_id)`. |
| Hapus satu submission dari database lewat SQL langsung, cek folder storage | Record di database terhapus namun file fisik di storage tertinggal menjadi berkas sampah (*orphaned file*). | Baris database terhapus bersih, tetapi berkas fisik tetap tertinggal di folder storage privat karena event `deleting` tidak jalan. |

---
