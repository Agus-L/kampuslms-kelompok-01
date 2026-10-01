# Nama: Aliya Labibah
## NIM:10241007

## READ — Peta route Anda sendiri

### 1. Jalankan `php artisan route:list --except-vendor`. Salin keluarannya ke catatan.

```text
  GET|HEAD        / ......................................................................... home › routes/web.php:60
  GET|HEAD        admin/courses ......................................... admin.courses.index › CourseController@index
  POST            admin/courses ......................................... admin.courses.store › CourseController@store
  GET|HEAD        admin/courses/create ................................ admin.courses.create › CourseController@create
  GET|HEAD        admin/courses/{course} .................................. admin.courses.show › CourseController@show
  PUT|PATCH       admin/courses/{course} .............................. admin.courses.update › CourseController@update
  DELETE          admin/courses/{course} ............................ admin.courses.destroy › CourseController@destroy
  GET|HEAD        admin/courses/{course}/edit ............................. admin.courses.edit › CourseController@edit
  GET|HEAD        admin/courses/{course}/enrollments admin.courses.enrollments.index › Admin\EnrollmentController@index
  POST            admin/courses/{course}/enrollments admin.courses.enrollments.store › Admin\EnrollmentController@store
  GET|HEAD        admin/courses/{course}/enrollments/create admin.courses.enrollments.create › Admin\EnrollmentController@create
  DELETE          admin/enrollments/{enrollment} ...... admin.enrollments.destroy › Admin\EnrollmentController@destroy
  GET|HEAD        admin/users ............................................... admin.users.index › UserController@index
  POST            admin/users ............................................... admin.users.store › UserController@store
  GET|HEAD        admin/users/create ...................................... admin.users.create › UserController@create
  PUT|PATCH       admin/users/{user} ...................................... admin.users.update › UserController@update
  DELETE          admin/users/{user} .................................... admin.users.destroy › UserController@destroy
  GET|HEAD        admin/users/{user}/edit ..................................... admin.users.edit › UserController@edit
  GET|HEAD        dashboard .................................................... dashboard › DashboardController@index
  GET|HEAD        dosen/assignments/{assignment} ............ dosen.assignments.show › Dosen\AssignmentController@show
  PUT|PATCH       dosen/assignments/{assignment} ........ dosen.assignments.update › Dosen\AssignmentController@update
  DELETE          dosen/assignments/{assignment} ...... dosen.assignments.destroy › Dosen\AssignmentController@destroy
  GET|HEAD        dosen/assignments/{assignment}/edit ....... dosen.assignments.edit › Dosen\AssignmentController@edit
  GET|HEAD        dosen/assignments/{assignment}/submissions dosen.assignments.submissions.index › Dosen\SubmissionController@index
  GET|HEAD        dosen/courses ................................... dosen.courses.index › Dosen\CourseController@index
  GET|HEAD        dosen/courses/{course} ............................ dosen.courses.show › Dosen\CourseController@show
  GET|HEAD        dosen/courses/{course}/assignments dosen.courses.assignments.index › Dosen\AssignmentController@index
  POST            dosen/courses/{course}/assignments dosen.courses.assignments.store › Dosen\AssignmentController@store
  GET|HEAD        dosen/courses/{course}/assignments/create dosen.courses.assignments.create › Dosen\AssignmentController@create
  GET|HEAD        dosen/courses/{course}/materials .... dosen.courses.materials.index › Dosen\MaterialController@index
  POST            dosen/courses/{course}/materials .... dosen.courses.materials.store › Dosen\MaterialController@store
  GET|HEAD        dosen/courses/{course}/materials/create dosen.courses.materials.create › Dosen\MaterialController@create
  PUT|PATCH       dosen/grades/{grade} ............................ dosen.grades.update › Dosen\GradeController@update
  GET|HEAD        dosen/materials/{material} .................... dosen.materials.show › Dosen\MaterialController@show
  PUT|PATCH       dosen/materials/{material} ................ dosen.materials.update › Dosen\MaterialController@update
  DELETE          dosen/materials/{material} .............. dosen.materials.destroy › Dosen\MaterialController@destroy
  GET|HEAD        dosen/materials/{material}/edit ............... dosen.materials.edit › Dosen\MaterialController@edit
  GET|HEAD        dosen/submissions/{submission} ............ dosen.submissions.show › Dosen\SubmissionController@show
  POST            dosen/submissions/{submission}/grades . dosen.submissions.grades.store › Dosen\GradeController@store
  GET|HEAD        login .................................................................... login › routes/web.php:75
  GET|HEAD        logout .................................................................. logout › routes/web.php:94
  GET|HEAD        mahasiswa/assignments/{assignment} mahasiswa.assignments.show › Mahasiswa\AssignmentController@show
  GET|HEAD        mahasiswa/assignments/{assignment}/submissions mahasiswa.assignments.submissions.index › Mahasiswa\SubmissionController@index
  POST            mahasiswa/assignments/{assignment}/submissions mahasiswa.assignments.submissions.store › Mahasiswa\SubmissionController@store
  GET|HEAD        mahasiswa/assignments/{assignment}/submissions/create mahasiswa.assignments.submissions.create › Mahasiswa\SubmissionController@create
  GET|HEAD        mahasiswa/courses ....................... mahasiswa.courses.index › Mahasiswa\CourseController@index
  GET|HEAD        mahasiswa/courses/{course} ................ mahasiswa.courses.show › Mahasiswa\CourseController@show
  GET|HEAD        mahasiswa/courses/{course}/assignments mahasiswa.courses.assignments.index › Mahasiswa\AssignmentController@index
  GET|HEAD        mahasiswa/courses/{course}/materials mahasiswa.courses.materials.index › Mahasiswa\MaterialController@index
  GET|HEAD        mahasiswa/materials/{material} ........ mahasiswa.materials.show › Mahasiswa\MaterialController@show
  GET|HEAD        mahasiswa/submissions/{submission} mahasiswa.submissions.show › Mahasiswa\SubmissionController@show
  PUT|PATCH       mahasiswa/submissions/{submission} mahasiswa.submissions.update › Mahasiswa\SubmissionController@update
  GET|HEAD        mahasiswa/submissions/{submission}/edit mahasiswa.submissions.edit › Mahasiswa\SubmissionController@edit
  GET|HEAD        mahasiswa/submissions/{submission}/grades mahasiswa.submissions.grades.index › Mahasiswa\GradeController@index
  GET|HEAD        notifications ................................... notifications.index › NotificationController@index
  PATCH           notifications/{notification} .................. notifications.update › NotificationController@update
  GET|HEAD        tentang ................................................................ tentang › routes/web.php:64

                                                                                                   Showing [57] routes
```

