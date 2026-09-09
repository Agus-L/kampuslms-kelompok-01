# Catatan Minggu 2
## Identitas Mahasiswa
Nama : Anastasya Salsabila Khoirunnisa

NIM  : 10241009

## A. READ — Menelusuri Request `/tentang`

Membuka halaman `/tentang`.

Kegiatan ini bertujuan untuk memahami bagaimana Laravel menerima request dari pengguna, mencocokkannya dengan route, memproses request tersebut, hingga akhirnya menampilkan halaman kepada pengguna.

### 1. Route yang menangkap `/tentang`

Route untuk halaman `/tentang` berada pada file:

```text
routes/web.php
```

Route tersebut menggunakan method `GET` karena halaman `/tentang` digunakan untuk menampilkan informasi kepada pengguna.

Ketika pengguna membuka alamat:

```text
/tentang
```

Laravel akan mencari route yang sesuai di dalam file `routes/web.php`.

Laravel memeriksa route berdasarkan urutan penulisannya dari atas ke bawah. Route pertama yang sesuai dengan request akan digunakan untuk memproses request tersebut.

Dengan demikian, route `/tentang` merupakan bagian yang menghubungkan alamat URL yang diminta pengguna dengan halaman yang akan ditampilkan.

### 2. Controller yang menangani `/tentang`

Berdasarkan route yang digunakan pada halaman `/tentang`, route tersebut langsung mengembalikan sebuah view dan tidak diteruskan ke controller.

Oleh karena itu, halaman `/tentang` tidak menggunakan controller dan method khusus untuk menampilkan halaman tersebut.

Alur prosesnya dapat dijelaskan melalui beberapa tahap:

1. Pengguna membuka `/tentang`.
2. Laravel menerima request `GET`.
3. Laravel mencari route yang sesuai di `routes/web.php`.
4. Route `/tentang` ditemukan.
5. Route langsung mengembalikan view.
6. Laravel menampilkan halaman `/tentang` kepada pengguna.

### 3. View yang dikembalikan

View yang digunakan untuk halaman `/tentang` berada pada:

```text
resources/views/tentang.blade.php
```

File tersebut berisi tampilan halaman **Tentang Proyek & Tim Pengembang**.

Di dalam file tersebut terdapat struktur halaman HTML seperti:

- `<!DOCTYPE html>`
- `<html>`
- `<head>`
- `<body>`
- navbar
- bagian informasi proyek
- informasi tim pengembang

File tersebut menggunakan ekstensi `.blade.php`, yang menunjukkan bahwa file tersebut merupakan Blade View pada Laravel.

Blade digunakan untuk membuat tampilan halaman dan dapat menggunakan berbagai sintaks Blade di dalam HTML.

### 4. Layout yang membungkus halaman

Pada halaman `/tentang`, struktur HTML dituliskan langsung di dalam file:

```text
resources/views/tentang.blade.php
```

Halaman tersebut belum menggunakan komponen layout bersama seperti:

```text
resources/views/components/layout.blade.php
```

atau pemanggilan:

```blade
<x-layout>
```

Artinya, struktur HTML dan navbar pada halaman `/tentang` masih berada di dalam file view tersebut.

Pada Minggu 2, penggunaan layout dan komponen Blade dipelajari agar bagian halaman yang digunakan berulang, seperti navbar dan struktur HTML, dapat dibuat di satu tempat dan digunakan kembali oleh halaman lain.

Dengan menggunakan layout, kode menjadi lebih terstruktur dan mengurangi penulisan kode yang sama secara berulang.

### 5. Hasil `php artisan route:list --path=tentang`

Untuk memeriksa route yang berkaitan dengan `/tentang`, digunakan perintah:

```bash
php artisan route:list --path=tentang
```

Perintah tersebut digunakan untuk menampilkan route yang memiliki hubungan dengan kata `tentang`.

Hasil dari perintah tersebut kemudian dibandingkan dengan route yang terdapat pada:

```text
routes/web.php
```

Dari hasil tersebut dapat diketahui informasi seperti:

- Method HTTP yang digunakan.
- URL atau URI route.
- Nama route jika tersedia.
- Action yang digunakan oleh route.

Hasil yang diperoleh perlu disesuaikan dengan hasil yang muncul pada proyek masing-masing.

