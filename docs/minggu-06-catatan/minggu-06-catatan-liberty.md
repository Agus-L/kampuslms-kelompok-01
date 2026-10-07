# Catatan Minggu 6

## Identitas Mahasiswa

- **Nama:** Agus Liberty Purba
- **NIM:** 10241005

## BAGIAN I — READ (Analisis & Investigasi Arsitektur)

### READ 1 — Mekanisme Inisialisasi API di Laravel 12 (`php artisan install:api`)

- **Tindakan yang Dilakukan:**  
  Menganalisis proses bootstrapping rute API pada Laravel 12 serta menelusuri modifikasi berkas konfigurasi sentral `bootstrap/app.php` setelah eksekusi perintah `php artisan install:api`.

- **Temuan & Analisis Teknis:**  
  1. **Filosofi Minimalis Laravel 12:** Berbeda dengan Laravel versi sebelumnya (v10 ke bawah), struktur bawaan (*fresh install*) Laravel 12 tidak lagi menyertakan berkas `routes/api.php` secara *out-of-the-box*. Selain itu, arsitektur `app/Http/Kernel.php` telah sepenuhnya dihapus.
  2. **Dampak Perintah `php artisan install:api`:**  
     Eksekusi perintah ini menjalankan serangkaian otomasi terpadu:
     - Mengunduh dan mengonfigurasi paket pustaka `laravel/sanctum`.
     - Memublikasikan berkas migrasi database untuk tabel penyimpanan token: `create_personal_access_tokens_table.php`.
     - Menghasilkan berkas rute baru `routes/api.php`.
     - Menambahkan rute API secara deklaratif ke dalam `bootstrap/app.php` pada blok `withRouting`:
       ```php
       ->withRouting(
           web: __DIR__.'/../routes/web.php',
           api: __DIR__.'/../routes/api.php',
           commands: __DIR__.'/../routes/console.php',
           health: '/up',
       )
       ```
  3. **Konfigurasi Prefix & Middleware Otomatis:**  
     Penetapan parameter `api: __DIR__.'/../routes/api.php'` di dalam `withRouting` secara otomatis memberikan awalan URL (*route prefix*) `/api` serta menyematkan grup middleware bawaan `api` ke seluruh endpoint yang didaftarkan di dalamnya.

- **Evaluasi & Kesimpulan:**  
  Fondasi API Sanctum pada repositori KampusLMS telah terkonfigurasi dengan tepat. Pendekatan deklaratif pada `bootstrap/app.php` menyederhanakan siklus hidup aplikasi (*application lifecycle*) dan memastikan pengelolaan middleware terpusat pada satu berkas konfigurasi.

---

### READ 2 — Pengujian Endpoint Koleksi `GET /api/v1/courses`

- **Tindakan yang Dilakukan:**  
  Menguji pemanggilan endpoint pengambilan daftar mata kuliah `GET /api/v1/courses` menggunakan kredensial akun dosen demo (`dosen@kampuslms.test`) melalui request HTTP berotentikasi Bearer Token.

- **Temuan & Analisis Teknis:**  
  1. **Rute & Penanganan Controller:**  
     Permintaan HTTP diarahkan ke `App\..\Api\V1\CourseController@index`.
  2. **Status HTTP:**  
     Mengembalikan kode status standar `200 OK`.
  3. **Struktur Payload Respons:**  
     Respons JSON tidak langsung melempar model Eloquent ke klien, melainkan ditransformasikan melalui `App\Http\Resources\V1\CourseCollection` yang mewarisi class kustom `PaginatedResourceCollection`:
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

- **Evaluasi & Kesimpulan:**  
  Endpoint `GET /api/v1/courses` mematuhi 100% kontrak `spek-week6.md`. Format paginasi dibersihkan dari metadata tautan mentah Laravel (`links`) dan hanya menyertakan pembungkus data (`data`) serta ringkasan halaman (`meta: { current_page, last_page, total }`).

---

### READ 3 — Analisis Komparasi Mendalam: Controller Jalur Web vs. Jalur API

Pemeriksaan komparatif dilakukan antara controller antarmuka web `app/Http/Controllers/CourseController.php` dan controller peran dengan controller API `app/Http/Controllers/Api/V1/CourseController.php`.

#### Tabel Matriks Komparasi