---

### 2. Tandai setiap route yang menerima parameter model ({course}, {assignment}, dst).

Route di bawah ini menerima parameter model (ditandai dengan kurung kurawal `{...}`). Parameter ini membuat route berpotensi rawan terhadap celah keamanan **IDOR** jika tidak dibentengi dengan otorisasi kepemilikan. Total ada **38 route**:

| Method | URI / Route | Parameter Model | Nama Route | Action / Controller |
| :--- | :--- | :--- | :--- | :--- |
| `GET\|HEAD` | `admin/courses/{course}` | `{course}` | `admin.courses.show` | `CourseController@show` |
| `PUT\|PATCH` | `admin/courses/{course}` | `{course}` | `admin.courses.update` | `CourseController@update` |
| `DELETE` | `admin/courses/{course}` | `{course}` | `admin.courses.destroy` | `CourseController@destroy` |
| `GET\|HEAD` | `admin/courses/{course}/edit` | `{course}` | `admin.courses.edit` | `CourseController@edit` |
| `GET\|HEAD` | `admin/courses/{course}/enrollments` | `{course}` | `admin.courses.enrollments.index` | `Admin\EnrollmentController@index` |
| `POST` | `admin/courses/{course}/enrollments` | `{course}` | `admin.courses.enrollments.store` | `Admin\EnrollmentController@store` |
| `GET\|HEAD` | `admin/courses/{course}/enrollments/create` | `{course}` | `admin.courses.enrollments.create` | `Admin\EnrollmentController@create` |
| `DELETE` | `admin/enrollments/{enrollment}` | `{enrollment}` | `admin.enrollments.destroy` | `Admin\EnrollmentController@destroy` |
| `PUT\|PATCH` | `admin/users/{user}` | `{user}` | `admin.users.update` | `UserController@update` |
| `DELETE` | `admin/users/{user}` | `{user}` | `admin.users.destroy` | `UserController@destroy` |
| `GET\|HEAD` | `admin/users/{user}/edit` | `{user}` | `admin.users.edit` | `UserController@edit` |
| `GET\|HEAD` | `dosen/assignments/{assignment}` | `{assignment}` | `dosen.assignments.show` | `Dosen\AssignmentController@show` |
| `PUT\|PATCH` | `dosen/assignments/{assignment}` | `{assignment}` | `dosen.assignments.update` | `Dosen\AssignmentController@update` |
| `DELETE` | `dosen/assignments/{assignment}` | `{assignment}` | `dosen.assignments.destroy` | `Dosen\AssignmentController@destroy` |
| `GET\|HEAD` | `dosen/assignments/{assignment}/edit` | `{assignment}` | `dosen.assignments.edit` | `Dosen\AssignmentController@edit` |
| `GET\|HEAD` | `dosen/assignments/{assignment}/submissions` | `{assignment}` | `dosen.assignments.submissions.index` | `Dosen\SubmissionController@index` |
| `GET\|HEAD` | `dosen/courses/{course}` | `{course}` | `dosen.courses.show` | `Dosen\CourseController@show` |
| `GET\|HEAD` | `dosen/courses/{course}/assignments` | `{course}` | `dosen.courses.assignments.index` | `Dosen\AssignmentController@index` |
| `POST` | `dosen/courses/{course}/assignments` | `{course}` | `dosen.courses.assignments.store` | `Dosen\AssignmentController@store` |
| `GET\|HEAD` | `dosen/courses/{course}/assignments/create` | `{course}` | `dosen.courses.assignments.create` | `Dosen\AssignmentController@create` |
| `GET\|HEAD` | `dosen/courses/{course}/materials` | `{course}` | `dosen.courses.materials.index` | `Dosen\MaterialController@index` |
| `POST` | `dosen/courses/{course}/materials` | `{course}` | `dosen.courses.materials.store` | `Dosen\MaterialController@store` |
| `GET\|HEAD` | `dosen/courses/{course}/materials/create` | `{course}` | `dosen.courses.materials.create` | `Dosen\MaterialController@create` |
| `PUT\|PATCH` | `dosen/grades/{grade}` | `{grade}` | `dosen.grades.update` | `Dosen\GradeController@update` |
| `GET\|HEAD` | `dosen/materials/{material}` | `{material}` | `dosen.materials.show` | `Dosen\MaterialController@show` |
| `PUT\|PATCH` | `dosen/materials/{material}` | `{material}` | `dosen.materials.update` | `Dosen\MaterialController@update` |
| `DELETE` | `dosen/materials/{material}` | `{material}` | `dosen.materials.destroy` | `Dosen\MaterialController@destroy` |
| `GET\|HEAD` | `dosen/materials/{material}/edit` | `{material}` | `dosen.materials.edit` | `Dosen\MaterialController@edit` |
| `GET\|HEAD` | `dosen/submissions/{submission}` | `{submission}` | `dosen.submissions.show` | `Dosen\SubmissionController@show` |
| `POST` | `dosen/submissions/{submission}/grades` | `{submission}` | `dosen.submissions.grades.store` | `Dosen\GradeController@store` |
| `GET\|HEAD` | `mahasiswa/assignments/{assignment}` | `{assignment}` | `mahasiswa.assignments.show` | `Mahasiswa\AssignmentController@show` |
| `GET\|HEAD` | `mahasiswa/assignments/{assignment}/submissions` | `{assignment}` | `mahasiswa.assignments.submissions.index` | `Mahasiswa\SubmissionController@index` |
| `POST` | `mahasiswa/assignments/{assignment}/submissions` | `{assignment}` | `mahasiswa.assignments.submissions.store` | `Mahasiswa\SubmissionController@store` |
| `GET\|HEAD` | `mahasiswa/assignments/{assignment}/submissions/create` | `{assignment}` | `mahasiswa.assignments.submissions.create` | `Mahasiswa\SubmissionController@create` |
| `GET\|HEAD` | `mahasiswa/courses/{course}` | `{course}` | `mahasiswa.courses.show` | `Mahasiswa\CourseController@show` |
| `GET\|HEAD` | `mahasiswa/courses/{course}/assignments` | `{course}` | `mahasiswa.courses.assignments.index` | `Mahasiswa\AssignmentController@index` |
| `GET\|HEAD` | `mahasiswa/courses/{course}/materials` | `{course}` | `mahasiswa.courses.materials.index` | `Mahasiswa\MaterialController@index` |
| `GET\|HEAD` | `mahasiswa/materials/{material}` | `{material}` | `mahasiswa.materials.show` | `Mahasiswa\MaterialController@show` |
| `GET\|HEAD` | `mahasiswa/submissions/{submission}` | `{submission}` | `mahasiswa.submissions.show` | `Mahasiswa\SubmissionController@show` |
| `PUT\|PATCH` | `mahasiswa/submissions/{submission}` | `{submission}` | `mahasiswa.submissions.update` | `Mahasiswa\SubmissionController@update` |
| `GET\|HEAD` | `mahasiswa/submissions/{submission}/edit` | `{submission}` | `mahasiswa.submissions.edit` | `Mahasiswa\SubmissionController@edit` |
| `GET\|HEAD` | `mahasiswa/submissions/{submission}/grades` | `{submission}` | `mahasiswa.submissions.grades.index` | `Mahasiswa\GradeController@index` |
| `PATCH` | `notifications/{notification}` | `{notification}` | `notifications.update` | `NotificationController@update` |