---

## B. BREAK — Delapan Kerusakan

Pada bagian BREAK, dilakukan delapan perubahan yang sengaja dibuat untuk menyebabkan masalah pada aplikasi.

Sebelum menjalankan aplikasi, saya membuat prediksi mengenai apa yang akan terjadi setelah setiap perubahan dilakukan.

Tujuan kegiatan ini adalah untuk memahami hubungan antara route, method HTTP, view, keamanan data, Vite, dan helper `route()` pada Laravel.

### Tabel Prediksi dan Hasil BREAK

| No. | Perubahan yang Dilakukan | Prediksi Sebelum Dijalankan | Hasil Setelah Dijalankan | Kesimpulan |
|---|---|---|---|---|
| 1 | Mengubah `Route::get` menjadi `Route::post` pada route daftar mata kuliah. | Halaman tidak dapat dibuka secara normal melalui browser karena browser melakukan request `GET`, sedangkan route hanya menerima `POST`. | Muncul error `405 Method Not Allowed`. | Method HTTP pada route harus disesuaikan dengan jenis request yang dilakukan. |
| 2 | Mengubah nama view pada `return view(...)` menjadi nama view yang tidak tersedia. | Laravel tidak dapat menemukan view yang diminta. | Muncul error karena view tidak ditemukan. | Nama view pada `return view()` harus sesuai dengan file yang tersedia di `resources/views`. |
| 3 | Menghapus `->name('courses.show')` dari route kemudian tetap menggunakan `route('courses.show')`. | Laravel tidak dapat menemukan route yang memiliki nama `courses.show`. | Muncul error `Route [courses.show] not defined`. | Nama route harus didefinisikan apabila digunakan melalui helper `route()`. |
| 4 | Menempatkan `/courses/{course}` sebelum `/courses/create`. | Laravel dapat menganggap `create` sebagai nilai dari parameter `{course}`. | URL `/courses/create` dapat masuk ke route detail course. | Urutan route penting karena Laravel menggunakan route pertama yang cocok. |
| 5 | Mengubah `{{ $nama }}` menjadi `{!! $nama !!}` dan memberikan nilai `<script>alert('XSS')</script>`. | Isi variabel akan diproses sebagai HTML mentah sehingga script dapat dijalankan oleh browser. | Muncul alert `XSS`. | Data yang tidak dipercaya tidak boleh langsung ditampilkan menggunakan `{!! !!}` karena dapat menimbulkan XSS. |
| 6 | Menghapus `@vite(...)` dari layout. | Aset CSS dan JavaScript yang terhubung melalui Vite tidak dimuat sebagaimana mestinya. | Tampilan atau fungsi yang membutuhkan aset dapat mengalami masalah. | `@vite` digunakan untuk menghubungkan aset aplikasi dengan Vite. |
| 7 | Menghentikan `npm run dev` kemudian memuat ulang halaman. | Development server Vite tidak lagi berjalan sehingga aset yang membutuhkan server tersebut dapat tidak diproses seperti sebelumnya. | Perubahan atau aset selama proses pengembangan tidak berjalan seperti ketika development server aktif. | `npm run dev` digunakan selama proses pengembangan, sedangkan `npm run build` digunakan untuk membangun aset. |
| 8 | Memanggil `route('courses.show')` tanpa memberikan parameter course. | Laravel membutuhkan parameter untuk membuat URL detail course. | Muncul error `Missing required parameter`. | Route yang memiliki parameter wajib harus diberikan parameter ketika dipanggil. |

### Kesimpulan BREAK

Dari delapan percobaan tersebut, dapat dipahami bahwa perubahan kecil pada kode Laravel dapat menyebabkan hasil yang berbeda pada aplikasi.

Beberapa hal yang dapat dipelajari adalah:

1. Method HTTP harus sesuai dengan kebutuhan route.
2. Nama view harus sesuai dengan file view yang tersedia.
3. Nama route harus didefinisikan apabila digunakan melalui helper `route()`.
4. Urutan route memengaruhi route mana yang akan digunakan Laravel.
5. Data yang tidak dipercaya harus ditampilkan dengan cara yang aman.
6. Penggunaan `{!! !!}` pada data yang tidak dipercaya dapat menyebabkan XSS.
7. `@vite` berperan dalam menghubungkan aset CSS dan JavaScript dengan Vite.
8. `npm run dev` dan `npm run build` memiliki fungsi yang berbeda.
9. Route yang memiliki parameter wajib harus diberikan parameter ketika dipanggil.

