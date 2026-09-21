## Nama: Aan Mardiah
## NIM:10241001

### READ
1. Baris mana di routes/web.php yang menangkapnya?
Jawab:Untuk route `/tentang`, saya dapat menemukannya pada file `routes/web.php`. Baris yang menangkap URL `/tentang` adalah `Route::get('/tentang', function () { return view('tentang'); });`. Route tersebut menggunakan method `GET` untuk menerima akses ke halaman `/tentang`, kemudian mengarahkan pengguna ke view `tentang.blade.php`.

2. Kalau ditangani controller, berkas dan method mana?
Jawab:Pada my Laravel app ini `route /tentang` saat ini tidak ditangani oleh controller, melainkan langsung menggunakan sebuah fungsi yang langsung ditulis di dalam route, tanpa membuat controller terpisah. di dalam `routes/web.php`. Route tersebut ditulis `Route::get('/tentang', function () { return view('tentang'); });`, sehingga ketika pengguna mengakses /tentang, Laravel langsung menampilkan view `tentang.blade.php` yang berada di `resources/views`. Jika route tersebut ditangani oleh controller, penulisannya akan menjadi seperti `Route::get('/tentang', [TentangController::class, 'index']);`. Dalam hal ini, route akan mengarahkan permintaan ke berkas `app/Http/Controllers/TentangController.php`, tepatnya pada method `index()`, yang kemudian dapat berisi return view('tentang'); untuk menampilkan halaman tersebut.Penggunaan controller bertujuan untuk memisahkan logic aplikasi dari route sehingga kode menjadi lebih terorganisir dan mudah dikembangkan. karena kalau aplikasinya makin besar dan route-nya banyak, menaruh semua logic langsung di web.php akan membuat file route berantakan. Controller membuat logic tersebut lebih terorganisir dan mudah dikembangkan.

3. View mana yang dikembalikan? Di path apa persisnya?
Jawab:View yang dikembalikan adalah `tentang.blade.php`, yang berada di path `My-Laravel-app\resources\views\tentang.blade.php`. View tersebut dipanggil melalui kode `return view('tentang');` pada route `/tentang`. Laravel otomatis mencari file `tentang.blade.php` di dalam folder `resources/views`, sehingga pada kode cukup ditulis `view('tentang')` tanpa menuliskan `.blade.php`.


4. Layout apa yang membungkusnya?
Jawab:View tentang.blade.php tidak dibungkus oleh layout tertentu. File tersebut merupakan view mandiri yang memiliki struktur HTML lengkap mulai dari <html>, <head>, hingga <body>, sehingga tidak menggunakan @extends untuk mewarisi layout lain.

5. Jalankan php artisan route:list --path=tentang. Cocok dengan analisis Anda?
Jawab:Ya, hasil php artisan route:list --path=tentang sesuai dengan analisis saya karena menunjukkan bahwa route tentang menggunakan method GET dan terdaftar pada routes/web.php, sesuai dengan route yang saya buat sebelumnya yaitu Route::get('/tentang', function () { return view('tentang'); });.



### BREAK
| # | Yang dirusak | Prediksi | Hasil Sebenarnya |
|----|------|-----|-------|
| 1  | Ubah `Route::get` menjadi `Route::post` pada route daftar mata kuliah  | error | Error 405 karena method post tidak diperuntukkan mengakses halaman website |
| 2  | Ubah nama view di `return view(...)` menjadi yang tidak ada | Tidak bisa menampilkan halaman website | View(bibah) not found karena nama file yang di panggil tidak ada  |
| 3  | Hapus `->name('courses.show')`, lalu muat halaman yang memakai `route('courses.show')` | error | Route [courses.show] not defined Karena route tersebut sudah tidak memiliki nama courses.show, sehingga route('courses.show') tidak dapat ditemukan oleh Laravel.   |
| 4  | Pindahkan `/courses/{course}` ke ATAS `/courses/create`, lalu buka `/courses/create` | error | eror 404 Not Found |
| 5  | Ganti `{{ $nama }}` menjadi `{!! $nama !!}`, isi `$nama` dengan `<script>alert('XSS')</script>` | Akan eror karena baris tersebut akan terbaca sebagai kode | Menampilkan notifikasi xss |
| 6  | Hapus `@vite(...)` dari layout | Halaman website akan berantakan | Halaman website menjadi terlihat sangat polos tanpa ada nya sentuhan css |
| 7  | Hentikan `npm run dev` lalu muat ulang halaman | Website tidak bisa di akses | Halaman error dan menampilkan ViteManifestNotFoundExceptionyang menunjukkan bahwa aset tidak dapat dimuat |
| 8  | Panggil `route('courses.show')` tanpa mengirim parameter | eror | Eror 500 karena parameter course-nya yang hilang. |