---

### 3. Analisis Hak Akses & Pencegahan Saat Ini

* **Siapa yang seharusnya boleh mengakses?**
  * **Area Admin:** Hanya user dengan role `admin`.
  * **Area Dosen:** Hanya dosen yang **mengampu mata kuliah tersebut**. Dosen lain tidak boleh melihat, mengedit, ataupun menghapus tugas/materi/nilai milik dosen lain.
  * **Area Mahasiswa:** Mahasiswa hanya boleh mengakses mata kuliah yang **diikutinya**. Khusus untuk tugas dan nilai (`submission` & `grade`), mahasiswa **hanya boleh mengakses tugas miliknya sendiri**.
  * **Notifikasi:** Hanya user pemilik notifikasi tersebut.

* **Apa yang saat ini mencegah orang lain?**
  * Di level rute grup, sudah dicegah oleh **Middleware Peran** (`role:admin`, `role:dosen`, `role:mahasiswa`).
  * Namun untuk perlindungan data spesifik (per-objek antar sesama role), pada awal tahapan minggu ini jawabannya adalah **"Belum ada apa-apa"**. Middleware hanya mengecek *"Apakah kamu dosen?"*, bukan *"Apakah ini mata kuliahmu?"*. Oleh karena itu, kita memasang gembok darurat `abort_unless()` di controller sebelum nantinya dirapikan menggunakan Policy pada Minggu 7.

