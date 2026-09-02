# Catatan Minggu 1
## Identitas Mahasiswa
Nama    : Agus Liberty Purba

NIM : 10241005

## Read
1. `public/index.php` merupakan berkas awal masuknya seluruh request pada broswer dan harus melewati berkas ini. Berkas ini menyiapkan tiga fondasi utama, yaitu mengecek apakah web sedang dalam perbaikan, memuat autoloader agar semua kelas dapat dipakai, dan menyalakan aplikasi laravel melalalui `bootstrap/app/php`
2. Pembagian tanggungjawab `bootstrap/app.php`.
    - `withRouting()`: Mendaftarkan rute-rute 
    - `withMiddleware()`: Mendaftarkan middleware 
    - `withExceptions()`: Mendaftarkan exceptions 
3. Belum terjadi perubahan karena tampilan masih menampilkan laman deploy
4. Hasilnya sudah sesuai dibuktikan dengan:
    - terminal saya
    ```
    GET|HEAD  / .......................................................................................... routes/web.php:5
    GET|HEAD  storage/{path} storage.local › vendor/laravel/framework/src/Illuminate/Filesystem/FilesystemServiceProvider.…
    PUT       storage/{path} storage.local.upload › vendor/laravel/framework/src/Illuminate/Filesystem/FilesystemServicePr…
    GET|HEAD  up .............. vendor/laravel/framework/src/Illuminate/Foundation/Configuration/ApplicationBuilder.php:219 
    ``` 
    - Kalau di `routes/web.php`
    ```php
    <?php

    use Illuminate\Support\Facades\Route;

    Route::get('/', function () {
        return view('welcome');
    });

    ```

## Break
| Yang dirusak | Prediksi Sebelum Mencoba | Pesan Error yang sebenarnya |
| :--- | :--- | :--- |
| Ganti nama `.env` menjadi `.env.bak` | Laravel tidak akan berjalan karena env utama berubah nama| `filemtime(): stat failed for C:\Semester_5\Proweb\My-Laravel-app\.env`|
| Kosongkan nilai `APP_KEY` di `.env`| Error akan muncul di web dengan menampilkan file path error | `Illuminate\Encryption\MissingAppKeyException` , `vendor\laravel\framework\src\Illuminate\Encryption\EncryptionServiceProvider.php:83` |
| 	Ubah `DB_DATABASE` menjadi nama yang tidak ada | menampilkan pesan error bahwa key ke databasenya tidak terdeteksi | `Illuminate\Database\QueryException` |
| Ubah `APP_DEBUG=false`, lalu ulangi nomor 3 | Session tidak akan berjalan | laman web menampilkan error dengan kode 500|