---

## C. FIX — Memperbaiki Repo yang Bermasalah

Pada bagian FIX, digunakan branch `w02` pada repository:

```text
kampuslms-broken
```

Repository tersebut berisi enam masalah yang sengaja dibuat untuk ditemukan dan diperbaiki.

Masalah yang harus ditemukan terdiri dari:

1. Satu masalah pada route yang saling menutupi karena urutan route.
2. Satu masalah pada method HTTP.
3. Dua URL yang masih ditulis secara hardcode.
4. Satu masalah keamanan XSS.
5. Satu logika query yang diletakkan di dalam view.

### Langkah Perbaikan

#### 1. Memeriksa kode

Langkah pertama adalah mencari masalah yang terdapat pada repository.

Pemeriksaan dilakukan pada bagian route, controller, view, dan bagian lain yang berkaitan dengan enam masalah tersebut.

#### 2. Memperbaiki urutan route

Route yang lebih spesifik harus diletakkan sebelum route yang menggunakan parameter umum.

Contohnya:

```text
/courses/create
/courses/{course}
```

Dengan urutan tersebut, `/courses/create` akan dikenali sebagai route khusus dan tidak dianggap sebagai nilai dari `{course}`.

#### 3. Memperbaiki method HTTP

Method HTTP harus disesuaikan dengan tujuan request.

Contohnya:

- `GET` digunakan untuk mengambil atau menampilkan data.
- `POST` digunakan untuk membuat atau mengirim data.
- `PUT` atau `PATCH` digunakan untuk memperbarui data.
- `DELETE` digunakan untuk menghapus data.

Penggunaan method yang tepat membantu menjaga struktur dan keamanan aplikasi.

#### 4. Mengubah URL hardcode

URL yang ditulis secara langsung sebaiknya diganti dengan helper:

```blade
route()
```

Contohnya:

```blade
<a href="{{ route('courses.index') }}">
    Mata Kuliah
</a>
```

Dengan menggunakan nama route, kode menjadi lebih mudah dikelola apabila alamat URL mengalami perubahan.

#### 5. Memperbaiki masalah XSS

Data yang tidak dipercaya tidak boleh langsung ditampilkan menggunakan:

```blade
{!! $data !!}
```

Untuk menampilkan data biasa, digunakan:

```blade
{{ $data }}
```

Sintaks `{{ }}` melakukan escape terhadap karakter HTML sehingga lebih aman untuk data yang berasal dari pengguna.

#### 6. Memindahkan logika query dari view

View seharusnya berfokus pada tampilan.

Logika untuk mengambil atau mengolah data sebaiknya ditempatkan pada controller atau bagian backend yang sesuai.

Dengan demikian, view hanya menerima data yang sudah disiapkan untuk ditampilkan.

#### 7. Melakukan pengujian kembali

Setelah semua masalah diperbaiki, aplikasi dijalankan kembali untuk memastikan:

1. Route dapat diakses.
2. Method HTTP sudah sesuai.
3. URL menggunakan helper `route()`.
4. Data ditampilkan dengan aman.
5. Query tidak lagi dilakukan di dalam view.
6. Tidak terdapat error baru setelah perbaikan.

#### 8. Membuat Pull Request

Setelah perbaikan selesai, perubahan dikirim ke repository GitHub melalui branch yang digunakan.

Kemudian dibuat Pull Request agar perubahan dapat diperiksa oleh anggota kelompok lainnya sebelum digabungkan ke branch utama.

Deskripsi Pull Request menjelaskan:

- Masalah yang ditemukan.
- Risiko atau dampak dari masalah tersebut.
- Perbaikan yang dilakukan.

---

## D. BUILD — Membuat Kerangka KampusLMS

Pada bagian BUILD, dibuat kerangka awal aplikasi KampusLMS.

Pada tahap ini, data mata kuliah masih menggunakan data statis berupa array dan belum menggunakan database.

