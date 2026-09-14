## Nama: Aliya Labibah
## NIM:10241007

### READ
1. Baris mana di routes/web.php yang menangkapnya?
jawab : terdapat Baris yang menangkapnya adalah baris 7 sampai 9 pada kode Route::get('/tentang', function () { return view('tentang'); });. Baris ini bertugas menunggu permintaan dari browser dengan metode GET di alamat /tentang, lalu langsung menjalankan instruksi langsung di dalamnya untuk menyajikan berkas tentang.blade.php.


2. Kalau ditangani controller, berkas dan method mana?
Jawab : ada kode saat ini, halaman /tentang tidak menggunakan controller sama sekali karena perintahnya ditulis langsung di file routes/web.php memakai fungsi tanpa nama (perintah instan). Namun, jika nantinya dialihkan menggunakan controller, berkas yang bertugas adalah TentangController.php di folder app/Http/Controllers/, dengan fungsi (method) bernama index(). Perubahannya, bagian perintah instan function () { ... } di file routes akan digantikan oleh [TentangController::class, 'index'] yaitu kode yang memberitahu Laravel untuk menyerahkan seluruh aksi penanganan ke controller tersebut. Alurnya, begitu browser mengakses /tentang, route akan mengoper tugasnya ke fungsi `index()` di dalam TentangController, lalu fungsi itulah yang bertugas mengeksekusi dan memanggil berkas tampilan tentang.blade.php.

3. View mana yang dikembalikan? Di path apa persisnya?
jawab : Mengembalikan view bernama tentang. Path filenya secara pasti berada di:
resources/views/tentang.blade.php


4. Layout apa yang membungkusnya?
Jawab:View `tentang.blade.php` dibuat sebagai view yang berdiri sendiri dan tidak menggunakan layout dari file lain. Di dalamnya sudah terdapat struktur HTML lengkap, mulai dari `<html>`, `<head>`, hingga `<body>`. Oleh karena itu, file ini tidak perlu menggunakan `@extends` karena tidak mengambil struktur dari layout lain.


5. Jalankan php artisan route:list --path=tentang. Cocok dengan analisis Anda?
jawab : Hasilnya sangat cocok dengan analisis. Saat perintah php artisan route:list --path=tentang dijalankan, terminal berhasil menyaring tampilan agar hanya memunculkan rincian untuk route /tentang saja. Keterangan routes/web.php:9 membuktikan bahwa permintaan halaman tersebut diproses langsung di file routes baris ke-9 menggunakan perintah instan (closure) tanpa controller.


### BREAK
| # | Yang dirusak | Prediksi | Hasil Sebenarnya |
|----|------|-----|-------|
| 1  | Ubah `Route::get` menjadi `Route::post` pada route daftar mata kuliah  | error karena menggunakan method yang salah | menampilkan error 405 |
| 2  | Ubah nama view di `return view(...)` menjadi yang tidak ada | menampilkan tulisan bahwa file tampilan/view tersebut tidak ditemukan |Layar error page Laravel akan muncul dengan tulisan InvalidArgumentException dan View  view(...) not found |
| 3  | Hapus `->name('courses.show')`, lalu muat halaman yang memakai `route('courses.show')` | menampilkan pesan error karena Laravel tidak bisa menemukan nama rute 'courses.show' yang dipanggil di file view |menampilkan error Route [courses.show] not defined. Fungsi route() bekerja berdasarkan nama alias rute, bukan alamat URL-nya. Jadi saat ->name() dihapus, Laravel bingung rute mana yang sedang dipanggil.|
| 4  | Pindahkan `/courses/{course}` ke ATAS `/courses/create`, lalu buka `/courses/create` | halaman tidak ditemukan | Halaman menampilkan error 404 Not Found. Karena rute dibaca dari atas ke bawah, kata 'create' dikira ID {course} |
| 5  | Ganti `{{ $nama }}` menjadi `{!! $nama !!}`, isi `$nama` dengan `<script>alert('XSS')</script>` | XSS-nya berhasil lolos dan berjalan  | menampilkan notifikasi bertuliskan "xss" |
| 6  | Hapus `@vite(...)` dari layout | Tampilan web akan polos tanpa warna | Tampilan web berubah total menjadi HTML polos tanpa warna, font, maupun tata letak. |
| 7  | Hentikan `npm run dev` lalu muat ulang halaman | browser tidak bisa di akses | Halaman error dan menampilkan ViteManifestNotFoundExceptionyang menunjukkan bahwa aset tidak dapat dimuat |
| 8  | Panggil `route('courses.show')` tanpa mengirim parameter | error karena data ID yang dibutuhkan oleh rute tersebut kosong. | menampilkan error UrlGenerationException: Missing required paramete |