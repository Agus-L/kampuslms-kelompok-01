# Catatan Minggu 5

## Identitas Mahasiswa

Nama: Anastasya Salsabila Khoirunnisa
NIM: 10241009

## READ

### READ #1 — Route List

Hasil eksekusi perintah `php artisan route:list --except-vendor`:

```
GET|HEAD   / ................................................................ home › routes/web.php:60
GET|HEAD   admin/courses ........................................ admin.courses.index › CourseController@index
POST       admin/courses ........................................ admin.courses.store › CourseController@store
GET|HEAD   admin/courses/create ............................... admin.courses.create › CourseController@create
GET|HEAD   admin/courses/{course} ................................. admin.courses.show › CourseController@show
PUT|PATCH  admin/courses/{course} ............................. admin.courses.update › CourseController@update
DELETE     admin/courses/{course} ........................... admin.courses.destroy › CourseController@destroy
GET|HEAD   admin/courses/{course}/edit ............................ admin.courses.edit › CourseController@edit
GET|HEAD   admin/courses/{course}/enrollments admin.courses.enrollments.index › Admin\EnrollmentController@index
POST       admin/courses/{course}/enrollments admin.courses.enrollments.store › Admin\EnrollmentController@store
GET|HEAD   admin/courses/{course}/enrollments/create admin.courses.enrollments.create › Admin\EnrollmentController@create
DELETE     admin/enrollments/{enrollment} ..... admin.enrollments.destroy › Admin\EnrollmentController@destroy
GET|HEAD   admin/users .............................................. admin.users.index › UserController@index
POST       admin/users .............................................. admin.users.store › UserController@store
GET|HEAD   admin/users/create ..................................... admin.users.create › UserController@create
PUT|PATCH  admin/users/{user} ..................................... admin.users.update › UserController@update
DELETE     admin/users/{user} ................................... admin.users.destroy › UserController@destroy
GET|HEAD   admin/users/{user}/edit .................................... admin.users.edit › UserController@edit
GET|HEAD   dashboard ................................................... dashboard › DashboardController@index
GET|HEAD   dosen/assignments/{assignment} ........... dosen.assignments.show › Dosen\AssignmentController@show
PUT|PATCH  dosen/assignments/{assignment} ....... dosen.assignments.update › Dosen\AssignmentController@update
DELETE     dosen/assignments/{assignment} ..... dosen.assignments.destroy › Dosen\AssignmentController@destroy
GET|HEAD   dosen/assignments/{assignment}/edit ...... dosen.assignments.edit › Dosen\AssignmentController@edit
GET|HEAD   dosen/assignments/{assignment}/submissions dosen.assignments.submissions.index › Dosen\SubmissionController@index
GET|HEAD   dosen/courses .................................. dosen.courses.index › Dosen\CourseController@index
GET|HEAD   dosen/courses/{course} ........................... dosen.courses.show › Dosen\CourseController@show
GET|HEAD   dosen/courses/{course}/assignments dosen.courses.assignments.index › Dosen\AssignmentController@index
POST       dosen/courses/{course}/assignments dosen.courses.assignments.store › Dosen\AssignmentController@store
GET|HEAD   dosen/courses/{course}/assignments/create dosen.courses.assignments.create › Dosen\AssignmentController@create
GET|HEAD   dosen/courses/{course}/materials .... dosen.courses.materials.index › Dosen\MaterialController@index
POST       dosen/courses/{course}/materials .... dosen.courses.materials.store › Dosen\MaterialController@store
GET|HEAD   dosen/courses/{course}/materials/create dosen.courses.materials.create › Dosen\MaterialController@create
PUT|PATCH  dosen/grades/{grade} ........................... dosen.grades.update › Dosen\GradeController@update
GET|HEAD   dosen/materials/{material} .................... dosen.materials.show › Dosen\MaterialController@show
PUT|PATCH  dosen/materials/{material} ................ dosen.materials.update › Dosen\MaterialController@update
DELETE     dosen/materials/{material} ............. dosen.materials.destroy › Dosen\MaterialController@destroy
GET|HEAD   dosen/materials/{material}/edit .............. dosen.materials.edit › Dosen\MaterialController@edit
GET|HEAD   dosen/submissions/{submission} ........... dosen.submissions.show › Dosen\SubmissionController@show
POST       dosen/submissions/{submission}/grades dosen.submissions.grades.store › Dosen\GradeController@store
GET|HEAD   login ................................................................... login › routes/web.php:75
GET|HEAD   logout ................................................................. logout › routes/web.php:94
GET|HEAD   mahasiswa/assignments/{assignment} mahasiswa.assignments.show › Mahasiswa\AssignmentController@show
GET|HEAD   mahasiswa/assignments/{assignment}/submissions mahasiswa.assignments.submissions.index › Mahasiswa\SubmissionController@index
POST       mahasiswa/assignments/{assignment}/submissions mahasiswa.assignments.submissions.store › Mahasiswa\SubmissionController@store
GET|HEAD   mahasiswa/assignments/{assignment}/submissions/create mahasiswa.assignments.submissions.create › Mahasiswa\SubmissionController@create
GET|HEAD   mahasiswa/courses ...................... mahasiswa.courses.index › Mahasiswa\CourseController@index
GET|HEAD   mahasiswa/courses/{course} ............... mahasiswa.courses.show › Mahasiswa\CourseController@show
GET|HEAD   mahasiswa/courses/{course}/assignments mahasiswa.courses.assignments.index › Mahasiswa\AssignmentController@index
GET|HEAD   mahasiswa/courses/{course}/materials mahasiswa.courses.materials.index › Mahasiswa\MaterialController@index
GET|HEAD   mahasiswa/materials/{material} ....... mahasiswa.materials.show › Mahasiswa\MaterialController@show
GET|HEAD   mahasiswa/submissions/{submission} mahasiswa.submissions.show › Mahasiswa\SubmissionController@show
PUT|PATCH  mahasiswa/submissions/{submission} mahasiswa.submissions.update › Mahasiswa\SubmissionController@update
GET|HEAD   mahasiswa/submissions/{submission}/edit mahasiswa.submissions.edit › Mahasiswa\SubmissionController@edit
GET|HEAD   mahasiswa/submissions/{submission}/grades mahasiswa.submissions.grades.index › Mahasiswa\GradeController@index
GET|HEAD   notifications .................................. notifications.index › NotificationController@index
PATCH      notifications/{notification} ................. notifications.update › NotificationController@update
GET|HEAD   tentang ............................................................... tentang › routes/web.php:64
```

