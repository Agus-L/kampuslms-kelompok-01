# Catatan Minggu 5
## Identitas Mahasiswa
Nama    : Agus Liberty Purba  
NIM     : 10241005

## READ
### 1. Jalankan `php artisan route:list --except-vendor`. Salin keluarannya ke catatan.
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
  - **Hak Akses Seharusnya**: Mahasiswa yang terdaftar (enrolled) pada mata kuliah terkait, atau mahasiswa pemilik minggu-05-catatan-libertysubmission/nilai tersebut.
  - **Perlindungan Saat Ini**: Dilindungi oleh middleware `auth`, `role:mahasiswa`, serta `scopeBindings()` pada level route group. Namun, **belum ada pengecekan kepemilikan submission** di level controller atau Policy, sehingga Mahasiswa A bisa membaca/mengedit submission milik Mahasiswa B.
- **Grup Umum/Notifikasi (`notifications/*`)**:
  - **Hak Akses Seharusnya**: Pengguna pemilik notifikasi terkait.
  - **Perlindungan Saat Ini**: Dilindungi oleh middleware `auth`.


### READ #4 Buat tabel di `docs/minggu-05-liberty.md` berjudul "Daftar Titik Rawan IDOR". Tabel ini akan Anda pakai lagi di minggu 7 dan saat interview.

