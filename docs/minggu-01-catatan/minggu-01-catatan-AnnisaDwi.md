# Minggu 1

**Mata Kuliah:** Pemograman Web A <br>
**Nama:** Annisa Dwi Lestari Sonny <br>
**Tanggal:** 02/09/2026

---

## Read

1. Buka `public/index.php.` Baca dari atas ke bawah. Tulis dalam 3 kalimat apa yang dilakukan berkas ini.

**Jawaban:** Berkas itu merupakan pintu masuk utama Laravel yang dimana request semua pengguna saat membuka website pertama kali masuk. Berkas tersebut menyiapkan laravel agar dapat berjalan dengan memuat Composer. Setelah itu Laravel memproses request dari pengguna dan memberikan respone kepada pengguna `$app->handleRequest(...)`

2. Buka `bootstrap/app.php.` Identifikasi bagian mana yang mengurus route, mana yang mengurus middleware, mana yang mengurus exception.

**Jawaban:** ada 3 yaitu:
* `withRouting`: mengatur route
* `withMiddleware`: mengatur middleware
* `withExceptions`: mengatur error

3. Buka `routes/web.php.` Temukan route yang menghasilkan halaman selamat datang. Ubah teksnya, muat ulang browser, pastikan berubah.

**Jawaban:** Bisa di lihat pada `routes/web.php` dimana terdapat route yang digunain untuk menampilkan halaman welcome, yaitu

```php
Route::get('/', function () {
    return view('welcome');
});
```
Dari welcome saya menggantikan menjadi "hallo". Setelah file disimpan dan browser direferesh, terdapat tampilan "hallo"

4. Jalankan `php artisan route:list.` Cocokkan keluarannya dengan isi `routes/web.php.`

**Jawaban:** 

hasil terminal:
```text  
GET|HEAD  / .................. routes/web.php:5
  GET|HEAD  storage/{path} storage.local › vendo…
  PUT       storage/{path} storage.local.upload …
  GET|HEAD  up vendor/laravel/framework/src/Illu…
```
routes/web.php:
```text
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
```

---

### Break



