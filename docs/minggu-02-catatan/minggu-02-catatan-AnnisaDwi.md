# Minggu 2

**Mata Kuliah:** Pemograman Web A <br>
**Nama:** Annisa Dwi Lestari Sonny <br>
**Tanggal:** 03/09/2026

---

## Read

1. Baris mana di routes/web.php yang menangkapnya?

**Jawaban:** ada di route/web.php bagian:

```php
Route::get('/tentang', function () {
    return view('tentang');
})->name('tentang');
```

2. Kalau ditangani controller, berkas dan method mana?

**Jawaban:** tidak menggunakan controller. Route langsung mengembalikan view melalui `view('tentang').`

3. View mana yang dikembalikan? Di path apa persisnya?

**Jawaban:** View: `resources/views/tentang.blade.php`, dengan path lengkap:
`resources/views/tentang.blade.php`

4. Layout apa yang membungkusnya?

**Jawaban:** Layout: menggunakan komponen `<x-layout>`, yaitu:
`resources/views/components/layout.blade.php`

5. Jalankan php artisan route:list --path=tentang. Cocok dengan analisis Anda?

**Jawaban:** Saat dijalankan di terminal saya didapat
```php
GET|HEAD  tentang ........................ tentang › routes/web.php:14
```
menampilkan route GET /tentang dengan nama tentang dan tidak memiliki controller karena ditangani langsung oleh Closure di web.php. Jadi, sesuai dengan analisis di atas.

---

### Break
| Yang Dirusak | Prediksi | Hasil Sebenarnya |
| :--- | :--- | :--- |
|Ubah Route::get menjadi Route::post pada route daftar mata kuliah |Akan terjadi error karena route hanya menerima POST, sedangkan akses daftar menggunakan GET.|Muncul 405 Method Not Allowed.
|Ubah nama view di return view(...) menjadi yang tidak ada|Halaman akan error karena view yang dipanggil tidak ditemukan.|Muncul View [] not found / error 500.
|Hapus ->name('courses.show'), lalu muat halaman yang memakai route('courses.show')|Halaman yang menggunakan route('courses.show') akan error karena nama route sudah dihapus.|Muncul Route [courses.show] not defined.
|Pindahkan /courses/{course} ke ATAS /courses/create, lalu buka /courses/create|/courses/create akan dianggap sebagai nilai {course}, sehingga masuk ke route detail dan dapat menghasilkan 404.|Masuk ke route show dan menghasilkan 404 Not Found.
|Ganti `{{ $nama }}` menjadi `{!! $nama !!}`, isi `$nama` dengan `<script>alert('XSS')</script>`|Script akan dianggap sebagai teks biasa / tidak dijalankan.|Muncul alert XSS di layar.
|Hapus `@vite(...)` dari layout|CSS/JS tidak akan termuat dengan benar.|Aset tidak termuat, tampilan menjadi tidak ter-style.
|Hentikan npm run dev lalu muat ulang halaman|Halaman akan mengalami masalah pada aset Vite.|Aset dari dev server tidak tersedia; hasil berbeda dengan kondisi setelah npm run build.
|Panggil route('courses.show') tanpa mengirim parameter|Akan error karena parameter {course} tidak diberikan.|Muncul Missing required parameter.
---