| No | Route | Model/Parameter | Siapa yang Seharusnya Boleh Mengakses | Perlindungan Saat Ini | Potensi IDOR |
|:--:|---|---|---|---|---|
| 1 | `GET dosen/courses/{course}` | `Course $course` | Dosen pengampu MK tersebut (`lecturer_id === auth()->id()`) | `auth` + `role:dosen` + `abort_unless` di controller  | **Rendah** — sudah ada pengecekan kepemilikan di `show()`, namun belum pakai Policy (inline check, rapuh jika lupa) |
| 2 | `GET dosen/courses/{course}/materials` | `Course $course` | Dosen pengampu MK tersebut | `auth` + `role:dosen` + `abort_unless` di controller   | **Rendah** — sudah dicek `lecturer_id`, tetapi pengecekan di setiap method controller (bukan Policy terpusat) rawan terlewat saat ada method baru |
| 3 | `POST dosen/courses/{course}/materials` | `Course $course` | Dosen pengampu MK tersebut | `auth` + `role:dosen` + `abort_unless` di controller   | **Rendah** — sama seperti No. 2 |
| 4 | `GET/PUT/DELETE dosen/materials/{material}` | `Material $material` | Dosen pengampu MK pemilik materi (`$material->course->lecturer_id`) | `auth` + `role:dosen` + `abort_unless` via relasi   | **Rendah** — shallow route sudah dicek via `$material->course->lecturer_id`, tapi tanpa `scopeBindings` di level shallow, dosen bisa memuat material apa saja yang lolos pengecekan relasi |
| 5 | `GET dosen/courses/{course}/assignments` | `Course $course` | Dosen pengampu MK tersebut | `auth` + `role:dosen` + `abort_unless` di controller   | **Rendah** — sama seperti No. 2 |
| 6 | `POST dosen/courses/{course}/assignments` | `Course $course` | Dosen pengampu MK tersebut | `auth` + `role:dosen` + `abort_unless` di controller   | **Rendah** — sama seperti No. 2 |
| 7 | `GET/PUT/DELETE dosen/assignments/{assignment}` | `Assignment $assignment` | Dosen pengampu MK pemilik tugas (`$assignment->course->lecturer_id`) | `auth` + `role:dosen` + `abort_unless` via relasi   | **Rendah** — shallow route, dicek via `$assignment->course->lecturer_id` |
| 8 | `GET dosen/assignments/{assignment}/submissions` | `Assignment $assignment` | Dosen pengampu MK yang memiliki tugas tersebut | `auth` + `role:dosen` + `abort_unless` di controller   | **Rendah** — dicek via `$assignment->course->lecturer_id` |
| 9 | `GET dosen/submissions/{submission}` | `Submission $submission` | Dosen pengampu MK dari submission tersebut | `auth` + `role:dosen` + `abort_unless` via rantai relasi `$submission->assignment->course->lecturer_id`   | **Rendah** — rantai relasi panjang (3 level), rawan N+1 tapi secara otorisasi sudah dicek |
| 10 | `POST dosen/submissions/{submission}/grades` | `Submission $submission` | Dosen pengampu MK dari submission tersebut | `auth` + `role:dosen` + `abort_unless` via rantai relasi   | **Rendah** — sama seperti No. 9 |
| 11 | `PUT dosen/grades/{grade}` | `Grade $grade` | Dosen pengampu MK dari grade tersebut | `auth` + `role:dosen` + `abort_unless` via rantai `$grade->submission->assignment->course->lecturer_id`   | **Sedang** — rantai relasi 4 level sangat panjang; jika salah satu relasi null, bisa error 500 alih-alih 403 |
| 12 | `GET mahasiswa/courses/{course}` | `Course $course` | Mahasiswa yang ter-enroll di MK tersebut | `auth` + `role:mahasiswa` + `abort_unless` cek enrollment   | **Rendah** — sudah dicek `$course->students()->where('user_id', auth()->id())->exists()` |
| 13 | `GET mahasiswa/courses/{course}/materials` | `Course $course` | Mahasiswa yang ter-enroll di MK tersebut | `auth` + `role:mahasiswa` + `abort_unless` cek enrollment   | **Rendah** — sama seperti No. 12 |
| 14 | `GET mahasiswa/materials/{material}` | `Material $material` | Mahasiswa yang ter-enroll di MK pemilik materi | `auth` + `role:mahasiswa` + `abort_unless` via `$material->course->students()`   | **Sedang** — shallow route: mahasiswa yang tidak ter-enroll di MK bisa menebak `{material}` ID; pengecekan sudah ada, tapi tanpa Policy terpusat |
| 15 | `GET mahasiswa/courses/{course}/assignments` | `Course $course` | Mahasiswa yang ter-enroll di MK tersebut | `auth` + `role:mahasiswa` + `abort_unless` cek enrollment   | **Rendah** — sama seperti No. 12 |
| 16 | `GET mahasiswa/assignments/{assignment}` | `Assignment $assignment` | Mahasiswa yang ter-enroll di MK pemilik tugas | `auth` + `role:mahasiswa` + `abort_unless` via `$assignment->course->students()`   | **Sedang** — shallow route, pengecekan enrollment via relasi sudah ada tapi bukan Policy |
| 17 | `GET/POST mahasiswa/assignments/{assignment}/submissions` | `Assignment $assignment` | Mahasiswa yang ter-enroll di MK pemilik tugas | `auth` + `role:mahasiswa` + `abort_unless` cek enrollment   | **Sedang** — index menampilkan **semua** submission di assignment, bukan hanya milik user yang login; mahasiswa bisa melihat submission mahasiswa lain dalam MK yang sama |
| 18 | `GET mahasiswa/submissions/{submission}` | `Submission $submission` | **Hanya** mahasiswa pemilik submission (`user_id === auth()->id()`) | `auth` + `role:mahasiswa` + `abort_unless` cek `user_id`   | **Rendah** — sudah dicek `$submission->user_id === auth()->id()` |
| 19 | `GET/PUT mahasiswa/submissions/{submission}/edit` | `Submission $submission` | **Hanya** mahasiswa pemilik submission | `auth` + `role:mahasiswa` + `abort_unless` cek `user_id`   | **Rendah** — sama seperti No. 18 |
| 20 | `GET mahasiswa/submissions/{submission}/grades` | `Submission $submission` | **Hanya** mahasiswa pemilik submission (melihat nilainya sendiri) | `auth` + `role:mahasiswa` + `abort_unless` cek `user_id`   | **Rendah** — sudah dicek `$submission->user_id === auth()->id()` |
| 21 | `PATCH notifications/{notification}` | `DatabaseNotification $notification` | **Hanya** pemilik notifikasi tersebut | `auth` saja  — **tidak ada** pengecekan `$notification->notifiable_id === auth()->id()` | **TINGGI** — Siapa pun yang login bisa menandai notifikasi milik user lain sebagai sudah dibaca dengan mengganti `{notification}` UUID di URL. Tidak ada pengecekan kepemilikan sama sekali |
| 22 | `GET dosen/courses` (index) | — (tanpa parameter model) | Hanya MK yang diampu dosen yang login | `auth` + `role:dosen`, tetapi method `index()` **kosong** (belum diimplementasi)  | **TINGGI** — Ketika diimplementasi, jika tidak memfilter `where('lecturer_id', auth()->id())`, dosen bisa melihat semua MK termasuk milik dosen lain |
| 23 | `GET mahasiswa/courses` (index) | — (tanpa parameter model) | Hanya MK yang diikuti mahasiswa yang login | `auth` + `role:mahasiswa`, tetapi method `index()` **kosong** (belum diimplementasi)  | **TINGGI** — Ketika diimplementasi, jika tidak memfilter via enrollment, mahasiswa bisa melihat semua MK |

