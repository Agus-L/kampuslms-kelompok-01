## Nama: Aan Mardiah
## NIM:10241001

### READ
1. Jalankan `php artisan route:list --except-vendor`. Salin keluarannya ke catatan.

    ``` GET|HEAD        / ................................... home › routes/web.php:60
    GET|HEAD        admin/courses ... admin.courses.index › CourseController@index
    POST            admin/courses ... admin.courses.store › CourseController@store
    GET|HEAD        admin/courses/create admin.courses.create › CourseController@…
    GET|HEAD        admin/courses/{course} admin.courses.show › CourseController@…
    PUT|PATCH       admin/courses/{course} admin.courses.update › CourseControlle…
    DELETE          admin/courses/{course} admin.courses.destroy › CourseControll…
    GET|HEAD        admin/courses/{course}/edit admin.courses.edit › CourseContro…
    GET|HEAD        admin/courses/{course}/enrollments admin.courses.enrollments.…
    POST            admin/courses/{course}/enrollments admin.courses.enrollments.…
    GET|HEAD        admin/courses/{course}/enrollments/create admin.courses.enrol…  
    DELETE          admin/enrollments/{enrollment} admin.enrollments.destroy › Ad…  
    GET|HEAD        admin/users ......... admin.users.index › UserController@index
    POST            admin/users ......... admin.users.store › UserController@store  
    GET|HEAD        admin/users/create admin.users.create › UserController@create   
    PUT|PATCH       admin/users/{user} admin.users.update › UserController@update   
    DELETE          admin/users/{user} admin.users.destroy › UserController@destr…  
    GET|HEAD        admin/users/{user}/edit admin.users.edit › UserController@edit  
    GET|HEAD        dashboard .............. dashboard › DashboardController@index  
    GET|HEAD        dosen/assignments/{assignment} dosen.assignments.show › Dosen…  
    PUT|PATCH       dosen/assignments/{assignment} dosen.assignments.update › Dos…  
    DELETE          dosen/assignments/{assignment} dosen.assignments.destroy › Do…
    GET|HEAD        dosen/assignments/{assignment}/edit dosen.assignments.edit › …  
    GET|HEAD        dosen/assignments/{assignment}/submissions dosen.assignments.…
    GET|HEAD        dosen/courses dosen.courses.index › Dosen\CourseController@in…  
    GET|HEAD        dosen/courses/{course} dosen.courses.show › Dosen\CourseContr…  
    GET|HEAD        dosen/courses/{course}/assignments dosen.courses.assignments.…
    POST            dosen/courses/{course}/assignments dosen.courses.assignments.…  
    GET|HEAD        dosen/courses/{course}/assignments/create dosen.courses.assig…
    GET|HEAD        dosen/courses/{course}/materials dosen.courses.materials.inde…  
    POST            dosen/courses/{course}/materials dosen.courses.materials.stor…  
    GET|HEAD        dosen/courses/{course}/materials/create dosen.courses.materia…  
    PUT|PATCH       dosen/grades/{grade} dosen.grades.update › Dosen\GradeControl…
    GET|HEAD        dosen/materials/{material} dosen.materials.show › Dosen\Mater…  
    PUT|PATCH       dosen/materials/{material} dosen.materials.update › Dosen\Mat…
    DELETE          dosen/materials/{material} dosen.materials.destroy › Dosen\Ma…  
    GET|HEAD        dosen/materials/{material}/edit dosen.materials.edit › Dosen\…  
    GET|HEAD        dosen/submissions/{submission} dosen.submissions.show › Dosen…  
    POST            dosen/submissions/{submission}/grades dosen.submissions.grade…  
    GET|HEAD        login .............................. login › routes/web.php:75  
    GET|HEAD        logout ............................ logout › routes/web.php:94
    GET|HEAD        mahasiswa/assignments/{assignment} mahasiswa.assignments.show…  
    GET|HEAD        mahasiswa/assignments/{assignment}/submissions mahasiswa.assi…  
    POST            mahasiswa/assignments/{assignment}/submissions mahasiswa.assi…  
    GET|HEAD        mahasiswa/assignments/{assignment}/submissions/create mahasis…
    GET|HEAD        mahasiswa/courses mahasiswa.courses.index › Mahasiswa\CourseC…  
    GET|HEAD        mahasiswa/courses/{course} mahasiswa.courses.show › Mahasiswa…
    GET|HEAD        mahasiswa/courses/{course}/assignments mahasiswa.courses.assi…  
    GET|HEAD        mahasiswa/courses/{course}/materials mahasiswa.courses.materi…  
    GET|HEAD        mahasiswa/materials/{material} mahasiswa.materials.show › Mah…
    GET|HEAD        mahasiswa/submissions/{submission} mahasiswa.submissions.show…  
    PUT|PATCH       mahasiswa/submissions/{submission} mahasiswa.submissions.upda…  
    GET|HEAD        mahasiswa/submissions/{submission}/edit mahasiswa.submissions…
    GET|HEAD        mahasiswa/submissions/{submission}/grades mahasiswa.submissio…  
    GET|HEAD        notifications notifications.index › NotificationController@in…
    PATCH           notifications/{notification} notifications.update › Notificat…  
    GET|HEAD        tentang .......................... tentang › routes/web.php:64  

                                                                Showing [57] routes  
    ```


