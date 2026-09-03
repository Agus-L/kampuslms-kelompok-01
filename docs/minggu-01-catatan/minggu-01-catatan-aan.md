## Nama: Aan Mardiah
## NIM:10241001
### READ

1. Hal yang dilakukan oleh public/index.php yaitu saat pengguna mengirimkan perintah atau request untuk membuka sebuah url, public/index.php akan menerima request dari pengguna. public/index.php bertugas sebagai pihak pertama yang bekerja untuk menjalankan sebuah request itu dimulai dengan mengecek apakah website atau aplikasi yang ingin dibuka oleh pengguna mengalami maintenance atau tidak. Kemudian public/index.php menjalankan bootstrap/app.php untuk menyiapkan sistem laravel agar siap menerima request, dan yang terakhir Request::capture() menangkap semua data yang diketik atau dikirim pengunjung dari browser (URL, form, header, dll.) lalu $app->handleRequest(...) mengolah data tersebut ke route dan controller yang sesuai, lalu mengirimkan hasil tampilannya kembali ke layar pengunjung

2. Saat public/index.php menerima request dari pengguna, dia akan menjalankan sistem kerja yang sudah disiapkan oleh bootstrap/app.php, jadi bootstrap/app.php  ini berfungsi untuk menyiapkan sistem kerja aplikasi tersebut untuk mengerjakan permintaan yang di minta oleh pengguna. nah didalam bootstrap/app.php itu ada route, middleware, dan exception. route itu yang mengarahkan request tersebut itu harus di tangani oleh siapa, middleware itu berfungsi untuk memeriksa si pengguna(memvalidasi pengguna), sedangkan  exception itu fungsinya sebagai bagian penanganan masalah ketika terjadi sesuatu yang tidak berjalan sebagaimana mestinya selama pengerjaan request. nah waktu route udah ngarahin ke sebuah file file itu akan ada "seseorang" yang akan mengerjakan request dari pengguna entah itu controller, views atau file lain. Didalam file bootstrap/app.php, terdapat:
    ```php
    ->withRouting(
            web: __DIR__.'/../routes/web.php',
            commands: __DIR__.'/../routes/console.php',
            health: '/up',
    ``` 
    Fungsinya mengatur pendaftaran alur jalur (routing) di aplikasi.

    ```php
        ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ``` 
    Fungsinya sebagai tempat untuk mendaftarkan, mengatur, atau mengubah middleware (lapisan penyaring request).

    ```php
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ```
    Fungsinya sebagai tempat untuk menangani eror (exception handling). Di dalam fungsi ini bisa menentukan cara aplikasi merespons saat eror terjadi, misalnya kustomisasi tampilan halaman 404/500, melaporkan eror ke layanan eksternal, atau menyembunyikan jenis eror tertentu

3. D:\kampuslms-kelompok-01\My-Laravel-app\resources\views\welcome.blade.php baris 55 terdapat header bertuliskan "Let's get started", ketika tulisannya diubah tampilan di websitenya juga akan berubah

4. perbandingan isi php artisan route:list dan routes/web.php adalah

    php artisan route:list 
    ```php
    GET|HEAD  / ............................................................................ routes/web.php:5
    GET|HEAD  storage/{path} storage.local › vendor/laravel/framework/src/Illuminate/Filesystem/FilesystemSe…
    PUT       storage/{path} storage.local.upload › vendor/laravel/framework/src/Illuminate/Filesystem/Files…
    GET|HEAD  up vendor/laravel/framework/src/Illuminate/Foundation/Configuration/ApplicationBuilder.php:219

                                                                                            Showing [4] routes
    ```

    routes/web.php
    ```php
    <?php

    use Illuminate\Support\Facades\Route;

    Route::get('/', function () {
        return view('welcome');
    });
    ```

    Perbedaan diantara keduanya adalah berkas routes/web.php itu cuma nampung rute yang kita tulis sendiri secara manual, contohnya rute /. kalau kita jalankan perintah php artisan route:list, terminal bakal nampilin seluruh rute yang aktif di aplikasi. Ini mencakup rute manual buatan kita tadi, dan rute bawaan dari sistem Laravel sendiri—seperti rute health check /up dan rute pengolahan storage.

