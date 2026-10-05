# Catatan Minggu 6 — Tasya

Dokumentasi hasil eksperimen dan analisis tugas **PEMROGRAMAN WEB — MINGGU 6: REST API dan Integrasi** pada proyek Laravel 12 KampusLMS.

---

## READ

### READ 1 — `php artisan install:api`
- **Tindakan:**
  Memeriksa eksekusi perintah `php artisan install:api` serta memeriksa berkas konfigurasi `bootstrap/app.php` dan struktur berkas terkait di proyek Laravel 12.
- **Hasil:**
  1. Pada Laravel 12, routing API tidak diaktifkan secara bawaan. Perintah `php artisan install:api` berfungsi menginstal paket `laravel/sanctum`, mempublikasikan migrasi tabel `personal_access_tokens`, membuat berkas `routes/api.php`, dan secara otomatis mendaftarkan route API ke dalam `bootstrap/app.php`.
  2. Pada berkas `bootstrap/app.php`, ditemukan perubahan konfigurasi routing pada blok `withRouting`:
     ```php
     ->withRouting(
         web: __DIR__.'/../routes/web.php',
         api: __DIR__.'/../routes/api.php',
         commands: __DIR__.'/../routes/console.php',
         health: '/up',
     )
     ```
  3. Konfigurasi `api: __DIR__.'/../routes/api.php'` tersebut otomatis memberikan awalan (*prefix*) `/api` pada seluruh endpoint yang didefinisikan di dalamnya.
- **Kesimpulan:**
  Implementasi `install:api` dan Sanctum sudah terpasang dan dikonfigurasi dengan benar oleh tim pengembang sebelumnya. Route API telah terhubung ke aplikasi Laravel 12 melalui `bootstrap/app.php` tanpa perlu mendaftarkan route secara manual di kernel lama (karena `app/Http/Kernel.php` sudah tidak digunakan lagi di Laravel 12).

---