2. Tandai setiap route yang menerima parameter model ({course}, {assignment}, dst).     

    | Method | URI / Route | Parameter Model | Nama Route | Action / Controller |
    | :--- | :--- | :--- | :--- | :--- |
    | `GET\|HEAD` | `admin/courses/{course}` | `{course}` | `admin.courses.show` | `CourseController@show` |
    | `PUT\|PATCH` | `admin/courses/{course}` | `{course}` | `admin.courses.update` | `CourseController@update` |
    | `DELETE` | `admin/courses/{course}` | `{course}` | `admin.courses.destroy` | `CourseController@destroy` |
    | `GET\|HEAD` | `admin/courses/{course}/edit` | `{course}` | `admin.courses.edit` | `CourseController@edit` |
    | `GET\|HEAD` | `admin/courses/{course}/enrollments` | `{course}` | `admin.courses.enrollments.index` | `CourseController@enrollments` |
    | `POST` | `admin/courses/{course}/enrollments` | `{course}` | `admin.courses.enrollments.store` | `CourseController@enrollments.store` |
    | `GET\|HEAD` | `admin/courses/{course}/enrollments/create` | `{course}` | `admin.courses.enrollments.create` | `CourseController@enrollments.create` |
    | `DELETE` | `admin/enrollments/{enrollment}` | `{enrollment}` | `admin.enrollments.destroy` | `Admin\EnrollmentController@destroy` |
    | `PUT\|PATCH` | `admin/users/{user}` | `{user}` | `admin.users.update` | `UserController@update` |
    | `DELETE` | `admin/users/{user}` | `{user}` | `admin.users.destroy` | `UserController@destroy` |
    | `GET\|HEAD` | `admin/users/{user}/edit` | `{user}` | `admin.users.edit` | `UserController@edit` |
    | `GET\|HEAD` | `dosen/assignments/{assignment}` | `{assignment}` | `dosen.assignments.show` | `Dosen\AssignmentController@show` |
    | `PUT\|PATCH` | `dosen/assignments/{assignment}` | `{assignment}` | `dosen.assignments.update` | `Dosen\AssignmentController@update` |
    | `DELETE` | `dosen/assignments/{assignment}` | `{assignment}` | `dosen.assignments.destroy` | `Dosen\AssignmentController@destroy` |
    | `GET\|HEAD` | `dosen/assignments/{assignment}/edit` | `{assignment}` | `dosen.assignments.edit` | `Dosen\AssignmentController@edit` |
    | `GET\|HEAD` | `dosen/assignments/{assignment}/submissions` | `{assignment}` | `dosen.assignments.submissions.index` | `Dosen\AssignmentController@submissions` |
    | `GET\|HEAD` | `dosen/courses/{course}` | `{course}` | `dosen.courses.show` | `Dosen\CourseController@show` |
    | `GET\|HEAD` | `dosen/courses/{course}/assignments` | `{course}` | `dosen.courses.assignments.index` | `Dosen\CourseController@assignments` |
    | `POST` | `dosen/courses/{course}/assignments` | `{course}` | `dosen.courses.assignments.store` | `Dosen\CourseController@assignments.store` |
    | `GET\|HEAD` | `dosen/courses/{course}/assignments/create` | `{course}` | `dosen.courses.assignments.create` | `Dosen\CourseController@assignments.create` |
    | `GET\|HEAD` | `dosen/courses/{course}/materials` | `{course}` | `dosen.courses.materials.index` | `Dosen\CourseController@materials` |
    | `POST` | `dosen/courses/{course}/materials` | `{course}` | `dosen.courses.materials.store` | `Dosen\CourseController@materials.store` |
    | `GET\|HEAD` | `dosen/courses/{course}/materials/create` | `{course}` | `dosen.courses.materials.create` | `Dosen\CourseController@materials.create` |
    | `PUT\|PATCH` | `dosen/grades/{grade}` | `{grade}` | `dosen.grades.update` | `Dosen\GradeController@update` |
    | `GET\|HEAD` | `dosen/materials/{material}` | `{material}` | `dosen.materials.show` | `Dosen\MaterialController@show` |
    | `PUT\|PATCH` | `dosen/materials/{material}` | `{material}` | `dosen.materials.update` | `Dosen\MaterialController@update` |
    | `DELETE` | `dosen/materials/{material}` | `{material}` | `dosen.materials.destroy` | `Dosen\MaterialController@destroy` |
    | `GET\|HEAD` | `dosen/materials/{material}/edit` | `{material}` | `dosen.materials.edit` | `Dosen\MaterialController@edit` |
    | `GET\|HEAD` | `dosen/submissions/{submission}` | `{submission}` | `dosen.submissions.show` | `Dosen\SubmissionController@show` |
    | `POST` | `dosen/submissions/{submission}/grades` | `{submission}` | `dosen.submissions.grades.store` | `Dosen\SubmissionController@grades` |
    | `GET\|HEAD` | `mahasiswa/assignments/{assignment}` | `{assignment}` | `mahasiswa.assignments.show` | `Mahasiswa\AssignmentController@show` |
    | `GET\|HEAD` | `mahasiswa/assignments/{assignment}/submissions` | `{assignment}` | `mahasiswa.assignments.submissions.index` | `Mahasiswa\AssignmentController@submissions` |
    | `POST` | `mahasiswa/assignments/{assignment}/submissions` | `{assignment}` | `mahasiswa.assignments.submissions.store` | `Mahasiswa\AssignmentController@submissions.store` |
    | `GET\|HEAD` | `mahasiswa/assignments/{assignment}/submissions/create` | `{assignment}` | `mahasiswa.assignments.submissions.create` | `Mahasiswa\AssignmentController@submissions.create` |
    | `GET\|HEAD` | `mahasiswa/courses/{course}` | `{course}` | `mahasiswa.courses.show` | `Mahasiswa\CourseController@show` |
    | `GET\|HEAD` | `mahasiswa/courses/{course}/assignments` | `{course}` | `mahasiswa.courses.assignments.index` | `Mahasiswa\CourseController@assignments` |
    | `GET\|HEAD` | `mahasiswa/courses/{course}/materials` | `{course}` | `mahasiswa.courses.materials.index` | `Mahasiswa\CourseController@materials` |
    | `GET\|HEAD` | `mahasiswa/materials/{material}` | `{material}` | `mahasiswa.materials.show` | `Mahasiswa\MaterialController@show` |
    | `GET\|HEAD` | `mahasiswa/submissions/{submission}` | `{submission}` | `mahasiswa.submissions.show` | `Mahasiswa\SubmissionController@show` |
    | `PUT\|PATCH` | `mahasiswa/submissions/{submission}` | `{submission}` | `mahasiswa.submissions.update` | `Mahasiswa\SubmissionController@update` |
    | `GET\|HEAD` | `mahasiswa/submissions/{submission}/edit` | `{submission}` | `mahasiswa.submissions.edit` | `Mahasiswa\SubmissionController@edit` |
    | `GET\|HEAD` | `mahasiswa/submissions/{submission}/grades` | `{submission}` | `mahasiswa.submissions.grades.index` | `Mahasiswa\SubmissionController@grades` |
    | `PATCH` | `notifications/{notification}` | `{notification}` | `notifications.update` | `NotificationController@update` |