Tujuan tahap ini adalah menghubungkan route, controller, dan view dalam sebuah aplikasi Laravel sederhana.

### 1. Membuat Layout `x-layout`

Layout digunakan untuk menyediakan struktur halaman yang dapat digunakan kembali oleh beberapa halaman.

Navbar pada layout berisi:

1. Dashboard.
2. Mata Kuliah.
3. Tentang.

Struktur file yang digunakan adalah:

```text
resources/views/
├── components/
│   └── layout.blade.php
├── courses/
│   ├── index.blade.php
│   └── show.blade.php
└── dashboard.blade.php
```

Dengan menggunakan layout, struktur HTML dan navbar tidak perlu ditulis berulang kali pada setiap halaman.

Contoh penggunaan layout:

```blade
<x-layout>
    Isi halaman
</x-layout>
```

### 2. Membuat `CourseController`

Controller dibuat untuk menangani halaman mata kuliah.

Controller memiliki dua method utama:

```text
index
show
```

Method `index` digunakan untuk menampilkan daftar mata kuliah.

Method `show` digunakan untuk menampilkan detail satu mata kuliah.

Pada tahap ini, data mata kuliah masih menggunakan array statis sehingga belum mengambil data dari database.

Contoh data sederhana:

```php
$courses = [
    [
        'code' => 'SI101',
        'name' => 'Sistem Informasi',
        'sks' => 3,
        'lecturer' => 'Nama Dosen'
    ],
];
```

Data tersebut kemudian dikirim dari controller menuju view.

### 3. Membuat Halaman Daftar Mata Kuliah

File yang digunakan:

```text
resources/views/courses/index.blade.php
```

Halaman ini digunakan untuk menampilkan daftar mata kuliah.

Informasi yang ditampilkan minimal terdiri dari:

1. Kode mata kuliah.
2. Nama mata kuliah.
3. SKS.
4. Dosen.

Data dapat ditampilkan dalam bentuk tabel.

Contoh struktur:

```blade
<table>
    <thead>
        <tr>
            <th>Kode</th>
            <th>Nama Mata Kuliah</th>
            <th>SKS</th>
            <th>Dosen</th>
        </tr>
    </thead>
</table>
```

### 4. Membuat Halaman Detail Mata Kuliah

File yang digunakan:

```text
resources/views/courses/show.blade.php
```

Halaman ini digunakan untuk menampilkan informasi lebih lengkap mengenai satu mata kuliah.

Halaman detail dapat menampilkan:

1. Kode mata kuliah.
2. Nama mata kuliah.
3. Jumlah SKS.
4. Nama dosen.
5. Informasi tambahan yang tersedia pada data.

Halaman detail diakses berdasarkan parameter course pada route.

### 5. Menggunakan Helper `route()`

Semua tautan pada aplikasi sebaiknya menggunakan helper:

```blade
route()
```

Contohnya:

```blade
<a href="{{ route('courses.index') }}">
    Mata Kuliah
</a>
```

Untuk halaman detail:

```blade
<a href="{{ route('courses.show', $course) }}">
    Lihat Detail
</a>
```

Penggunaan `route()` membuat kode lebih mudah dipelihara dibandingkan menulis URL secara langsung.

Contoh URL hardcode:

```blade
<a href="/courses/1">
```

Contoh menggunakan nama route:

```blade
<a href="{{ route('courses.show', $course) }}">
```

Jika URL route berubah, penggunaan nama route membuat perubahan lebih mudah dikelola.

### 6. Membuat Custom 404

Halaman khusus untuk kondisi halaman tidak ditemukan dibuat pada:

```text
resources/views/errors/404.blade.php
```

Halaman tersebut digunakan ketika pengguna meminta alamat yang tidak tersedia.

Custom 404 dapat memberikan tampilan yang lebih jelas kepada pengguna bahwa halaman yang diminta tidak ditemukan.

### 7. Commit dan Review

Dalam pengerjaan BUILD, minimal dua anggota kelompok harus membuat commit yang berbeda.

Hal ini bertujuan agar setiap anggota memiliki kontribusi dalam repository.

Setelah perubahan dibuat, perubahan dikirim ke repository dan dapat dibuat Pull Request.