### READ #2 — Route dengan Model Parameter

Route yang menerima parameter model pada aplikasi:

1. `admin/courses/{course}` (GET, PUT/PATCH, DELETE)
2. `admin/courses/{course}/edit` (GET)
3. `admin/courses/{course}/enrollments` (GET, POST)
4. `admin/courses/{course}/enrollments/create` (GET)
5. `admin/enrollments/{enrollment}` (DELETE)
6. `admin/users/{user}` (PUT/PATCH, DELETE)
7. `admin/users/{user}/edit` (GET)
8. `dosen/assignments/{assignment}` (GET, PUT/PATCH, DELETE)
9. `dosen/assignments/{assignment}/edit` (GET)
10. `dosen/assignments/{assignment}/submissions` (GET)
11. `dosen/courses/{course}` (GET)
12. `dosen/courses/{course}/assignments` (GET, POST)
13. `dosen/courses/{course}/assignments/create` (GET)
14. `dosen/courses/{course}/materials` (GET, POST)
15. `dosen/courses/{course}/materials/create` (GET)
16. `dosen/grades/{grade}` (PUT/PATCH)
17. `dosen/materials/{material}` (GET, PUT/PATCH, DELETE)
18. `dosen/materials/{material}/edit` (GET)
19. `dosen/submissions/{submission}` (GET)
20. `dosen/submissions/{submission}/grades` (POST)
21. `mahasiswa/assignments/{assignment}` (GET)
22. `mahasiswa/assignments/{assignment}/submissions` (GET, POST)
23. `mahasiswa/assignments/{assignment}/submissions/create` (GET)
24. `mahasiswa/courses/{course}` (GET)
25. `mahasiswa/courses/{course}/assignments` (GET)
26. `mahasiswa/courses/{course}/materials` (GET)
27. `mahasiswa/materials/{material}` (GET)
28. `mahasiswa/submissions/{submission}` (GET, PUT/PATCH)
29. `mahasiswa/submissions/{submission}/edit` (GET)
30. `mahasiswa/submissions/{submission}/grades` (GET)
31. `notifications/{notification}` (PATCH)

### READ #3 — Akses dan Perlindungan

- **Grup Admin (`admin/*`)**:
  - **Hak Akses Seharusnya**: Hanya pengguna terautentikasi dengan peran `admin`.
  - **Perlindungan Saat Ini**: Dilindungi oleh middleware `auth` dan middleware kustom `role:admin`.