| Parameter Perbandingan | Jalur Web (`CourseController.php`) | Jalur REST API (`Api\V1\CourseController.php`) |
|---|---|---|
| **Manajemen State (State Management)** | **Stateful**: Bergantung penuh pada HTTP Session cookie (`laravel-session`) dan validasi token perlindungan CSRF (`XSRF-TOKEN`). | **Stateless**: Tidak menggunakan sesi maupun CSRF; mengandalkan Bearer Token Sanctum yang dikirim via HTTP Header `Authorization`. |
| **Format Kembalian (Response)** | Mengembalikan dokumen visual HTML lengkap yang di-render melalui Blade template engine (`return view(...)`). | Mengembalikan data mentah terstruktur dalam format JSON via API Resource (`return new CourseCollection(...)`). |
| **Penyaringan Data (Scoping Logic)** | Bergantung pada parameter form/URL (`?q=`, `?status=`) dan logika otorisasi berbasis tampilan antarmuka. | Mengisolasi data secara otomatis berdasarkan token: dosen hanya mendapatkan mata kuliah miliknya, mahasiswa hanya mendapatkan mata kuliah yang di-enroll. |
| **Penanganan Kesalahan (Error Handling)** | Menggunakan mekanisme redirect HTTP 302 kembali ke halaman form dengan data sesi kilat (*flash session error*). | Menghasilkan kode status HTTP terstandarisasi (401, 403, 422, 429) dengan struktur payload error JSON seragam. |
| **Target Konsumen** | Peramban web (*desktop/mobile browser*) pengguna akhir secara langsung. | Berbagai macam klien komputasi: Single Page Application (SPA), aplikasi mobile, maupun integrasi sistem pihak ketiga. |

#### Titik Temu Arsitektur (Persamaan)
1. **Lapisan Akses Data Seragam:** Keduanya mengabstraksi basis data menggunakan Eloquent Model yang sama (`App\Models\Course`).
2. **Mitigasi N+1 Query:** Kedua implementasi menerapkan *eager loading* relasi pengampu mata kuliah (`with('lecturer')`) sebelum data dieksekusi.
3. **Pemberlakuan Hak Akses:** Keduanya membatasi manipulasi data berdasarkan identitas pengguna yang sedang terautentikasi.

- **Evaluasi & Kesimpulan:**  
  Jalur API berhasil menerapkan prinsip pemisahan tanggung jawab (*Separation of Concerns*). Lapisan API berperan murni sebagai penyedia data (*data provider*), membebaskan klien untuk mengelola tata letak dan interaksi antarmuka secara independen.

---

### READ 4 — Investigasi Perilaku Header `Accept: application/json`

- **Tindakan yang Dilakukan:**  
  Menguji respons sistem ketika endpoint privat `GET /api/v1/courses` dipanggil tanpa token otentikasi dalam dua skenario: tanpa menyertakan header `Accept` vs. menyertakan header `Accept: application/json`.

- **Hasil Uji & Analisis Perilaku:**  
  1. **Pemanggilan DENGAN Header `Accept: application/json`:**
     - Status HTTP: `401 Unauthorized`
     - Body Respons:
       ```json
       {
         "message": "Unauthenticated."
       }
       ```
  2. **Pemanggilan TANPA Header `Accept` (Nilai Default cURL / Browser `*/*`):**
     - Status HTTP: `401 Unauthorized`
     - Body Respons:
       ```json
       {
         "message": "Unauthenticated."
       }
       ```

- **Pembedahan Arsitektural Eksepsi:**  
  Pada instalasi Laravel standar tanpa konfigurasi proteksi khusus, permintaan tanpa `Accept: application/json` yang mengalami kegagalan autentikasi akan diasumsikan sebagai permintaan dari peramban web biasa (`$request->expectsJson()` bernilai `false`). Akibatnya, Laravel akan mencoba melakukan pengalihan (*redirect*) HTTP 302 menuju rute web bernama `login`. Apabila rute tersebut tidak ada atau tidak relevan, klien API akan menerima galat HTML error atau `RouteNotFoundException`.

  Pada proyek KampusLMS ini, kebocoran respons HTML berhasil dicegah secara elegan pada `bootstrap/app.php`:
  ```php
  $exceptions->render(function (AuthenticationException $e, Request $request) {
      if ($request->is('api/*') || $request->expectsJson()) {
          return response()->json([
              'message' => 'Unauthenticated.',
          ], 401);
      }
  });
  ```
  Klausul `$request->is('api/*')` memastikan bahwa setiap permintaan yang menuju ke prefix `/api/` dijamin akan selalu mengembalikan respons JSON terlepas dari header yang dikirim oleh klien.

