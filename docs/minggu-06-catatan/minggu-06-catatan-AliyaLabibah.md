# Nama: Aliya Labibah
## NIM: 10241007

## READ — Bandingkan dua jalur

### 1. Jalankan `php artisan install:api`. Baca perubahan yang terjadi di `bootstrap/app.php`.

Di Laravel 12, fitur API tidak langsung aktif dari awal. Perintah `php artisan install:api` dijalankan untuk mengaktifkannya.

**Perubahan di `bootstrap/app.php`:**
Pada bagian `withRouting()`, Laravel otomatis menambahkan satu baris baru:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',   // <-- Baris ini yang baru bertambah
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

**Penjelasan:**
Baris tersebut membuat file `routes/api.php` jadi aktif. Semua endpoint yang ditulis di file itu otomatis punya awalan alamat URL `/api` dan berjalan tanpa session/cookie (bersifat *stateless*).

---

### 2. Buat satu endpoint `GET /api/v1/courses` sederhana.

Endpoint ini dibuat untuk menampilkan daftar mata kuliah.

* **Di `routes/api.php`:**
  ```php
  Route::prefix('v1')->group(function () {
      Route::middleware('auth:sanctum')->get('/courses', [CourseController::class, 'index']);
  });
  ```

* **Di controller (`app/Http/Controllers/Api/V1/CourseController.php`):**
  Controller mengecek siapa yang sedang login:
  * Kalau Dosen: mengambil mata kuliah yang dia ajar (`taughtCourses`).
  * Kalau Mahasiswa: mengambil mata kuliah yang dia ikuti (`courses`).
  
  Datanya kemudian dibungkus menggunakan `CourseCollection` agar hasil keluarannya berupa format JSON rapi lengkap dengan info halaman (*pagination*).

---

### 3. Bandingkan dengan CourseController versi web yang sudah ada. Apa yang sama dan apa yang berbeda di antara keduanya?

* **Yang Sama:**
  * Keduanya sama-sama mengambil data dari database lewat model `Course`.
  * Logikanya sama: membedakan mata kuliah dosen pengampu dan mahasiswa yang terdaftar.
  * Sama-sama membatasi data per halaman menggunakan pagination (`paginate(15)`).

* **Yang Berbeda:**
  1. **Bentuk Keluaran:** Versi Web mengembalikan tampilan halaman HTML via Blade (`view()`), sedangkan versi API mengembalikan data murni berformat JSON via API Resource.
  2. **Cara Login:** Versi Web memakai session dan cookie browser, sedangkan versi API memakai Bearer Token dari Sanctum.
  3. **Fungsi di Controller:** Versi Web punya fungsi untuk menampilkan form input seperti `create()` dan `edit()`, sedangkan versi API tidak butuh fungsi form karena langsung mengolah data.
  4. **Keamanan CSRF:** Versi Web wajib memakai token `@csrf` di setiap form, sedangkan versi API tidak perlu token CSRF.

---

### 4. Panggil endpoint API tanpa header `Accept: application/json`. Lalu dengan header itu. Catat bedanya!

* **Tanpa `Accept: application/json`:**
  Kalau kita panggil API tanpa membawa token atau saat belum login, Laravel mengira kita membukanya lewat browser biasa. Akibatnya, Laravel mencoba melempar kita (redirect 302) ke halaman login web. Kalau halaman login web-nya tidak siap, yang muncul malah tampilan error berupa halaman HTML.

* **Dengan `Accept: application/json`:**
  Laravel langsung sadar bahwa yang memanggil adalah aplikasi/klien API. Jadi saat kita belum login, Laravel tidak melakukan redirect, melainkan langsung membalas dengan status **401 Unauthorized** berbentuk JSON murni:
  ```json
  {
    "message": "Unauthenticated."
  }
  ```
  Begitu juga kalau input salah, langsung keluar pesan error validasi **422** dalam format JSON tanpa merender halaman web.

---

### 5. Jalankan `php artisan route:list --path=api`. Cocokkan dengan kontrak di spesifikasi.

Saat perintah `php artisan route:list --path=api` dijalankan di terminal, muncul total **15 route** aktif:

```text
POST        api/v1/auth/login
POST        api/v1/auth/logout
GET|HEAD    api/v1/me
GET|HEAD    api/v1/courses
GET|HEAD    api/v1/courses/{course}
GET|HEAD    api/v1/courses/{course}/materials
GET|HEAD    api/v1/courses/{course}/assignments
POST        api/v1/assignments
PUT|PATCH   api/v1/assignments/{assignment}
DELETE      api/v1/assignments/{assignment}
GET|HEAD    api/v1/assignments/{assignment}/submissions
POST        api/v1/assignments/{assignment}/submissions
PUT         api/v1/submissions/{submission}/grade
GET|HEAD    api/v1/notifications
POST        api/v1/notifications/{id}/read
```

**Hasil Pencocokan:**
Semua 15 endpoint yang tampil di terminal sudah **cocok 100%** dengan tabel kontrak di berkas `spek-week6.md`. Semuanya sudah memakai awalan `/api/v1`, metodenya sesuai (GET, POST, PUT, DELETE), dan endpoint yang butuh login sudah dilindungi oleh Sanctum (`auth:sanctum`).

## BREAK — Tujuh kerusakan