Pull Request kemudian harus diperiksa atau direview oleh anggota kelompok lain sebelum perubahan digabungkan ke branch utama.

Dengan demikian, proses pengerjaan tidak hanya berfokus pada pembuatan kode, tetapi juga pada proses kerja menggunakan Git dan GitHub secara bersama-sama.

---

## E. CHECKPOINT MINGGU 2

### 1. Kenapa menghapus data lewat `GET` berbahaya? Beri satu skenario konkret.

Menghapus data menggunakan `GET` berbahaya karena method `GET` seharusnya digunakan untuk mengambil atau menampilkan data, bukan untuk melakukan tindakan penghapusan.

Contohnya terdapat URL:

```text
/courses/5/delete
```

Jika URL tersebut menggunakan method `GET`, maka ketika URL tersebut diakses, proses penghapusan dapat langsung dijalankan.

Hal tersebut berbahaya karena pengguna dapat mengakses URL tersebut secara tidak sengaja.

Selain itu, request `GET` dapat dilakukan oleh berbagai proses yang hanya bermaksud mengakses halaman.

Oleh karena itu, operasi penghapusan sebaiknya menggunakan method:

```text
DELETE
```

Dengan demikian, tindakan penghapusan dapat dibedakan dari request yang hanya bertujuan untuk melihat data.

### 2. Apa yang terjadi kalau `/courses/{course}` ditulis sebelum `/courses/create`? Kenapa?

Jika route:

```text
/courses/{course}
```

diletakkan sebelum:

```text
/courses/create
```

maka Laravel dapat menganggap `create` sebagai nilai dari parameter:

```text
{course}
```

Hal tersebut terjadi karena Laravel membaca route dari atas ke bawah dan menggunakan route pertama yang cocok.

Contohnya ketika pengguna membuka:

```text
/courses/create
```

Laravel dapat mencocokkannya dengan:

```text
/courses/{course}
```

sehingga:

```text
course = create
```

Akibatnya, route `/courses/create` tidak mendapatkan kesempatan untuk digunakan.

Oleh karena itu, route yang lebih spesifik sebaiknya diletakkan terlebih dahulu:

```text
/courses/create
/courses/{course}
```

### 3. Tunjukkan di kode Anda satu tempat yang memakai `route()`. Apa keuntungannya dibanding URL hardcode?

Salah satu contoh penggunaan `route()` adalah:

```blade
<a href="{{ route('courses.show', $course) }}">
    Lihat Detail
</a>
```

Penggunaan `route()` memiliki beberapa keuntungan:

1. Tidak perlu menuliskan URL secara langsung.
2. Kode menjadi lebih mudah dibaca.
3. Jika URL berubah, tidak perlu mengubah URL hardcode di banyak tempat.
4. Laravel akan membuat URL berdasarkan nama route yang sudah didefinisikan.

Contoh URL hardcode:

```blade
<a href="/courses/5">
```

Contoh menggunakan `route()`:

```blade
<a href="{{ route('courses.show', $course) }}">
```

Cara kedua lebih mudah dikelola ketika aplikasi memiliki banyak halaman.

### 4. Apa beda `{{ }}` dan `{!! !!}`? Peragakan XSS yang Anda buat di bagian BREAK.

`{{ }}` digunakan untuk menampilkan data dengan melakukan escape terhadap HTML.

Contohnya:

```blade
{{ $nama }}
```

Jika `$nama` berisi:

```html
<script>alert('XSS')</script>
```

maka isi tersebut tidak diperlakukan sebagai script yang harus dijalankan, tetapi ditampilkan sebagai teks yang sudah di-escape.

Sedangkan:

```blade
{!! $nama !!}
```

menampilkan isi variabel sebagai HTML mentah.

Jika `$nama` berisi:

```html
<script>alert('XSS')</script>
```

maka browser dapat menjalankan script tersebut.

Hal tersebut merupakan contoh:

```text
XSS (Cross-Site Scripting)
```

XSS dapat terjadi ketika data yang tidak dipercaya dimasukkan ke dalam halaman tanpa dilakukan pengamanan yang sesuai.

Oleh karena itu, untuk menampilkan data biasa, lebih aman menggunakan:

```blade
{{ $nama }}
```

Sedangkan `{!! !!}` hanya digunakan ketika isi HTML memang diperlukan dan sumber datanya sudah dipastikan aman.

