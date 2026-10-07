## Nama: Aan Mardiah
## NIM:10241001

### READ
1. Jalankan php artisan install:api. Baca perubahan yang terjadi di bootstrap/app.php.    
Jawab: Sebelum `install:api`, Laravel hanya punya routing `web`. Setelah dijalankan, baris `api: routes/api.php` ditambahkan, artinya semua route di `routes/api.php` otomatis dapat prefix `/api`.

2. Buat satu endpoint GET /api/v1/courses sederhana.
Jawab: File: `routes/api.php` (Baris 28 & 41), File: `app/Http/Controllers/Api/V1/CourseController.php` (Baris 30–45)

3. Bandingkan dengan CourseController versi web yang sudah ada. Tulis di catatan: apa yang sama dan apa yang berbeda di antara keduanya?   
Jawab:   

    | Aspek | **Web** [`CourseController.php`]| **API** [`Api/V1/CourseController.php`] |
    |---|---|---|
    | **Namespace** | `App\Http\Controllers` | `App\Http\Controllers\Api\V1` |
    | **Return value** | `return view(...)` (HTML) | `return new CourseCollection(...)` (JSON) |
    | **Method yang ada** | `index, create, store, show, edit, update, destroy` | Hanya `index` dan `show` |
    | **Filter pencarian** | Pakai query string `?q=` dan baca dari `session()` | Tidak ada filter session |
    | **Scope akses** | Semua course ditampilkan (admin-centric) | Dibatasi by role: dosen lihat MK sendiri, mahasiswa lihat MK yang diikuti |
    | **Autentikasi** | Pakai session login (web guard) | Pakai Bearer Token Sanctum |
    | **Format error** | Redirect + flash message | JSON dengan status code HTTP |


4. Panggil endpoint API tanpa header Accept: application/json. Lalu dengan header itu. Catat bedanya.   
Jawab:
Perbedaan saat memanggil endpoint `GET /api/v1/courses` via curl:

    a. **Tanpa header `Accept: application/json`:**
    - Perintah: `curl -i http://127.0.0.1:8000/api/v1/courses`
    - Hasil: Laravel memperlakukan request seperti browser web biasa. Karena belum login, Laravel merespons dengan **HTTP 302 Redirect** menuju URL halaman login (`/login`).

    b. **Dengan header `Accept: application/json`:**
    - Perintah: `curl -i -H "Accept: application/json" http://127.0.0.1:8000/api/v1/courses`
    - Hasil: Laravel mendeteksi bahwa klien meminta format JSON (`$request->expectsJson()`). Laravel tidak melakukan redirect, melainkan mengembalikan response API **HTTP 401 Unauthorized** berupa JSON:
        `{"message": "Unauthenticated."}`



5. Jalankan php artisan route:list --path=api. Cocokkan dengan kontrak di spesifikasi.   
Jawab:    
```php
  POST            api/v1/assignments ........ Api\V1\AssignmentController@store
  PUT|PATCH       api/v1/assignments/{assignment} Api\V1\AssignmentController@…
  DELETE          api/v1/assignments/{assignment} Api\V1\AssignmentController@…
  GET|HEAD        api/v1/assignments/{assignment}/submissions Api\V1\Assignmen…  
  POST            api/v1/assignments/{assignment}/submissions Api\V1\Assignmen…  
  POST            api/v1/auth/login ............... Api\V1\AuthController@login  
  POST            api/v1/auth/logout ............. Api\V1\AuthController@logout  
  GET|HEAD        api/v1/courses ................ Api\V1\CourseController@index  
  GET|HEAD        api/v1/courses/{course} ........ Api\V1\CourseController@show  
  GET|HEAD        api/v1/courses/{course}/assignments Api\V1\CourseAssignmentC…  
  GET|HEAD        api/v1/courses/{course}/materials Api\V1\CourseMaterialContr…  
  GET|HEAD        api/v1/me .......................... Api\V1\AuthController@me  
  GET|HEAD        api/v1/notifications .... Api\V1\NotificationController@index  
  POST            api/v1/notifications/{id}/read Api\V1\NotificationController…  
  PUT             api/v1/submissions/{submission}/grade Api\V1\SubmissionGrade…  

                                                            Showing [15] routes  

```