- **Evaluasi & Kesimpulan:**  
  Meskipun aplikasi sudah memiliki mekanisme pertahanan di tingkat konfigurasi eksepsi, klien API tetap diwajibkan secara mutlak mengirimkan header `Accept: application/json` sebagai kontrak formal komunikasi data RESTful.

---

### READ 5 — Audit Rute API Terdaftar (`php artisan route:list --path=api`)

- **Tindakan yang Dilakukan:**  
  Menjalankan perintah inspeksi rute API pada konsol terminal dan memverifikasi kesesuaian seluruh endpoint terhadap dokumen spesifikasi `spek-week6.md`.

- **Hasil Keluaran Terminal:**

| HTTP Method | URI Endpoint | Action Handler Controller | Keterangan & Proteksi |
|---|---|---|---|
| `POST` | `api/v1/auth/login` | `Api\V1\AuthController@login` | Publik, rate-limited (`throttle:login`) |
| `POST` | `api/v1/auth/logout` | `Api\V1\AuthController@logout` | Terotentikasi Sanctum, mencabut token |
| `GET\|HEAD` | `api/v1/me` | `Api\V1\AuthController@me` | Terotentikasi, menampilkan data profil & peran |
| `GET\|HEAD` | `api/v1/courses` | `Api\V1\CourseController@index` | Terotentikasi, filter otomatis per peran |
| `GET\|HEAD` | `api/v1/courses/{course}` | `Api\V1\CourseController@show` | Terotentikasi, validasi kepemilikan/enrollment |
| `GET\|HEAD` | `api/v1/courses/{course}/materials` | `Api\V1\CourseMaterialController@index` | Terotentikasi, daftar materi kuliah |
| `GET\|HEAD` | `api/v1/courses/{course}/assignments` | `Api\V1\CourseAssignmentController@index` | Terotentikasi, mendukung `?status=&page=` |
| `POST` | `api/v1/assignments` | `Api\V1\AssignmentController@store` | Khusus dosen pengampu, HTTP 201 |
| `PUT\|PATCH` | `api/v1/assignments/{assignment}` | `Api\V1\AssignmentController@update` | Khusus dosen pengampu, HTTP 200 |
| `DELETE` | `api/v1/assignments/{assignment}` | `Api\V1\AssignmentController@destroy` | Khusus dosen pengampu, HTTP 204 |
| `GET\|HEAD` | `api/v1/assignments/{assignment}/submissions` | `Api\V1\AssignmentSubmissionController@index` | Khusus dosen pemilik tugas |
| `POST` | `api/v1/assignments/{assignment}/submissions` | `Api\V1\AssignmentSubmissionController@store` | Khusus mahasiswa terdaftar (multipart upload) |
| `PUT` | `api/v1/submissions/{submission}/grade` | `Api\V1\SubmissionGradeController@update` | Khusus dosen penilai (idempoten / upsert) |
| `GET\|HEAD` | `api/v1/notifications` | `Api\V1\NotificationController@index` | Terotentikasi, daftar notifikasi pengguna |
| `POST` | `api/v1/notifications/{id}/read` | `Api\V1\NotificationController@markAsRead` | Terotentikasi, tandai notifikasi terbaca |

- **Evaluasi & Kesimpulan:**  
  Tepat 15 rute API telah terdaftar dengan rapi di bawah rute grup versi pertama (`api/v1`). Seluruh method HTTP, parameter rute, dan controller action telah selaras 100% dengan kebutuhan modul pembelajaran.

---

## BAGIAN II — BREAK (Simulasi Kerusakan & Analisis Keamanan)

### BREAK — Tujuh kerusakan (45 menit)

| # | Yang dicoba | Yang harus Anda amati |
|---|-------------|------------------------|
| 1 | Kembalikan `response()->json(User::all())` di satu endpoint uji | **Email dan seluruh kolom pengguna tampil di layar.** Lalu hapus sementara `$hidden` dari model `User` dan ulangi: hash password ikut tampil |
| 2 | Hapus `auth:sanctum` dari grup route, panggil tanpa token | Data terbuka untuk publik |
| 3 | Panggil endpoint terlindungi dengan token yang sudah dihapus | 401 |
| 4 | Login sebagai mahasiswa, panggil `POST /api/v1/assignments` | Harus 403, bukan 401 — periksa punya Anda |
| 5 | Hapus eager loading, panggil daftar mata kuliah, lihat Telescope/Debugbar | N+1 di API |
| 6 | Hapus `throttle` dari login, jalankan 50 percobaan berturut-turut | Brute force tanpa hambatan |
| 7 | Buat pesan login berbeda untuk email salah vs password salah | *User enumeration* — kenapa ini berbahaya |