- **Grup Dosen (`dosen/*`)**:
  - **Hak Akses Seharusnya**: Dosen yang mengampu mata kuliah terkait.
  - **Perlindungan Saat Ini**: Dilindungi oleh middleware `auth`, `role:dosen`, serta `scopeBindings()` pada level route group. Namun, **belum ada verifikasi kepemilikan (ownership check)** pada level controller atau Policy, sehingga dosen A masih bisa melihat/mengakses course milik dosen B.
- **Grup Mahasiswa (`mahasiswa/*`)**:
  - **Hak Akses Seharusnya**: Mahasiswa yang terdaftar (enrolled) pada mata kuliah terkait, atau mahasiswa pemilik submission/nilai tersebut.
  - **Perlindungan Saat Ini**: Dilindungi oleh middleware `auth`, `role:mahasiswa`, serta `scopeBindings()` pada level route group. Namun, **belum ada pengecekan kepemilikan submission** di level controller atau Policy, sehingga Mahasiswa A bisa membaca/mengedit submission milik Mahasiswa B.
- **Grup Umum/Notifikasi (`notifications/*`)**:
  - **Hak Akses Seharusnya**: Pengguna pemilik notifikasi terkait.
  - **Perlindungan Saat Ini**: Dilindungi oleh middleware `auth`.

### READ #4 — Daftar Titik Rawan IDOR

| Route | Model/Parameter | Siapa yang Seharusnya Boleh Mengakses | Perlindungan Saat Ini | Potensi IDOR |
|---|---|---|---|---|
| `mahasiswa/submissions/{submission}` | `Submission` | Mahasiswa pemilik submission | Middleware `auth`, `role:mahasiswa` | **Tinggi**: Mahasiswa A dapat mengakses/membaca submission milik Mahasiswa B dengan mengganti ID submission pada URL. |
| `mahasiswa/submissions/{submission}/edit` | `Submission` | Mahasiswa pemilik submission | Middleware `auth`, `role:mahasiswa` | **Tinggi**: Mahasiswa A dapat membuka form edit submission milik Mahasiswa B. |
| `mahasiswa/submissions/{submission}/grades` | `Submission` | Mahasiswa pemilik submission | Middleware `auth`, `role:mahasiswa` | **Tinggi**: Mahasiswa A dapat melihat nilai tugas milik Mahasiswa B. |
| `dosen/courses/{course}` | `Course` | Dosen pengampu course tersebut | Middleware `auth`, `role:dosen` | **Sedang**: Dosen A dapat mengakses detail course milik Dosen B. |
| `dosen/materials/{material}` | `Material` | Dosen pengampu course terkait | Middleware `auth`, `role:dosen` | **Tinggi**: Dosen A dapat mengakses/mengedit/menghapus materi milik Dosen B. |
| `dosen/assignments/{assignment}` | `Assignment` | Dosen pengampu course terkait | Middleware `auth`, `role:dosen` | **Tinggi**: Dosen A dapat mengakses/mengedit/menghapus assignment milik Dosen B. |
| `dosen/submissions/{submission}` | `Submission` | Dosen pengampu course terkait | Middleware `auth`, `role:dosen` | **Sedang**: Dosen A dapat melihat submission tugas pada course milik Dosen B. |
| `dosen/grades/{grade}` | `Grade` | Dosen pengampu course terkait | Middleware `auth`, `role:dosen` | **Tinggi**: Dosen A dapat memperbarui nilai submission course milik Dosen B. |
| `mahasiswa/courses/{course}` | `Course` | Mahasiswa yang enrolled di course tersebut | Middleware `auth`, `role:mahasiswa` | **Sedang**: Mahasiswa yang tidak terdaftar dapat melihat detail course jika mengetahui ID course. |
| `mahasiswa/materials/{material}` | `Material` | Mahasiswa yang enrolled di course terkait | Middleware `auth`, `role:mahasiswa` | **Sedang**: Mahasiswa dapat membuka materi dari course yang tidak diambilnya. |
| `mahasiswa/assignments/{assignment}` | `Assignment` | Mahasiswa yang enrolled di course terkait | Middleware `auth`, `role:mahasiswa` | **Sedang**: Mahasiswa dapat membuka tugas dari course yang tidak diambilnya. |
| `notifications/{notification}` | `Notification` | User penerima notifikasi | Middleware `auth` | **Tinggi**: User dapat memperbarui/membaca notifikasi milik user lain dengan mengubah ID notification. |

---

## BREAK

### BREAK #1 — IDOR pada Submission