### BREAK

| # | Yang dicoba | Prediksi | Hasil Sebenarnya |
| --- | --- | --- | --- | 
| 1 | Kembalikan `response()->json(User::all())` di satu endpoint uji | Mengembalikan semua data user dalam format JSON |Seluruh data tabel database diekspos secara mentah ke publik tanpa disaring API Resource, termasuk metadata internal (`email_verified_at`, `deleted_at`, `created_at`). Saat `$hidden` dinonaktifkan, string hash password bcrypt (`$2y$12$...`) seluruh akun ikut tampil telanjang di respons JSON, menciptakan risiko kebocoran kredensial yang fatal|
| 2 | Hapus `auth:sanctum` dari grup route, panggil tanpa token | 	Endpoint privat dapat diakses secara bebas oleh siapa saja tanpa perlu autentikasi/token (menjadi endpoint publik). |Request tanpa token tidak lagi dicegat status 401 Unauthorized. Server mengembalikan status 200 OK dan seluruh daftar mata kuliah beserta relasi dosen pengampu langsung tampil terbuka ke publik tanpa perlu login.|
| 3 | Panggil endpoint terlindungi dengan token yang sudah dihapus | Server akan menolak request dan mengembalikan status 401 karena token sudah tidak valid. | Setelah logout, token dihapus dari tabel `personal_access_tokens`. Saat token yang sama dipakai kembali, Sanctum tidak menemukan record-nya di database dan langsung mengembalikan status `401 Unauthorized` (`{"message": "Unauthenticated."}`). |
| 4 | Login sebagai mahasiswa, panggil `POST` `/api/v1/assignments` | Permintaan ditolak karena mahasiswa tidak berhak membuat tugas; mengembalikan status 403 Forbidden (bukan 401 Unauthorized). | Server meloloskan autentikasi token Sanctum (tidak kena 401), namun menolak aksi saat pengecekan otorisasi role di controller dengan status `403 Forbidden` (`{"message": "Anda tidak memiliki akses ke sumber daya ini."}`). Ini membuktikan perbedaan autentikasi (identitas valid) dan otorisasi (hak akses peran ditolak). |
| 5 | Hapus eager loading, panggil daftar mata kuliah, lihat Telescope/Debugbar | Response JSON tetap 200 OK dan data tetap tampil, namun terjadi masalah N+1 query di database di mana jumlah query melonjak drastis. | Response API tetap sukses 200 OK dengan format data yang sama, tetapi ketiadaan `with('lecturer')` memicu lazy loading saat `CourseResource` mengakses `$this->lecturer`. Database mengeksekusi 1 query untuk mengambil daftar courses ditambah N query individual untuk mengambil data dosen setiap baris (terjadi lonjakan query SQL N+1 yang membebani database). |
| 6 | Hapus `throttle` dari login, jalankan 50 percobaan berturut-turut | Semua percobaan login terus diproses tanpa batas waktu atau pemblokiran, berisiko terkena serangan brute force. | Tanpa middleware `throttle:login`, seluruh percobaan login terus menghasilkan respon status 422 Unprocessable Content tanpa pernah dicegat oleh status 429 Too Many Requests. Sistem terbuka lebar terhadap serangan brute force dan credential stuffing karena tidak ada batasan frekuensi percobaan tebak kata sandi. | 
| 7 | Buat pesan login berbeda untuk email salah vs password salah  | Sistem akan memberitahu apakah email terdaftar atau tidak, menimbulkan celah keamanan User Enumeration. | Saat pesan dibedakan, sistem secara terang-terangan membocorkan keberadaan akun: jika email tidak ada muncul pesan 'email tidak terdaftar', sedangkan jika email valid muncul 'password salah'. Ini sangat berbahaya karena penyerang dapat memvalidasi ribuan daftar email civitas kampus (User Enumeration) untuk kemudian ditargetkan serangan brute force terfokus maupun penipuan spear-phishing. Standar OWASP mewajibkan pesan kesalahan login disatukan secara generik ('kredensial salah'). |