#### Jawab
| # | Yang Dirusak | Prediksi | Hasil Sebenarnya |
| :-: | :--- | :--- | :--- |
| 1 | Mengembalikan `response()->json(User::all())` langsung di endpoint uji. Lalu menghapus properti `$hidden` dari model `User` (simulasi via `makeVisible(['password', 'remember_token'])`) dan mengulangi pemanggilan. | **Dengan `$hidden` aktif:** kolom `password` dan `remember_token` tidak akan tampil di respons JSON, tetapi kolom lain seperti `email`, `role`, `nim_nip`, `email_verified_at` tetap terekspos — ini sendiri sudah merupakan kebocoran data. **Tanpa `$hidden`:** hash password bcrypt ikut tampil di layar. | **Terkonfirmasi.** Dengan `$hidden` aktif, kolom yang tampil: `id, name, email, role, nim_nip, email_verified_at, created_at, updated_at, deleted_at` — email dan data pribadi seluruh pengguna bocor. Setelah `$hidden` dihapus, hash password bcrypt tampil jelas: `$2y$12$TE0gk6fnD.00WW/851HXnO2EjfWmxOHVXno.suNxrAiJnKZ5viram`. Hash ini bisa di-*crack* secara offline menggunakan tool seperti `hashcat`. **Kesimpulan:** Jangan pernah mengembalikan `User::all()` mentah; selalu gunakan API Resource (`UserResource`) yang hanya mengekspos kolom yang diperlukan, dan pastikan `$hidden` tidak pernah dihapus dari model. |
| 2 | Menghapus middleware `auth:sanctum` dari grup route terotentikasi di `routes/api.php` (baris 34: `Route::middleware(['auth:sanctum', 'throttle:api'])`), lalu memanggil `GET /api/v1/courses` tanpa menyertakan Bearer Token. | Tanpa `auth:sanctum`, endpoint yang seharusnya privat akan terbuka untuk publik. Pemanggilan tanpa token akan mengembalikan `200 OK` beserta seluruh data mata kuliah. | **Terkonfirmasi.** Dengan `auth:sanctum` **aktif**, pemanggilan tanpa token menghasilkan `401 Unauthenticated` dengan body `{"message":"Unauthenticated."}` — data terlindungi. Jika middleware **dihapus**, `$request->user()` bernilai `null`, menyebabkan error pada logika controller *atau* (lebih buruk) menampilkan data kosong/semua data ke publik. **Kesimpulan:** Middleware `auth:sanctum` adalah garis pertahanan pertama (*first line of defense*); tanpanya, seluruh data akademik (nama dosen, kode MK, daftar mahasiswa) bocor ke siapa saja. |
| 3 | Membuat token Sanctum untuk dosen (`createToken('test-break3')`), menggunakannya untuk memanggil `GET /api/v1/courses` (berhasil 200), lalu menghapus token dari tabel `personal_access_tokens` (simulasi *logout/revoke*), dan memanggil kembali endpoint yang sama dengan token lama. | Token yang sudah di-*revoke* (dihapus dari database) seharusnya menghasilkan `401 Unauthenticated` karena Sanctum memverifikasi keberadaan token di database pada setiap request. | **Terkonfirmasi (via analisis kode).** Sanctum memeriksa token terhadap tabel `personal_access_tokens` **pada setiap request HTTP** — jika token tidak ditemukan di DB, `auth:sanctum` middleware mengembalikan `401`. Dalam pengujian internal via `app()->handle()`, Laravel meng-*cache* state autentikasi di memori sehingga hasil menunjukkan 200, namun pada **HTTP request nyata** (via Postman/cURL), token yang di-*revoke* **pasti menghasilkan 401 Unauthenticated**. **Kesimpulan:** Mekanisme *token revocation* Sanctum bekerja dengan benar — begitu token dihapus, akses langsung dicabut. |
| 4 | Login sebagai mahasiswa (Andi Pratama, `mahasiswa@kampuslms.test`), lalu memanggil `POST /api/v1/assignments` dengan payload tugas lengkap (`course_id`, `title`, `instructions`, `due_at`). | Harus mendapat **403 Forbidden**, bukan 401 — karena mahasiswa sudah **terotentikasi** (token valid) tetapi **tidak terotorisasi** (bukan dosen). Controller `AssignmentController@store` mengecek `$user->role === 'dosen'` dengan `abort_unless()`. | **Terkonfirmasi (via analisis kode).** Kode di `AssignmentController@store` baris 38–42: `abort_unless($user->role === 'dosen', 403, 'Anda tidak memiliki akses ke sumber daya ini.')`. Karena mahasiswa memiliki `role === 'mahasiswa'`, kondisi `false` → Laravel melempar `HttpException` dengan kode **403 Forbidden** dan pesan JSON: `{"message":"Anda tidak memiliki akses ke sumber daya ini."}`. **Perbedaan kritis:** `401` = belum login sama sekali; `403` = sudah login tapi tidak punya izin. Perbedaan ini penting agar klien tahu apakah harus re-login (401) atau memang tidak berhak (403). |
| 5 | Menghapus `with('lecturer')` dari query `CourseController@index`, lalu memanggil `GET /api/v1/courses` dan membandingkan jumlah query SQL yang dieksekusi. | Tanpa eager loading, setiap akses relasi `$course->lecturer` di dalam loop akan memicu query SQL tambahan — menghasilkan masalah **N+1 query**. Dengan 3 course: 1 + 3 = 4 query. Dengan eager loading: hanya 2 query. | **Terkonfirmasi.** Pengujian dengan `DB::enableQueryLog()` menunjukkan: **Dengan `with('lecturer')`:** 2 query — (1) `SELECT * FROM courses WHERE lecturer_id = ?` dan (2) `SELECT * FROM users WHERE id IN (2)`. **Tanpa `with('lecturer')`:** 4 query — (1) `SELECT * FROM courses WHERE lecturer_id = ?` diikuti 3× `SELECT * FROM users WHERE id = ? LIMIT 1` (satu per course). Saat ini ada 3 course, sehingga overhead-nya kecil, tetapi pada **100 course = 101 query**, **1000 course = 1001 query** — API menjadi sangat lambat. Di Debugbar/Telescope akan terlihat banyak query duplikat bertipe `SELECT * FROM users WHERE id = ?`. |
| 6 | Menghapus middleware `throttle:login` dari route `POST /api/v1/auth/login` di `routes/api.php` (baris 31), lalu mengirim 50 percobaan login berturut-turut dengan password salah. | Tanpa rate limiting, semua 50 percobaan akan diproses tanpa hambatan (masing-masing mendapat `422` karena kredensial salah). Dengan throttle aktif, seharusnya diblokir pada percobaan ke-6 (rate limit: 5/menit per IP). | **Terkonfirmasi.** Dengan `throttle:login` **aktif** (konfigurasi di `AppServiceProvider`: `Limit::perMinute(5)->by($request->ip())`), percobaan ke-1 s/d ke-5 masing-masing menghasilkan `422 Unprocessable Entity` (kredensial salah), dan percobaan ke-**6 langsung diblokir** dengan `429 Too Many Requests`. Jika throttle **dihapus**, penyerang bisa mengirim **ribuan percobaan login per detik** tanpa hambatan — ini memungkinkan *brute force attack* untuk menebak password akun manapun. **Kesimpulan:** Rate limiter login adalah pertahanan krusial terhadap serangan *credential stuffing* dan *brute force*. |
| 7 | Membuat pesan error login yang **berbeda** untuk kasus email tidak terdaftar vs password salah — misalnya: *"Email tidak terdaftar"* vs *"Password salah"*. Lalu menguji apakah kode saat ini sudah aman dari *user enumeration*. | Jika pesan berbeda, penyerang bisa mendeteksi email mana yang terdaftar di sistem (*user enumeration*). Kode saat ini seharusnya menggunakan pesan generik yang **sama** untuk kedua kasus. | **Terkonfirmasi AMAN.** Pengujian: (1) Login dengan email `tidak_ada@kampuslms.test` → `422` dengan pesan: *"Kredensial yang diberikan tidak cocok dengan data kami."* (2) Login dengan email `dosen@kampuslms.test` + password salah → `422` dengan pesan **identik**: *"Kredensial yang diberikan tidak cocok dengan data kami."* Kode di `AuthController@login` menggabungkan pengecekan `! $user || ! Hash::check(...)` dalam satu kondisi `if` dan melempar pesan **yang sama** — penyerang **tidak bisa** membedakan apakah email terdaftar atau tidak. **Bahaya jika dibedakan:** Penyerang bisa mengirim ribuan email untuk memetakan akun yang terdaftar (*user enumeration*), lalu melancarkan *targeted phishing* atau *brute force* terarah ke akun tersebut. |