**Prediksi:**
Mahasiswa A yang sudah login dapat mengakses data submission milik Mahasiswa B dengan mengubah ID submission pada URL (`/mahasiswa/submissions/{id}`) karena controller belum memverifikasi kepemilikan (`user_id`).

**Perubahan:**
Tidak ada perubahan kode yang dilakukan (menguji kondisi default project saat ini).

**Pengujian:**
1. Login sebagai Mahasiswa A (ID: 5).
2. Tentukan ID submission milik Mahasiswa B (ID: 6), yaitu Submission ID: 67.
3. Kirim request `GET /mahasiswa/submissions/67` dengan sesi Mahasiswa A.

**Hasil Aktual:**
Server mengembalikan response dengan status **HTTP 200 OK**. Mahasiswa A berhasil mengakses endpoint detail submission milik Mahasiswa B tanpa terhalang otorisasi. Terbukti terjadi kerentanan IDOR (*Insecure Direct Object Reference*).

**Kesimpulan:**
Route Model Binding (`{submission}`) hanya memastikan data `Submission` dengan ID tersebut ada di database, tetapi tidak memeriksa apakah pengguna yang sedang login (`auth()->user()`) adalah pemilik dari submission tersebut.

**Pemulihan:**
Tidak ada perubahan kode yang perlu dikembalikan.

---

### BREAK #2 — Nested Route Tanpa Scoping

**Prediksi:**
Jika `scopeBindings()` dinonaktifkan pada route bersarang `/dosen/courses/{course}/assignments/{assignment}`, maka mengakses assignment ID 4 (yang sebenarnya milik Course ID 2) melalui URL Course ID 1 (`/dosen/courses/1/assignments/4`) akan berhasil (HTTP 200 OK) meskipun relasi parent-child tidak cocok.

**Perubahan:**
Menghapus sementara method `->scopeBindings()` dari grup route Dosen di file `routes/web.php` dan mendefinisikan route bersarang tanpa scoping.

**Pengujian:**
1. Login sebagai Dosen.
2. Buat request `GET /dosen/courses/1/assignments/4` di mana Assignment ID 4 secara database memiliki `course_id = 2`.

**Hasil Aktual:**
Tanpa `scopeBindings()`, Laravel melakukan pencarian model secara terpisah (`Course::findOrFail(1)` dan `Assignment::findOrFail(4)`). Karena Assignment ID 4 ada di database, request berhasil diproses dengan status **HTTP 200 OK** (atau route dapat ditemukan).

**Kesimpulan:**
Tanpa `scopeBindings()`, Laravel tidak memvalidasi hubungan kepemilikan parent-child pada parameter nested route, sehingga resource milik parent lain dapat diakses melalui URL parent yang salah.

**Pemulihan:**
Mengembalikan deklarasi `scopeBindings()` pada grup route di `routes/web.php`.

---

### BREAK #3 — scopeBindings()

**Prediksi:**
Setelah `Route::scopeBindings()` diaktifkan kembali pada grup route bersarang, request ke `/dosen/courses/1/assignments/4` akan ditolak dengan status HTTP 404 Not Found karena Assignment 4 bukan bagian dari Course 1.

**Perubahan:**
Memastikan `Route::scopeBindings()` aktif pada grup route di `routes/web.php`.

**Pengujian:**
1. Login sebagai Dosen.
2. Buat request `GET /dosen/courses/1/assignments/4` di mana Assignment 4 milik Course 2.

**Hasil Aktual:**
Server mengembalikan response dengan status **HTTP 404 Not Found**.

**Kesimpulan:**
`Route::scopeBindings()` secara otomatis menambah klausul kueri relasi (e.g. `$course->assignments()->findOrFail($assignmentId)`), sehingga jika model anak tidak terikat dengan model induknya, Laravel otomatis melempar `ModelNotFoundException` (HTTP 404).

**Pemulihan:**
Perubahan eksperimen telah dikembalikan dan file `routes/web.php` tetap mempertahankan `scopeBindings()`.

---

### BREAK #4 — Registrasi Middleware

**Prediksi:**
Mencari dan mencoba mendaftarkan middleware kustom pada file `app/Http/Kernel.php` seperti pada tutorial Laravel 10 atau versi sebelumnya.

**Perubahan:**
Memeriksa struktur direktori proyek `app/Http/` untuk menemukan file `Kernel.php`.

**Pengujian:**
Memeriksa keberadaan file `app/Http/Kernel.php` pada repository proyek.

