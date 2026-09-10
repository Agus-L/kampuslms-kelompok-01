 ## Nama : Aliya Labibah
 ## NIM : 10241007
 
 ### READ
 
 1. public/index.php merupakan titik awal pemrosesan request pada aplikasi Laravel. Berkas ini melakukan beberapa persiapan, seperti mengecek maintenance, memuat kebutuhan aplikasi, dan menjalankan konfigurasi dari bootstrap/app.php. Setelah itu, request dari browser ditangkap dan diserahkan kepada aplikasi Laravel untuk diproses lebih lanjut.

 2. bootstrap/app.php digunakan untuk menyiapkan dan mengatur konfigurasi utama aplikasi Laravel. Application::configure() memulai konfigurasi aplikasi, kemudian withRouting() mengatur route, yaitu aturan yang menentukan request dari URL tertentu akan diarahkan ke proses atau halaman mana; withMiddleware() mengatur middleware, yaitu bagian yang memeriksa atau memproses request sebelum diteruskan; dan withExceptions() mengatur exception, yaitu penanganan error atau kesalahan yang terjadi saat aplikasi berjalan. Setelah konfigurasi selesai, create() membuat aplikasi Laravel agar siap memproses request dari browser.
 
 3. Pada file routes/web.php terdapat route yang digunakan untuk menampilkan halaman Welcome, yaitu:

    ```php 
    Route::get('/', function () {
    return view('welcome');
    });
    ```

    Dari view('welcome'), halaman yang ditampilkan berasal dari file resources/views/welcome.blade.php. Jadi, saya membuka file tersebut dan mengubah teks “Let's get started” pada baris 55 menjadi “Selamat Datang di Website Saya”. Setelah disimpan dan browser di-refresh, teks pada halaman Welcome berhasil berubah.

4. Perbedaan di antara keduanya adalah routes/web.php hanya berisi rute yang kita tulis atau atur sendiri, contohnya rute / pada baris 5. Sedangkan ketika menjalankan perintah php artisan route:list, terminal akan menampilkan seluruh rute yang aktif di aplikasi. Jadi, hasilnya tidak hanya menampilkan rute yang kita buat sendiri, tetapi juga rute bawaan Laravel, seperti rute health check /up dan rute untuk pengolahan storage.