---

### 4. Daftar Titik Rawan IDOR

| No | URL | Method | Parameter Model | Siapa yang Seharusnya Boleh Akses | Perlindungan Saat Ini | Status |
|:---:|:---|:---|:---:|:---|:---|:---:|
| 1 | `admin/courses/{course}` | GET, PUT, DELETE | `{course}` | Admin saja | Middleware `role:admin` | ⚠️ Cukup — admin hanya 1 peran, risiko rendah |
| 2 | `admin/courses/{course}/edit` | GET | `{course}` | Admin saja | Middleware `role:admin` | ⚠️ Cukup — admin hanya 1 peran, risiko rendah |
| 3 | `admin/courses/{course}/enrollments` | GET, POST | `{course}` | Admin saja | Middleware `role:admin` | ⚠️ Cukup — admin hanya 1 peran, risiko rendah |
| 4 | `admin/courses/{course}/enrollments/create` | GET | `{course}` | Admin saja | Middleware `role:admin` | ⚠️ Cukup — admin hanya 1 peran, risiko rendah |
| 5 | `admin/enrollments/{enrollment}` | DELETE | `{enrollment}` | Admin saja | Middleware `role:admin` | ⚠️ Cukup — admin hanya 1 peran, risiko rendah |
| 6 | `admin/users/{user}` | PUT, DELETE, GET (edit) | `{user}` | Admin saja | Middleware `role:admin` | ⚠️ Cukup — admin hanya 1 peran, risiko rendah |
| 7 | `dosen/courses/{course}` | GET | `{course}` | Dosen pengampu MK tersebut + Admin | Middleware `role:dosen` saja — dosen lain bisa akses MK yang bukan miliknya | ❌ **IDOR — belum ada cek `lecturer_id`** |
| 8 | `dosen/courses/{course}/assignments` | GET, POST | `{course}` | Dosen pengampu MK tersebut | Middleware `role:dosen` saja | ❌ **IDOR — dosen lain bisa membuat tugas di MK orang lain** |
| 9 | `dosen/courses/{course}/assignments/create` | GET | `{course}` | Dosen pengampu MK tersebut | Middleware `role:dosen` saja | ❌ **IDOR — belum ada cek kepemilikan MK** |
| 10 | `dosen/assignments/{assignment}` | GET, PUT, DELETE | `{assignment}` | Dosen pengampu MK yang punya tugas ini | Middleware `role:dosen` saja | ❌ **IDOR — dosen lain bisa edit/hapus tugas milik dosen lain** |
| 11 | `dosen/assignments/{assignment}/edit` | GET | `{assignment}` | Dosen pengampu MK yang punya tugas ini | Middleware `role:dosen` saja | ❌ **IDOR — belum ada cek kepemilikan** |
| 12 | `dosen/assignments/{assignment}/submissions` | GET | `{assignment}` | Dosen pengampu MK terkait + Admin | Middleware `role:dosen` saja | ❌ **IDOR — dosen A bisa melihat submission di MK dosen B** |
| 13 | `dosen/courses/{course}/materials` | GET, POST | `{course}` | Dosen pengampu MK tersebut | Middleware `role:dosen` saja | ❌ **IDOR — dosen lain bisa upload materi di MK orang lain** |
| 14 | `dosen/courses/{course}/materials/create` | GET | `{course}` | Dosen pengampu MK tersebut | Middleware `role:dosen` saja | ❌ **IDOR — belum ada cek kepemilikan MK** |
| 15 | `dosen/materials/{material}` | GET, PUT, DELETE | `{material}` | Dosen pengampu MK yang punya materi ini | Middleware `role:dosen` saja | ❌ **IDOR — dosen lain bisa edit/hapus materi bukan miliknya** |
| 16 | `dosen/materials/{material}/edit` | GET | `{material}` | Dosen pengampu MK yang punya materi ini | Middleware `role:dosen` saja | ❌ **IDOR — belum ada cek kepemilikan** |
| 17 | `dosen/submissions/{submission}` | GET | `{submission}` | Dosen pengampu MK terkait + Admin | Middleware `role:dosen` saja | ❌ **IDOR — dosen A bisa baca submission di MK dosen B** |
| 18 | `dosen/submissions/{submission}/grades` | POST | `{submission}` | Dosen pengampu MK terkait | Middleware `role:dosen` saja | ❌ **IDOR — dosen A bisa memberi nilai di MK dosen B** |
| 19 | `dosen/grades/{grade}` | PUT | `{grade}` | Dosen yang membuat nilai tersebut | Middleware `role:dosen` saja | ❌ **IDOR — dosen lain bisa mengubah nilai milik dosen lain** |
| 20 | `mahasiswa/courses/{course}` | GET | `{course}` | Mahasiswa yang terdaftar di MK tersebut | Middleware `role:mahasiswa` saja | ❌ **IDOR — mahasiswa bisa lihat MK yang tidak diikutinya** |
| 21 | `mahasiswa/courses/{course}/assignments` | GET | `{course}` | Mahasiswa yang terdaftar di MK tersebut | Middleware `role:mahasiswa` saja | ❌ **IDOR — mahasiswa bisa lihat tugas MK yang tidak diikutinya** |
| 22 | `mahasiswa/courses/{course}/materials` | GET | `{course}` | Mahasiswa yang terdaftar di MK tersebut | Middleware `role:mahasiswa` saja | ❌ **IDOR — mahasiswa bisa lihat materi MK yang tidak diikutinya** |
| 23 | `mahasiswa/assignments/{assignment}` | GET | `{assignment}` | Mahasiswa yang terdaftar di MK pemilik tugas ini | Middleware `role:mahasiswa` saja | ❌ **IDOR — mahasiswa dari MK lain bisa lihat tugas** |
| 24 | `mahasiswa/assignments/{assignment}/submissions` | GET, POST | `{assignment}` | Mahasiswa yang terdaftar di MK terkait | Middleware `role:mahasiswa` saja | ❌ **IDOR — mahasiswa lain bisa membuat/melihat submission** |
| 25 | `mahasiswa/assignments/{assignment}/submissions/create` | GET | `{assignment}` | Mahasiswa yang terdaftar di MK terkait | Middleware `role:mahasiswa` saja | ❌ **IDOR — mahasiswa dari MK lain bisa akses form** |
| 26 | `mahasiswa/materials/{material}` | GET | `{material}` | Mahasiswa yang terdaftar di MK pemilik materi ini | Middleware `role:mahasiswa` saja | ❌ **IDOR — mahasiswa dari MK lain bisa baca materi** |
| 27 | `mahasiswa/submissions/{submission}` | GET, PUT | `{submission}` | Mahasiswa pemilik submission itu saja | Middleware `role:mahasiswa` saja | ❌ **IDOR KRITIS — mahasiswa bisa baca/edit submission orang lain + nilainya** |
| 28 | `mahasiswa/submissions/{submission}/edit` | GET | `{submission}` | Mahasiswa pemilik submission itu saja | Middleware `role:mahasiswa` saja | ❌ **IDOR KRITIS — mahasiswa bisa akses form edit milik orang lain** |
| 29 | `mahasiswa/submissions/{submission}/grades` | GET | `{submission}` | Mahasiswa pemilik submission itu saja | Middleware `role:mahasiswa` saja | ❌ **IDOR KRITIS — mahasiswa bisa melihat nilai milik orang lain** |
| 30 | `notifications/{notification}` | PATCH | `{notification}` | Pemilik notifikasi itu saja (semua peran) | Middleware `auth` saja | ❌ **IDOR — user lain bisa menandai notifikasi milik orang lain sebagai dibaca** |