**Hasil Aktual:**
File `app/Http/Kernel.php` **TIDAK ADA / TIDAK DITEMUKAN** (Keberadaan: `NO`). 
Pada proyek Laravel 12 ini, registrasi middleware dilakukan di file `bootstrap/app.php` melalui method `->withMiddleware()`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\EnsureUserHasRole::class,
    ]);
})
```

**Kesimpulan:**
Laravel 11 dan 12 mengadopsi struktur aplikasi yang lebih ringkas (*streamlined application structure*) di mana `app/Http/Kernel.php` telah dihapus. Pendaftaran alias middleware, middleware global, maupun middleware group kini dipusatkan di `bootstrap/app.php`.

**Pemulihan:**
Tidak ada pembuatan file `Kernel.php` buatan (eksperimen mengikuti kondisi aktual Laravel 12).

---

### BREAK #5 — Middleware Role

**Prediksi:**
Jika akun dengan role `dosen` mencoba mengakses route yang dilindungi middleware `role:admin` (seperti `/admin/courses`), request akan ditolak oleh middleware `EnsureUserHasRole` dengan status HTTP 403 Forbidden.

**Perubahan:**
Tidak ada perubahan kode (menguji middleware `role:admin` yang sudah terpasang di `routes/web.php`).

**Pengujian:**
1. Login sebagai Dosen (misal Dr. Budi Santoso, role: `dosen`).
2. Akses URL `/admin/courses`.

**Hasil Aktual:**
Server mengembalikan response dengan status **HTTP 403 Forbidden**. Middleware `EnsureUserHasRole` mengecek `$request->user()->role` dan menghentikan eksekusi karena role user bukan `admin`.

**Kesimpulan:**
Middleware role berfungsi dengan baik sebagai *gatekeeper* (penjaga gerbang pintu masuk) untuk membatasi akses antar zona/peran aplikasi (Admin vs Dosen vs Mahasiswa).

**Pemulihan:**
Tidak ada perubahan yang perlu dikembalikan.

---

### BREAK #6 — Middleware Role vs Ownership

**Prediksi:**
Dosen A dan Dosen B sama-sama memiliki role `dosen`. Middleware `role:dosen` akan meloloskan Dosen A ketika membuka halaman course milik Dosen B (`/dosen/courses/{id_course_b}`), membuktikan bahwa middleware role belum cukup untuk mengamankan data spesifik per-objek.

**Perubahan:**
Tidak ada perubahan kode (kondisi default project).

**Pengujian:**
1. Login sebagai Dosen A (Dr. Budi Santoso, ID User: 2).
2. Akses halaman course milik Dosen B (Prof. Siti Rahayu, ID User: 3), yaitu URL `/dosen/courses/3`.

**Hasil Aktual:**
Server mengembalikan response dengan status **HTTP 200 OK**. Dosen A berhasil mengakses halaman detail course milik Dosen B.

**Kesimpulan:**
Middleware `role:dosen` hanya menjawab pertanyaan: *"Apakah pengguna ini seorang dosen?"* (peran secara umum). Middleware role **TIDAK BISA** menjawab pertanyaan: *"Apakah dosen ini adalah pengampu dari mata kuliah ini?"* (otorisasi objek/kepemilikan). Oleh karena itu, middleware role saja belum cukup dan membutuhkan lapisan otorisasi tambahan di level controller atau Policy (materi Minggu 7).

**Pemulihan:**
Tidak ada perubahan yang perlu dikembalikan.

---

## Kesimpulan

Berdasarkan hasil analisis READ dan serangkaian pengujian BREAK pada Minggu 5:

1. **IDOR (*Insecure Direct Object Reference*)** merupakan kerentanan utama saat menggunakan Route Model Binding tanpa otorisasi kepemilikan. Route Model Binding hanya menjamin bahwa data ada di database, tetapi tidak menjamin pengguna yang mengakses berhak atas data tersebut.
2. **`Route::scopeBindings()`** sangat efektif untuk mengamankan *nested routes* (route bersarang) agar data anak (*child*) dipastikan milik data induk (*parent*), mencegah akses data anak yang terlepas dari konteks parent.
3. **Pendaftaran Middleware di Laravel 12** berlokasi di `bootstrap/app.php`, bukan lagi di `app/Http/Kernel.php` seperti pada tutorial Laravel versi lama.
4. **Middleware vs Ownership**: Middleware peran (`role:admin`, `role:dosen`, `role:mahasiswa`) hanya membatasi zona akses umum (pintu masuk), tetapi **tidak cukup** untuk melindungi kepemilikan data spesifik per-objek. Diperlukan pengecekan kepemilikan (*ownership authorization*) di level controller/Policy untuk mencegah serangan IDOR antar pengguna dengan role yang sama.