## BREAK

#### Soal
| # | Yang dicoba | Yang harus Anda amati |
|---|-------------|------------------------|
| 1 | Login sebagai mahasiswa A. Buka submission milik mahasiswa B dengan mengubah angka di URL | **IDOR nyata di aplikasi Anda sendiri** |
| 2 | Buka `/courses/1/assignments/99` di mana tugas 99 milik mata kuliah lain | Nested route tanpa scoping |
| 3 | Aktifkan `Route::scopeBindings()`, ulangi nomor 2 | Bandingkan hasilnya |
| 4 | Daftarkan middleware di `app/Http/Kernel.php` seperti tutorial lama | Berkas itu tidak ada — kenali gejalanya |
| 5 | Pasang `role:admin` pada grup, lalu akses sebagai dosen | 403 dari middleware |
| 6 | Sebagai dosen A, edit mata kuliah milik dosen B (keduanya lolos `role:dosen`) | **Middleware saja tidak cukup** |


#### Jawab
| # | Yang Dirusak | Prediksi | Hasil Sebenarnya |
| :-: | :--- | :--- | :--- |
| 1 | Login sebagai Mahasiswa **Andi Pratama** (`id=5`) via `/login?role=mahasiswa`, lalu buka `/mahasiswa/submissions/1` — submission milik **Citra Kirana** (`user_id=32`) | Seharusnya ditolak karena controller `Mahasiswa\SubmissionController@show` punya `abort_unless($submission->user_id === auth()->id(), 403)` | **403 Forbidden**  — Pengecekan `abort_unless` di controller berhasil menolak akses. Andi (`id=5`) ≠ pemilik submission (`id=32`). Namun, perlindungan ini bersifat *inline* di controller, bukan Policy terpusat. Jika developer lupa menambahkan baris ini di method baru, IDOR bisa lolos. |
| 2 | Login sebagai **Dr. Budi Santoso** (dosen, `user_id=2`) via `/login?role=dosen`, lalu buka `/dosen/courses/1/assignments/7` — Assignment 7 (Desain ERD Toko Online) milik **Course 3** (Basis Data), bukan Course 1 (Algoritma dan Pemrograman) yang tertulis di URL | Karena `scopeBindings()` **sudah aktif** di grup route dosen, Laravel akan memvalidasi bahwa `{assignment}` benar-benar merupakan child dari `{course}` yang ada di URL. Assignment 7 bukan milik Course 1, jadi seharusnya **404** | **404 Not Found**  — `scopeBindings()` bekerja: Laravel gagal menemukan Assignment dengan `id=7` yang sekaligus memiliki `course_id=1`, sehingga otomatis mengembalikan 404. Ini mencegah IDOR bersarang (*nested IDOR*) di mana penyerang menyisipkan ID child dari parent lain. |
| 3 | Ulangi skenario nomor 2 dengan `scopeBindings()` aktif | Di proyek ini `scopeBindings()` **sudah terpasang sejak awal** di grup route dosen dan mahasiswa (`routes/web.php` baris 163 dan 207). Hasilnya akan **sama persis** dengan nomor 2: **404 Not Found** | **404 Not Found**  — Tidak ada perbedaan karena `scopeBindings()` memang sudah aktif. **Perbandingan konseptual:** Tanpa `scopeBindings()`, Laravel hanya meng-query `assignments WHERE id = 7` tanpa peduli `course_id`. Assignment 7 ditemukan, lalu `abort_unless` di controller mengecek `$assignment->course->lecturer_id === auth()->id()` → Assignment 7 milik Course 3 (lecturer Prof. Siti, `id=3`) ≠ Dr. Budi (`id=2`) → **403**. Dengan `scopeBindings()`, Laravel langsung query `assignments WHERE id = 7 AND course_id = 1` → tidak ditemukan → **404** (lebih aman: tidak membocorkan bahwa resource tersebut *ada* di tempat lain). |
| 4 | Coba buat/edit file `app/Http/Kernel.php` untuk mendaftarkan middleware `role` di dalamnya, mengikuti tutorial Laravel 10 atau lebih lama | File `app/Http/Kernel.php` **tidak ada** di Laravel 12 (proyek ini). Middleware didaftarkan di `bootstrap/app.php` menggunakan `->withMiddleware()`. Jika developer membuat file `Kernel.php` secara manual, Laravel **tidak akan membacanya** karena arsitektur bootstrap sudah berubah | **File tidak ditemukan**  — `app/Http/Kernel.php` memang tidak ada di project ini (`Test-Path` mengembalikan `False`). Di Laravel 12, middleware alias didaftarkan di `bootstrap/app.php` baris 13–16: `$middleware->alias(['role' => EnsureUserHasRole::class])`. Hampir semua tutorial di internet dan jawaban AI (ChatGPT, dsb.) akan menyuruh mengedit `Kernel.php` — ini adalah jebakan bagi developer yang tidak membaca dokumentasi versi terbaru. **Gejalanya:** middleware yang ditulis di `Kernel.php` tidak pernah dijalankan, seolah-olah tidak terdaftar. |
| 5 | Login sebagai **Dr. Budi Santoso** (dosen, `user_id=2`) via `/login?role=dosen`, lalu akses route admin: `/admin/users` dan `/admin/courses` | Route admin dilindungi middleware `role:admin`. Middleware `EnsureUserHasRole` mengecek `in_array($request->user()->role, $roles)`. Role Dr. Budi adalah `dosen`, bukan `admin` → `abort(403)` | **403 Forbidden** dengan pesan "*Anda tidak memiliki akses ke halaman ini.*" ✅ — Middleware `role:admin` berhasil memblokir dosen. Ini menunjukkan middleware berfungsi sebagai penjaga **level area** (boleh masuk zona admin atau tidak). Tetapi middleware **tidak bisa** membedakan antar-individu dalam role yang sama (lihat nomor 6). |
| 6 | Login sebagai **Dr. Budi Santoso** (dosen, `user_id=2`) via `/login?role=dosen`, lalu akses `/dosen/courses/3` — Course 3 (Basis Data) milik **Prof. Siti Rahayu** (`lecturer_id=3`). Keduanya sama-sama berstatus `dosen` | Middleware `role:dosen` akan meloloskan request karena Dr. Budi memang berstatus dosen. Namun `Dosen\CourseController@show` punya `abort_unless($course->lecturer_id === auth()->id(), 403)` → `lecturer_id=3` ≠ `auth()->id()=2` → **403** | **403 Forbidden** ✅ — Middleware `role:dosen` **meloloskan** request (Dr. Budi memang dosen), tetapi pengecekan *inline* `abort_unless` di controller berhasil menolak. **Pelajaran utama:** Middleware hanya menjawab *"Apakah Anda seorang dosen?"*, bukan *"Apakah ini mata kuliah Anda?"*. Untuk pertanyaan kedua, diperlukan **otorisasi level objek** — saat ini dilakukan dengan `abort_unless` manual, idealnya menggunakan **Policy** Laravel (minggu 7). |