3. Untuk setiap route bertanda, jawab: siapa saja yang seharusnya boleh mengaksesnya, dan apa yang saat ini mencegah orang lain? Kemungkinan besar jawabannya "belum ada apa-apa" — itu wajar, dan itulah pekerjaan minggu ini dan minggu 7.
4. Buat tabel di docs/minggu-05-<nama>.md berjudul "Daftar Titik Rawan IDOR". Tabel ini akan Anda pakai lagi di minggu 7 dan saat interview.

    ### Daftar Titik Rawan IDOR
    | No | URL | Method | Parameter Model | Siapa yang Seharusnya Boleh Akses | Perlindungan Saat Ini | Status |
    |----|-----|--------|-----------------|-----------------------------------|-----------------------|--------|
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

    ### Keterangan Status:
    - ✅ **Aman** — ada pemeriksaan kepemilikan/policy yang benar
    - ⚠️ **Cukup untuk saat ini** — risiko rendah karena hanya 1 tipe pengguna (admin)
    - ❌ **IDOR — belum aman** — hanya ada middleware role, belum ada cek per-objek



### BREAK 

| # | Yang dicoba | Prediksi | Hasil Sebenarnya |
| --- | --- | --- | --- |
| 1 | Login sebagai mahasiswa A. Buka submission milik mahasiswa B dengan mengubah angka di URL `(/mahasiswa/submissions/{id})` | Sistem akan langsung menampilkan isi submission dan nilai milik mahasiswa B |Akses ditolak dengan respon eror 403. Karena pada controller Mahasiswa\SubmissionController.php di method show(), terdapat pengaman manual berupa baris kode abort_unless($submission->user_id === auth()->id(), 403);. Pengecekan ini bertindak sebagai otorisasi lapis pertama di level controller yang membandingkan apakah user_id pada data submission cocok dengan akun yang sedang login. Karena ID pemilik submission berbeda dengan akun mahasiswa yang login, Laravel langsung menghentikan request dan melempar error 403. Hal ini membuktikan bahwa middleware role:mahasiswa saja belum cukup mengamankan data spesifik (karena sama-sama mahasiswa lolos di pintu gerbang middleware), sehingga proteksi berbasis kepemilikan objek mutlak diperlukan agar celah IDOR tidak terjadi. |
| 2 | Buka /courses/1/assignments/99 di mana tugas 99 milik mata kuliah lain | Sistem akan otomatis menolak akses atau menampilkan error 404 |Halaman berhasil diakses secara normal dan data tugas tetap ditampilkan. Ketika menguji rute bersarang /courses/2/assignments/4 tanpa menggunakan scopeBindings(), sistem menampilkan Course ID 2 (Algoritma dan Pemrograman), namun tugas yang muncul adalah Assignment ID 4 (Implementasi Stack & Queue) yang sebenarnya terdaftar di bawah Course ID 3 (Struktur Data). Hal ini terjadi karena secara default, mekanisme Route Model Binding pada Laravel mengevaluasi parameter {course} dan {assignment} secara terpisah dan independen (hanya memeriksa apakah ID 2 ada di tabel courses dan ID 4 ada di tabel assignments). Tanpa adanya scopeBindings(), Laravel sama sekali tidak memvalidasi hubungan |
| 3 | Aktifkan `Route::scopeBindings()`, ulangi nomor 2 | Sistem akan mendeteksi ketidakcocokan relasi antara mata kuliah dan tugas pada URL |Akses ditolak dengan respon HTTP 404 (Not Found). Setelah menambahkan method ->scopeBindings() pada rute bersarang dan mengakses kembali URL /courses/2/assignments/4, Laravel secara otomatis memvalidasi relasi antara mata kuliah dan tugas tersebut. Alih-alih mencari tugas secara independen di seluruh tabel assignments, scopeBindings() memaksa Route Model Binding untuk mencari tugas melalui relasi model induknya ($course->assignments()). Karena Assignment ID 4 memiliki course_id = 3 (bukan Course ID 2), pencarian pada relasi mata kuliah tersebut menghasilkan nilai null, sehingga Laravel langsung menghentikan request dan melempar error 404. Hal ini membuktikan bahwa scopeBindings() adalah solusi bawaan Laravel yang sangat efektif untuk mengunci integritas relasi orang tua-anak (parent-child) dan secara otomatis menutup celah IDOR pada route bersarang tanpa memerlukan pengecekan manual tambahan di controller. |
| 4 | Daftarkan middleware di `app/Http/Kernel.php` seperti tutorial lama | Berkas `Kernel.php` tidak akan ditemukan di folder `app/Http/` |File app/Http/Kernel.php tidak ditemukan di dalam proyek (berkas tersebut tidak ada). Ketika mencoba mencari dan mendaftarkan middleware sesuai panduan tutorial lama di direktori app/Http/, direktori tersebut hanya memuat folder Controllers, Middleware, dan Requests. Hal ini terjadi karena sejak Laravel 11 hingga Laravel 12 yang digunakan saat ini, arsitektur Laravel telah disederhanakan (streamlined application structure) dan menghapus file Kernel.php secara permanen. Konfigurasi seluruh middleware aplikasi kini dipusatkan ke dalam file bootstrap/app.php menggunakan callback ->withMiddleware(). Hal ini membuktikan bahwa mengikuti tutorial lawas tanpa memahami versi Laravel akan menyebabkan kebingungan akibat file konfigurasi yang sudah usang dan berpindah tempat. |
| 5 | Pasang `role:admin` pada grup, lalu akses sebagai dosen | menampilkan respon error 403 (Forbidden) karena peran akun (dosen) tidak sesuai  |Akses ditolak dengan respon HTTP 403 (Forbidden: "Anda tidak memiliki akses ke halaman ini"). Ketika login menggunakan akun dengan peran dosen (role:dosen) lalu mencoba mengakses rute khusus administrator seperti /admin/users atau /admin/courses, request langsung dicegat oleh middleware EnsureUserHasRole yang terpasang pada grup rute admin (middleware('role:admin')). Middleware tersebut memeriksa apakah kolom role pada user yang sedang login cocok dengan parameter peran yang diizinkan. Karena peran akun adalah dosen sedangkan rute hanya mengizinkan admin, middleware mengeksekusi abort_unless(..., 403) sebelum request diteruskan ke controller. Hal ini membuktikan bahwa middleware berbasis peran bekerja efektif sebagai filter gerbang awal (coarse-grained authorization) untuk membatasi akses ke seluruh area atau fitur tertentu berdasarkan hak akses level peran pengguna. |
| 6 | Sebagai dosen A, edit mata kuliah milik dosen B (keduanya lolos `role:dosen`) | Dosen A tidak akan berhasil masuk ke halaman form edit dosen B |Akses ditolak dengan respon eror 403 (Forbidden) bukan oleh middleware, melainkan oleh validasi kepemilikan manual di controller Dosen\AssignmentController.php pada baris abort_unless($assignment->course->lecturer_id === auth()->id(), 403);. Ketika login sebagai Dosen A lalu mencoba mengakses rute edit tugas milik mata kuliah Dosen B (/dosen/assignments/{id}/edit), middleware role:dosen meloloskan request tersebut tanpa hambatan karena Dosen A memang memiliki peran sebagai dosen. Tanpa adanya pengecekan kepemilikan objek (lecturer_id) di dalam controller, Dosen A akan dapat dengan leluasa melihat, mengedit, bahkan menghapus tugas kuliah milik Dosen B. Hal ini membuktikan bahwa middleware peran saja sama sekali tidak cukup (coarse-grained hanya memfilter level halaman), sehingga diperlukan mekanisme otorisasi berbasis objek (fine-grained authorization) yang nantinya diorganisir menggunakan Policy pada materi Minggu 7. |

