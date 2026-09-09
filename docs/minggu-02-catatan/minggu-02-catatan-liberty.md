# Catatan Minggu 2
## Identitas Mahasiswa
Nama    : Agus Liberty Purba

NIM : 10241005

## Read
1. View `/tentang ` ditangkap oleh baris `Route::get('/tentang', function () {
    return view('tentang');
})->name('tentang');` 
2. Tidak ditangani oleh controller karena sudah ada di jawban no 1
3. View yang dikembalikan `tentang` dan pathnya `\Semester_5\Proweb\My-Laravel-app\resources\views\tentang.blade.php`
4. Layoutnya tidak dibungkus karena berdiri sendiri.
5. Hasil terminal:
```
 GET|HEAD tentang ............. tentang › routes/web.php:14
``` 

## Break
| Yang Dirusak | Prediksi | Hasil Sebenarnya |
| :--- | :--- | :--- |
| Ubah `Route::get` menjadi `Route::post` pada route daftar mata kuliah | Error karna methodnya salah | Muncul kode error 405 |
| Ubah nama view di `return view(...)` menjadi yang tidak ada | view tentang tidak akan tampil dan akan muncul exception | `View [] not found` dan kode 500. |
| Hapus `->name('courses.show')`, lalu muat halaman yang memakai `route('courses.show')` | Terjadi error karena tidak bisa memuat | Kalau di halaman *detail mata kuliah*, tidak terjadi apa-apa karena sudah memuat id detail mata kuliah. Sedangkan *daftar mata kuliah* tidak bisa dimuat karena route harus mencari dengan nama identitas mata kuliah sehingga menampilkan `Route [courses.show] not defined.`. |
| Pindahkan `/courses/{course}` ke ATAS `/courses/create`, lalu buka /courses/create |Akan terjadi error | Masuk ke halaman teks putih dengan tulisan "Halaman Form Tambah Mata Kuliah". |
| Ganti `{{ $nama }}` menjadi `{!! $nama !!}`, isi `$nama` dengan `<script>alert('XSS')</script>` | Muncul peringatan | Muncul notifikasi XSS |
| Hapus `@vite(...)` dari layout| Tampilan akan menjadi seperti html biasa tanpa hiasan | Muncul error kdoe 500 dan aset tidak dapat dimuat |
| Hentikan npm run dev lalu muat ulang halaman |aset tidak bisa diakses, muncul error 404 | Baris 7, Data 3 |
| Baris 7, Data 1 | Baris 7, Data 2 | Baris 7, Data 3 |