### 5. Apa fungsi `@vite`? Apa beda `npm run dev` dan `npm run build`?

`@vite` digunakan untuk menghubungkan aset aplikasi seperti CSS dan JavaScript dengan Vite.

Contohnya:

```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

Aset tersebut kemudian dapat diproses oleh Vite.

#### `npm run dev`

Perintah:

```bash
npm run dev
```

digunakan ketika melakukan pengembangan aplikasi.

Perintah tersebut menjalankan development server Vite sehingga perubahan pada CSS atau JavaScript dapat diproses selama proses pengembangan.

#### `npm run build`

Perintah:

```bash
npm run build
```

digunakan untuk melakukan proses build terhadap aset aplikasi.

Secara sederhana:

1. `npm run dev` digunakan saat proses pengembangan.
2. `npm run build` digunakan untuk membuat hasil build aset.

Keduanya memiliki tujuan yang berbeda sesuai dengan tahap penggunaan aplikasi.

### 6. Jelaskan mengapa data dari `Request` tidak boleh dipercaya.

Data yang berasal dari `Request` tidak boleh langsung dipercaya karena data tersebut berasal dari pengguna.

Contohnya:

```php
public function index(Request $request)
{
    $keyword = $request->query('q');
}
```

Nilai `q` berasal dari input pengguna.

Pengguna dapat mengirimkan nilai yang tidak sesuai dengan yang diharapkan aplikasi.

Oleh karena itu, data dari `Request` harus diperiksa dan divalidasi sebelum digunakan.

Hal ini penting agar aplikasi tidak menerima dan memproses data secara sembarangan.

Prinsip yang digunakan adalah:

1. Data diterima dari pengguna.
2. Data dianggap belum terpercaya.
3. Data diperiksa atau divalidasi.
4. Data yang sudah sesuai baru digunakan oleh aplikasi.

Dengan cara tersebut, aplikasi dapat mengurangi risiko kesalahan maupun masalah keamanan akibat input pengguna.

---

## F. KESIMPULAN MINGGU 2

Pada Minggu 2, saya mempelajari hubungan antara **Route, Controller, dan View** dalam aplikasi Laravel.

Route berfungsi menentukan alamat URL dan method HTTP yang digunakan untuk menerima request. Controller digunakan untuk menangani proses dan menyiapkan data yang diperlukan oleh halaman, sedangkan View digunakan untuk menampilkan data kepada pengguna.

Saya juga mempelajari bahwa Laravel membaca route berdasarkan urutan sehingga route yang lebih spesifik perlu diperhatikan penempatannya. Penggunaan method HTTP juga harus disesuaikan dengan tujuan request, seperti `GET` untuk mengambil data dan `DELETE` untuk penghapusan.

Selain itu, saya memahami perbedaan antara penggunaan `{{ }}` dan `{!! !!}` pada Blade. `{{ }}` melakukan escape terhadap output sehingga lebih aman untuk data yang tidak dipercaya, sedangkan `{!! !!}` menampilkan HTML mentah dan dapat menimbulkan risiko XSS apabila digunakan pada data yang tidak aman.

Penggunaan helper `route()` juga dipelajari sebagai cara yang lebih baik dibandingkan menuliskan URL secara hardcode. Dengan helper tersebut, hubungan antara link dan route menjadi lebih mudah dikelola.

Pada bagian BUILD, saya mempelajari penggunaan layout dan komponen Blade agar struktur halaman dapat digunakan kembali. Saya juga membuat kerangka halaman KampusLMS yang terdiri dari dashboard, daftar mata kuliah, detail mata kuliah, dan halaman tentang.

Selain itu, saya memahami fungsi `@vite`, perbedaan `npm run dev` dan `npm run build`, serta pentingnya menganggap data yang berasal dari `Request` sebagai data yang belum terpercaya dan perlu divalidasi.

Secara keseluruhan, kegiatan Minggu 2 membantu saya memahami bagaimana request dari pengguna dapat diproses oleh Laravel hingga menghasilkan halaman yang ditampilkan pada browser, sekaligus memahami beberapa praktik dasar dalam menjaga struktur, keamanan, dan pemeliharaan aplikasi web.