#### Keterangan Status:
* ✅ **Aman** — ada pemeriksaan kepemilikan/policy yang benar
* ⚠️ **Cukup untuk saat ini** — risiko rendah karena hanya 1 tipe pengguna (admin)
* ❌ **IDOR — belum aman** — hanya ada middleware role, belum ada cek per-objek

## BREAK

| # | Yang dirusak | Prediksi | Hasil Sebenarnya |
| --- | --- | --- | --- |
| 1 | Login sebagai mahasiswa A. Buka submission milik mahasiswa B dengan mengubah angka di URL (menonaktifkan proteksi `abort_unless` kepemilikan di controller) | Sistem akan otomatis menolak akses atau melempar error 403 karena mahasiswa A tidak berhak melihat tugas milik mahasiswa B. |  **Muncul halaman 403 Akses Ditolak.** Meskipun mahasiswa A (Andi Pratama) berhasil masuk ke area mahasiswa, ia tetap tidak bisa membuka URL `/mahasiswa/submissions/1`. Hal ini karena sistem memeriksa pemilik tugas tersebut: submission #1 bukan milik Andi, melainkan milik mahasiswa lain. Percobaan ini membuktikan bahwa middleware peran (`role:mahasiswa`) saja tidak cukup—aplikasi juga wajib mengecek apakah data yang dibuka benar-benar milik pengguna yang sedang login agar terhindar dari celah **IDOR (Insecure Direct Object Reference)**. |
| 2 | Buka `/courses/1/assignments/99` di mana tugas 99 milik mata kuliah lain (Nested route tanpa scoping) | Sistem akan otomatis menolak karena tugas tersebut bukan milik mata kuliah ID 1. | **Tanpa `scopeBindings()`, sistem tetap menampilkan tugas tersebut.** Hal ini terjadi karena Laravel hanya memeriksa: apakah mata kuliah ada, dan apakah tugas ada di database. Laravel tidak mengecek apakah tugas itu benar-benar bagian dari mata kuliah tersebut. Akibatnya, tugas dari mata kuliah lain (Course 2) bisa bocor dan ikut tampil di halaman mata kuliah yang salah (Course 1).|
| 3 | Aktifkan `Route::scopeBindings()`, ulangi nomor 2 | Tampilannya akan tetap sama seperti nomor 2 karena URL yang dibuka sama. | **Muncul halaman 404 (Halaman Tidak Ditemukan).** Begitu `scopeBindings()` diaktifkan, Laravel otomatis mengecek kecocokan tugas dengan mata kuliahnya. Karena tugas tersebut bukan milik mata kuliah ID 1, sistem langsung menolak dan menganggap halaman itu tidak ada. Ini membuktikan fitur ini sangat penting agar tugas dari mata kuliah lain tidak nyasar dan bisa dibuka lewat alamat yang salah|
| 4 | Daftarkan middleware di `app/Http/Kernel.php` seperti tutorial lama | Mengira file `Kernel.php` akan ditemukan di folder `app/Http/` untuk mendaftarkan nama panggilan (alias) middleware baru. | **File `Kernel.php` sama sekali tidak ada / tidak ditemukan.** Di dalam folder `app/Http/` hanya terdapat sub-folder `Controllers`, `Middleware`, dan `Requests`. Hal ini membuktikan bahwa pada Laravel versi terbaru (Laravel 12), file `Kernel.php` sudah resmi dihapus dari sistem. Pendaftaran middleware sekarang dipindahkan ke file `bootstrap/app.php` menggunakan method `->withMiddleware()`. |
| 5 | Pasang `role:admin` pada grup dosen, lalu akses sebagai dosen | Sistem akan menolak akses dan memunculkan error 403 karena dosen tidak memiliki hak akses admin. | **Muncul halaman 403 Akses Ditolak.** Satpam pintu gerbang (middleware) berhasil mencegat request. Karena pengguna yang sedang login adalah `dosen`, sedangkan halaman tersebut mewajibkan peran `admin`, sistem langsung menghentikan langkah pengguna sebelum halaman sempat terbuka. Ini membuktikan bahwa middleware bekerja dengan baik untuk membatasi halaman agar hanya bisa dibuka oleh jenis akun yang tepat. |
| 6 | Sebagai dosen A, coba akses/edit mata kuliah milik dosen B (keduanya lolos `role:dosen`) | Dosen A dapat membuka mata kuliah dosen B karena sama-sama memiliki peran sebagai dosen. | **Muncul halaman 403 Akses Ditolak.** Meskipun Dosen A (Dr. Budi) berhasil lolos dari satpam middleware karena memang berstatus dosen, sistem menolak akses saat membuka mata kuliah milik Dosen B (Prof. Siti). Hal ini terjadi karena aplikasi memeriksa kepemilikan pengampu di dalam controller. Percobaan ini membuktikan bahwa **middleware peran saja tidak cukup** sistem wajib memeriksa kepemilikan data agar sesama dosen tidak bisa saling melihat atau mengedit mata kuliah milik dosen lain.|