| # | Yang dirusak | Prediksi | Hasil Sebenarnya |
| --- | --- | --- | --- |
| 1 | Kembalikan `response()->json(User::all())` di satu endpoint uji. Lalu hapus sementara `$hidden` dari model `User` dan ulangi. | Email dan seluruh kolom pengguna akan tampil di layar saat endpoint dipanggil. Ketika properti `$hidden` dihapus, atribut sensitif seperti hash password juga ikut tampil ke publik/klien API. | **Muncul seluruh data 34 pengguna beserta hash password di terminal.** Saat endpoint `GET /api/v1/me` diubah menjadi `User::all()`, respon tidak lagi menampilkan 1 profil pengguna yang login, melainkan membeberkan seluruh daftar user lengkap dengan email, NIM/NIP, dan role. Ketika baris `$hidden` di model `User.php` dimatikan sementara, kolom `password` yang berisi hash bcrypt (seperti `$2y$12$DgSttwO0nCQiwzB...`) ikut bocor keluar. Percobaan ini membuktikan bahayanya mengembalikan model Eloquent mentah ke API dan menegaskan pentingnya peran **API Resource** serta properti `$hidden` untuk mencegah kebocoran data sensitif (*Information Disclosure*). |
| 2 | Hapus `auth:sanctum` dari grup route, panggil tanpa token | Data terbuka untuk publik | **Seluruh data mata kuliah berhasil ditarik tanpa token (terbuka untuk publik).** Sebelum dihapus, request tanpa token otomatis dicegat oleh Sanctum dengan respon 401 Unauthorized. Setelah `auth:sanctum` dicopot, endpoint `/api/v1/courses` dapat dipanggil secara bebas oleh siapa saja dan mengembalikan seluruh daftar mata kuliah beserta dosen pengampunya. Ini membuktikan bahwa tanpa middleware autentikasi, API kehilangan perlindungan utamanya (*Broken Authentication / Unauthorized Access*).|
| 3 | Panggil endpoint terlindungi dengan token yang sudah dihapus | 401 | **Muncul respon status 401 Unauthorized (Ditolak).** Begitu kita melakukan logout, sistem otomatis menghapus "kunci masuk" (token) dari database. Ketika kita mencoba membuka kembali `/api/v1/me` menggunakan token lama tersebut, server langsung menolaknya dengan tulisan `HTTP/1.1 401 Unauthorized` dan pesan `{"message": "Unauthenticated."}`. Ini membuktikan bahwa saat pengguna logout, kunci lamanya benar-benar hangus dan tidak bisa lagi dipakai untuk menyusup masuk.|
| 4 | Login sebagai mahasiswa, panggil `POST /api/v1/assignments` | Harus 403, bukan 401 — periksa punya Anda | **Muncul status HTTP 403 Forbidden (Akses Ditolak).** Mahasiswa berhasil login dan membawa token yang sah, sehingga ia lolos pemeriksaan pintu masuk (tidak kena 401). Namun, saat ia mencoba membuat tugas baru, controller langsung menolaknya dengan kode **403 Forbidden** dan pesan `{"message":"Anda tidak memiliki akses ke sumber daya ini."}` karena membuat tugas adalah wewenang dosen. Ini membuktikan perbedaan tegas antara **Autentikasi (401: Siapa Anda? Identitas sah)** dan **Otorisasi (403: Boleh atau tidak Anda melakukan aksi ini?)**. |
| 5 | Hapus eager loading, panggil daftar mata kuliah, lihat Telescope/Debugbar | N+1 di API | Data JSON tetap muncul normal tanpa error (200 OK), tetapi database bekerja boros (terjadi masalah N+1). Di layar semuanya terlihat sukses, padahal di balik layar Laravel diam-diam bolak-balik menembak database: 1 kali untuk mengambil daftar mata kuliah, lalu ditambah tembakan baru satu per satu untuk mencari dosen tiap mata kuliah. Percobaan ini membuktikan bahwa tanpa with('lecturer'), aplikasi memang tidak error, tetapi database dipaksa bekerja berkali-kali lipat sehingga server menjadi berat dan lambat saat datanya banyak. |
| 6 | Hapus throttle dari login, jalankan 50 percobaan berturut-turut | Brute force tanpa hambatan | **Seluruh 50 percobaan login diproses terus-menerus tanpa ada blokir (lolos brute force).** Server terus melayani setiap tebakan password yang salah dengan respon status 422 Unprocessable Content, tanpa pernah mengeluarkan status proteksi **429 Too Many Requests**. Jika pembatas laju (*throttle*) dimatikan, sistem tidak memiliki rem pengaman, sehingga penyerang atau bot bisa menebak password ribuan kali per menit secara bebas (*Brute Force / Credential Stuffing*).|
| 7 | Buat pesan login berbeda untuk email salah vs password salah | User enumeration — kenapa ini berbahaya | **Muncul pesan error spesifik yang membocorkan status keberadaan akun (User Enumeration).** Saat dicoba dengan email acak muncul pesan `"Email tidak terdaftar"`, sedangkan saat dicoba dengan email valid tapi password salah muncul pesan `"Password salah"`. Perbedaan ini **sangat berbahaya** karena penyerang bisa memetakan dan menyortir email mana saja yang terdaftar di database kampus. Setelah mengetahui daftar akun yang valid, penyerang bisa menargetkan akun-akun tersebut untuk serangan tebak password (*brute force*) atau penipuan (*phishing*). Standar keamanan mengharuskan pesan error login selalu ambigu (misal: *"Kredensial tidak cocok"*)|