### READ 2 — `GET /api/v1/courses`
- **Tindakan:**
  Menguji pemanggilan endpoint `GET /api/v1/courses` yang sudah tersedia pada [routes/api.php](file:///C:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/routes/api.php) menggunakan akun terotentikasi dosen demo (`dosen@kampuslms.test`).
- **Hasil:**
  1. Endpoint yang digunakan: `GET /api/v1/courses` diproses oleh `App\Http\Controllers\Api\V1\CourseController@index`.
  2. Status HTTP yang diterima: `200 OK`.
  3. Bentuk response JSON yang diterima terbungkus rapi dalam API Resource (`CourseCollection`):
     ```json
     {
       "data": [
         {
           "id": 9,
           "code": "IF444",
           "name": "hkcbhkabc",
           "description": "ckuacb",
           "sks": 5,
           "status": "active",
           "lecturer": {
             "id": 2,
             "name": "Dr. Budi Santoso"
           },
           "created_at": "2026-09-24T06:10:26.000000Z",
           "updated_at": "2026-09-24T06:10:26.000000Z"
         },
         {
           "id": 1,
           "code": "IF101",
           "name": "Algoritma dan Pemrograman",
           "description": "Mata kuliah Algoritma dan Pemrograman...",
           "sks": 3,
           "status": "active",
           "lecturer": {
             "id": 2,
             "name": "Dr. Budi Santoso"
           },
           "created_at": "2026-09-24T00:50:20.000000Z",
           "updated_at": "2026-09-24T00:50:20.000000Z"
         }
       ],
       "meta": {
         "current_page": 1,
         "last_page": 1,
         "total": 3
       }
     }
     ```
- **Kesimpulan:**
  Endpoint `GET /api/v1/courses` berjalan sesuai kontrak `spek-week6.md`. Format response menyertakan koleksi data di dalam `data` serta informasi halaman terstandarisasi di dalam `meta` (`current_page`, `last_page`, `total`), tanpa membocorkan atribut model mentah yang tidak perlu.

---

### READ 3 — Perbandingan CourseController Web dan API
- **Persamaan:**
  1. Keduanya mengakses data dari tabel database yang sama melalui Eloquent Model `Course`.
  2. Keduanya menerapkan eager loading relasi `lecturer` (`with('lecturer')`) untuk mencegah masalah performa N+1 query.
  3. Keduanya menerapkan pembagian akses data berdasarkan peran (*role-based scoping*) pengguna yang sedang login.
- **Perbedaan:**
  1. **Mekanisme Otentikasi & State:**
     - **Web Controller (`app/Http/Controllers/Dosen/CourseController.php` / `CourseController.php`):** Bersifat *stateful*, bergantung pada sesi browser (*session cookie*) dan proteksi CSRF.
     - **API Controller (`app/Http/Controllers/Api/V1/CourseController.php`):** Bersifat *stateless*, mengandalkan Bearer Token Laravel Sanctum pada setiap header permintaan HTTP tanpa CSRF cookie.
  2. **Pengambilan Data & Logika Scoping:**
     - **Web Controller:** Ditujukan untuk navigasi halaman web umum/peran dosen dengan dukungan pencarian string `?q=` dan filter status form HTML.
     - **API Controller:** Mengisolasi logika data secara otomatis berdasarkan token: dosen hanya mendapatkan mata kuliah yang diajarnya (`Course::where('lecturer_id', $user->id)`), sedangkan mahasiswa hanya mendapatkan mata kuliah yang diikutinya melalui relasi pivot (`$user->courses()`).
  3. **Bentuk Nilai Kembalian (Response):**
     - **Web Controller:** Mengembalikan tampilan visual Blade HTML (`return view('courses.index', compact('courses'))`).
     - **API Controller:** Mengembalikan representasi JSON terstruktur menggunakan API Resource (`return new CourseCollection($paginated)`).
- **Kesimpulan:**
  API Controller memisahkan sepenuhnya logika penyajian (*presentation layer*) dari logika penyediaan data (*data layer*). API tidak mempedulikan antarmuka pengguna (Blade/Vue/React), melainkan fokus mengembalikan struktur data JSON yang konsisten, aman, dan dapat dikonsumsi oleh berbagai platform klien.

---

### READ 4 — Header `Accept: application/json`
- **Tanpa header (`Accept` kosong / `*/*`):**
  - Permintaan: `GET http://localhost:8000/api/v1/courses` tanpa token dan tanpa header `Accept`.
  - Status HTTP: `401 Unauthorized`.
  - Body Response:
    ```json
    {
      "message": "Unauthenticated."
    }
    ```
- **Dengan header (`Accept: application/json`):**
  - Permintaan: `GET http://localhost:8000/api/v1/courses` tanpa token dengan header `Accept: application/json`.
  - Status HTTP: `401 Unauthorized`.
  - Body Response:
    ```json
    {
      "message": "Unauthenticated."
    }
    ```
- **Perbedaan:**
  Pada implementasi proyek KampusLMS di berkas `bootstrap/app.php`, tim telah menambahkan pengatur eksepsi global:
  ```php
  $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
      if ($request->is('api/*') || $request->expectsJson()) {
          return response()->json([
              'message' => 'Unauthenticated.',
          ], 401);
      }
  });
  ```
  Karena adanya aturan `$request->is('api/*')`, request tanpa header `Accept: application/json` tetap aman dikembalikan dalam bentuk JSON 401 dan tidak dilempar (redirect) ke halaman login HTML `routes/web.php`.
  Namun, pada aplikasi Laravel standar tanpa konfigurasi `$request->is('api/*')`, memanggil endpoint tanpa `Accept: application/json` akan menyebabkan Laravel mengira permintaan berasal dari browser web biasa, sehingga Laravel akan mencoba melakukan redirect HTTP 302 ke route bernama `login` yang menghasilkan error HTML jika route tersebut tidak ada.
- **Kesimpulan:**
  Header `Accept: application/json` adalah kontrak mutlak bagi klien API agar server selalu merespons dalam format JSON pada segala kondisi (sukses maupun gagal). Konfigurasi proteksi exception di `bootstrap/app.php` proyek ini sangat baik karena mencegah kebocoran HTML pada rute `/api/*`.

---

### READ 5 — `php artisan route:list --path=api`
- **Hasil:**
  Perintah `php artisan route:list --path=api` menampilkan daftar rute berikut:

  | HTTP Method | URI | Controller Action |
  |---|---|---|
  | `POST` | `api/v1/assignments` | `Api\V1\AssignmentController@store` |
  | `PUT\|PATCH` | `api/v1/assignments/{assignment}` | `Api\V1\AssignmentController@update` |
  | `DELETE` | `api/v1/assignments/{assignment}` | `Api\V1\AssignmentController@destroy` |
  | `GET\|HEAD` | `api/v1/assignments/{assignment}/submissions` | `Api\V1\AssignmentSubmissionController@index` |
  | `POST` | `api/v1/assignments/{assignment}/submissions` | `Api\V1\AssignmentSubmissionController@store` |
  | `POST` | `api/v1/auth/login` | `Api\V1\AuthController@login` |
  | `POST` | `api/v1/auth/logout` | `Api\V1\AuthController@logout` |
  | `GET\|HEAD` | `api/v1/courses` | `Api\V1\CourseController@index` |
  | `GET\|HEAD` | `api/v1/courses/{course}` | `Api\V1\CourseController@show` |
  | `GET\|HEAD` | `api/v1/courses/{course}/assignments` | `Api\V1\CourseAssignmentController@index` |
  | `GET\|HEAD` | `api/v1/courses/{course}/materials` | `Api\V1\CourseMaterialController@index` |
  | `GET\|HEAD` | `api/v1/me` | `Api\V1\AuthController@me` |
  | `GET\|HEAD` | `api/v1/notifications` | `Api\V1\NotificationController@index` |
  | `POST` | `api/v1/notifications/{id}/read` | `Api\V1\NotificationController@markAsRead` |
  | `PUT` | `api/v1/submissions/{submission}/grade` | `Api\V1\SubmissionGradeController@update` |

  *(Total: 15 routes)*
- **Kecocokan:**
  Seluruh 15 rute yang terdaftar cocok 100% dengan kontrak spesifikasi pada dokumen `spek-week6.md`.
- **Kesimpulan:**
  Rute API pada proyek telah terdaftar secara lengkap dan terstruktur rapi di bawah prefix `api/v1`, mencakup modul Autentikasi, Profil, Mata Kuliah, Materi, Tugas, Pengumpulan Tugas, Penilaian, hingga Notifikasi.

---

## BREAK

### BREAK 1 — Model Mentah
- **Perubahan:**
  Menguji pengembalian data model Eloquent mentah secara langsung via `response()->json(User::first())` dibandingkan dengan menggunakan API Resource `new UserResource(User::first())`.
- **Pengujian:**
  Mengevaluasi payload JSON yang dihasilkan dari serialisasi objek model `User` secara mentah.
- **Hasil:**
  1. Serialisasi model mentah menghasilkan output berikut:
     ```json
     {
       "id": 1,
       "name": "Admin KampusLMS",
       "email": "admin@kampuslms.test",
       "role": "admin",
       "nim_nip": "ADM000001",
       "email_verified_at": "2026-09-29T07:05:19.000000Z",
       "created_at": "2026-09-24T00:50:10.000000Z",
       "updated_at": "2026-09-29T07:05:19.000000Z",
       "deleted_at": null
     }
     ```
  2. Seluruh kolom tabel database ikut tampil ke publik (termasuk metadata internal `deleted_at`, `email_verified_at`, dan `updated_at`). Jika atribut `$hidden` pada model tidak sengaja terhapus, hash password dan remember token akan ikut keluar.
  3. Sebaliknya, saat menggunakan `UserResource`, data yang keluar disaring secara ketat (*whitelist*):
     ```json
     {
       "id": 1,
       "name": "Admin KampusLMS",
       "email": "admin@kampuslms.test",
       "role": "admin",
       "nim_nip": "ADM000001",
       "created_at": "2026-09-24T00:50:10.000000Z"
     }
     ```
- **Kesimpulan:**
  Mengembalikan model mentah sangat berbahaya karena berisiko membocorkan struktur internal database dan data sensitif pengguna (*data leakage*). API Resource bertindak sebagai gerbang penyaring data (*data transformation layer*) yang memastikan hanya field yang diizinkan yang dikirimkan ke klien.
- **Kondisi setelah dikembalikan:**
  Seluruh endpoint controller tetap menggunakan API Resource (`UserResource`, `CourseResource`, `CourseDetailResource`, dll.) sesuai kondisi semula.

---

### BREAK 2 — Tanpa `auth:sanctum`
- **Perubahan:**
  Mensimulasikan penghapusan middleware `auth:sanctum` dari pembungkus grup route API.
- **Pengujian:**
  Memanggil endpoint privat `GET /api/v1/courses` tanpa menyertakan HTTP Header `Authorization`.
- **Hasil:**
  1. Tanpa `auth:sanctum`: Endpoint dapat diakses secara bebas oleh siapa saja tanpa autentikasi, mengembalikan status `200 OK`. Seluruh daftar data mata kuliah dapat dilihat oleh publik tanpa batas.
  2. Dengan `auth:sanctum`: Akses tanpa token langsung dicegat oleh middleware Sanctum sebelum mencapai controller, menghasilkan status `401 Unauthorized` dengan pesan `{"message": "Unauthenticated."}`.
- **Kesimpulan:**
  Middleware `auth:sanctum` adalah fondasi keamanan utama REST API untuk memverifikasi keabsahan identitas pemanggil melalui Bearer Token.
- **Kondisi setelah dikembalikan:**
  Middleware `auth:sanctum` tetap aktif membungkus seluruh endpoint privat di [routes/api.php](file:///C:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/routes/api.php).

---

### BREAK 3 — Token Tidak Valid
- **Pengujian:**
  Memanggil endpoint yang dilindungi `auth:sanctum` (`GET /api/v1/courses`) dengan menyertakan header token sembarangan/palsu:
  `Authorization: Bearer 99999|invalidTokenStringXYZ123456789`.
- **Hasil:**
  Server memeriksa hash token pada tabel `personal_access_tokens` dan tidak menemukan record yang cocok.
  Respons yang diterima:
  ```json
  {
    "message": "Unauthenticated."
  }
  ```
- **Status code:**
  `401 Unauthorized`.
- **Kesimpulan:**
  Sanctum berhasil menolak token yang tidak terdaftar, token palsu, maupun token yang telah kedaluwarsa dengan status code `401 Unauthorized`.

---

### BREAK 4 — Mahasiswa Mengakses Endpoint Dosen
- **Akun yang digunakan:**
  Mahasiswa Demo: `mahasiswa@kampuslms.test` (Andi Pratama, Role: `mahasiswa`).
- **Pengujian:**
  Melakukan autentikasi sebagai mahasiswa, lalu memanggil endpoint pembuatan tugas:
  `POST /api/v1/assignments` dengan payload data tugas baru.
- **Hasil:**
  Permintaan berhasil lolos pemeriksaan autentikasi Sanctum (token valid), namun saat controller menjalankan otorisasi peran:
  ```php
  abort_unless($user->role === 'dosen', 403, 'Anda tidak memiliki akses ke sumber daya ini.');
  ```
  Sistem menolak permintaan mahasiswa dengan body respons:
  ```json
  {
    "message": "Anda tidak memiliki akses ke sumber daya ini."
  }
  ```
- **Status code:**
  `403 Forbidden` (bukan `401 Unauthorized`).
- **Kesimpulan:**
  Terdapat perbedaan mendasar antara **Authentication** dan **Authorization**:
  - **401 Unauthorized (Authentication):** Sistem tidak mengetahui siapa Anda (belum login atau token tidak valid).
  - **403 Forbidden (Authorization):** Sistem mengetahui siapa Anda (Andi Pratama, mahasiswa), tetapi Anda **tidak memiliki hak akses** untuk melakukan tindakan tersebut (hanya dosen yang boleh membuat tugas).

---

### BREAK 5 — N+1 Tanpa Eager Loading
- **Kondisi awal:**
  Endpoint `GET /api/v1/courses` menggunakan eager loading:
  ```php
  Course::where('lecturer_id', $user->id)->with('lecturer')->paginate(15);
  ```
  Pengujian query log menunjukkan hanya **3 query SQL** yang dijalankan ke database (1 query count pagination, 1 query data courses, dan 1 query `where in` untuk mengambil data dosen pengampu sekaligus).
- **Perubahan:**
  Menghilangkan klausul `with('lecturer')` dan mengambil relasi `lecturer` secara terpisah pada setiap iterasi mata kuliah (*lazy loading*).
- **Hasil query:**
  Jumlah query yang dieksekusi melonjak menjadi **8 query SQL** untuk data yang sama:
  - 1 query mengambil courses
  - 1 query per record course untuk mengambil relasi lecturer (`N` kali query tambahan).
- **Kesimpulan:**
  Tanpa eager loading, terjadi masalah performa klasik N+1 query. Pada dataset nyata dengan ratusan atau ribuan baris, ketiadaan eager loading akan membebani database secara masif dan memperlambat waktu respon API secara drastis.
- **Kondisi setelah dikembalikan:**
  Eager loading `with('lecturer')` dan `withCount(['materials', 'assignments'])` tetap aktif di seluruh controller koleksi API.

---

### BREAK 6 — Login Tanpa Throttle
- **Kondisi awal:**
  Endpoint login diproteksi dengan middleware `throttle:login` (maksimum 5 request per menit):
  ```php
  Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
  ```
- **Perubahan:**
  Mensimulasikan penonaktifan middleware throttle pada endpoint login, lalu mengirimkan 6 percobaan login berturut-turut dengan kata sandi yang salah.
- **Pengujian:**
  1. **Saat throttle aktif:**
     - Percobaan 1 hingga 5: Status `422 Unprocessable Content` (kredensial salah).
     - Percobaan ke-6: Akses langsung diblokir di tingkat middleware dengan status HTTP `429 Too Many Requests` (`{"message": "Too Many Attempts."}`).
  2. **Saat throttle dinonaktifkan:**
     - Seluruh percobaan (ke-1 sampai ke-6 dan seterusnya) terus diproses oleh database tanpa batas.
- **Risiko:**
  Tanpa rate limiting (throttle), sistem rentan terhadap serangan *brute force* dan *credential stuffing*, di mana penyerang menggunakan skrip otomatis untuk menebak kata sandi ribuan kali per detik hingga akun berhasil ditembus.
- **Kesimpulan:**
  Throttle pada endpoint autentikasi publik adalah perlindungan vital yang wajib ada untuk menjaga keamanan akun pengguna dari serangan berbasis tebakan otomatis.
- **Kondisi setelah dikembalikan:**
  Middleware `throttle:login` tetap terpasang aktif pada `POST /api/v1/auth/login`.

---

### BREAK 7 — User Enumeration
- **Pengujian email tidak terdaftar:**
  - Request login dengan email yang tidak ada di sistem: `tidakada@kampuslms.test` dan password acak.
  - Status HTTP: `422 Unprocessable Content`.
  - Body respons:
    ```json
    {
      "message": "Data yang diberikan tidak valid.",
      "errors": {
        "email": [
          "Kredensial yang diberikan tidak cocok dengan data kami."
        ]
      }
    }
    ```
- **Pengujian password salah:**
  - Request login dengan email valid: `dosen@kampuslms.test`, tetapi password salah.
  - Status HTTP: `422 Unprocessable Content`.
  - Body respons:
    ```json
    {
      "message": "Data yang diberikan tidak valid.",
      "errors": {
        "email": [
          "Kredensial yang diberikan tidak cocok dengan data kami."
        ]
      }
    }
    ```
- **Hasil:**
  Kedua kondisi (email tidak terdaftar maupun password salah) menghasilkan pesan kesalahan yang persis sama.
- **Risiko jika pesan dibedakan (*User Enumeration*):**
  Jika sistem membedakan pesan (misal: `"Email tidak terdaftar"` vs `"Kata sandi salah"`), penyerang dapat memanfaatkan perbedaan tersebut untuk memvalidasi daftar email korban. Penyerang dapat mengumpulkan basis data alamat email mana saja yang merupakan civitas kampus aktif di KampusLMS, untuk kemudian dijadikan target serangan spear-phishing atau brute force kata sandi secara terfokus.
- **Kesimpulan:**
  Implementasi di `AuthController` telah menerapkan prinsip keamanan standar dengan menyamakan pesan kesalahan kegagalan login, sehingga meniadakan celah kerentanan *user enumeration*.
- **Kondisi setelah dikembalikan:**
  Pesan error login di [AuthController.php](file:///C:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/Api/V1/AuthController.php) tetap menggunakan satu pesan generik yang seragam.
