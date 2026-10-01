# Minggu 5

**Mata Kuliah:** Pemograman Web A <br>
**Nama:** Annisa Dwi Lestari Sonny <br>
**Tanggal:** 29/09/2026

---

## Read

1. Jalankan `php artisan route:list --except-vendor`. Salin keluarannya ke catatan.

**Jawaban:** 
```text
 GET|HEAD        / ................................................................................................................... home › routes/web.php:60
  GET|HEAD        admin/courses ................................................................................... admin.courses.index › CourseController@index
  POST            admin/courses ................................................................................... admin.courses.store › CourseController@store
  GET|HEAD        admin/courses/create .......................................................................... admin.courses.create › CourseController@create
  GET|HEAD        admin/courses/{course} ............................................................................ admin.courses.show › CourseController@show
  PUT|PATCH       admin/courses/{course} ........................................................................ admin.courses.update › CourseController@update
  DELETE          admin/courses/{course} ...................................................................... admin.courses.destroy › CourseController@destroy
  GET|HEAD        admin/courses/{course}/edit ....................................................................... admin.courses.edit › CourseController@edit
  GET|HEAD        admin/courses/{course}/enrollments ........................................ admin.courses.enrollments.index › Admin\EnrollmentController@index
  POST            admin/courses/{course}/enrollments ........................................ admin.courses.enrollments.store › Admin\EnrollmentController@store
  GET|HEAD        admin/courses/{course}/enrollments/create ............................... admin.courses.enrollments.create › Admin\EnrollmentController@create
  DELETE          admin/enrollments/{enrollment} ................................................ admin.enrollments.destroy › Admin\EnrollmentController@destroy  
  GET|HEAD        admin/users ......................................................................................... admin.users.index › UserController@index  
  POST            admin/users ......................................................................................... admin.users.store › UserController@store  
  GET|HEAD        admin/users/create ................................................................................ admin.users.create › UserController@create  
  PUT|PATCH       admin/users/{user} ................................................................................ admin.users.update › UserController@update  
  DELETE          admin/users/{user} .............................................................................. admin.users.destroy › UserController@destroy  
  GET|HEAD        admin/users/{user}/edit ............................................................................... admin.users.edit › UserController@edit
  GET|HEAD        dashboard .............................................................................................. dashboard › DashboardController@index  
  GET|HEAD        dosen/assignments/{assignment} ...................................................... dosen.assignments.show › Dosen\AssignmentController@show  
  PUT|PATCH       dosen/assignments/{assignment} .................................................. dosen.assignments.update › Dosen\AssignmentController@update  
  DELETE          dosen/assignments/{assignment} ................................................ dosen.assignments.destroy › Dosen\AssignmentController@destroy  
  GET|HEAD        dosen/assignments/{assignment}/edit ................................................. dosen.assignments.edit › Dosen\AssignmentController@edit  
  GET|HEAD        dosen/assignments/{assignment}/submissions ............................ dosen.assignments.submissions.index › Dosen\SubmissionController@index  
  GET|HEAD        dosen/courses ............................................................................. dosen.courses.index › Dosen\CourseController@index  
  GET|HEAD        dosen/courses/{course} ...................................................................... dosen.courses.show › Dosen\CourseController@show
  GET|HEAD        dosen/courses/{course}/assignments ........................................ dosen.courses.assignments.index › Dosen\AssignmentController@index  
  POST            dosen/courses/{course}/assignments ........................................ dosen.courses.assignments.store › Dosen\AssignmentController@store  
  GET|HEAD        dosen/courses/{course}/assignments/create ............................... dosen.courses.assignments.create › Dosen\AssignmentController@create  
  GET|HEAD        dosen/courses/{course}/materials .............................................. dosen.courses.materials.index › Dosen\MaterialController@index  
  POST            dosen/courses/{course}/materials .............................................. dosen.courses.materials.store › Dosen\MaterialController@store  
  GET|HEAD        dosen/courses/{course}/materials/create ..................................... dosen.courses.materials.create › Dosen\MaterialController@create  
  PUT|PATCH       dosen/grades/{grade} ...................................................................... dosen.grades.update › Dosen\GradeController@update  
  GET|HEAD        dosen/materials/{material} .............................................................. dosen.materials.show › Dosen\MaterialController@show  
  PUT|PATCH       dosen/materials/{material} .......................................................... dosen.materials.update › Dosen\MaterialController@update
  DELETE          dosen/materials/{material} ........................................................ dosen.materials.destroy › Dosen\MaterialController@destroy  
  GET|HEAD        dosen/materials/{material}/edit ......................................................... dosen.materials.edit › Dosen\MaterialController@edit  
  GET|HEAD        dosen/submissions/{submission} ...................................................... dosen.submissions.show › Dosen\SubmissionController@show  
  POST            dosen/submissions/{submission}/grades ........................................... dosen.submissions.grades.store › Dosen\GradeController@store  
  GET|HEAD        login .............................................................................................................. login › routes/web.php:75  
  GET|HEAD        logout ............................................................................................................ logout › routes/web.php:94  
  GET|HEAD        mahasiswa/assignments/{assignment} .......................................... mahasiswa.assignments.show › Mahasiswa\AssignmentController@show
  GET|HEAD        mahasiswa/assignments/{assignment}/submissions ................ mahasiswa.assignments.submissions.index › Mahasiswa\SubmissionController@index  
  POST            mahasiswa/assignments/{assignment}/submissions ................ mahasiswa.assignments.submissions.store › Mahasiswa\SubmissionController@store  
  GET|HEAD        mahasiswa/assignments/{assignment}/submissions/create ....... mahasiswa.assignments.submissions.create › Mahasiswa\SubmissionController@create  
  GET|HEAD        mahasiswa/courses ................................................................. mahasiswa.courses.index › Mahasiswa\CourseController@index  
  GET|HEAD        mahasiswa/courses/{course} .......................................................... mahasiswa.courses.show › Mahasiswa\CourseController@show  
  GET|HEAD        mahasiswa/courses/{course}/assignments ............................ mahasiswa.courses.assignments.index › Mahasiswa\AssignmentController@index
  GET|HEAD        mahasiswa/courses/{course}/materials .................................. mahasiswa.courses.materials.index › Mahasiswa\MaterialController@index  
  GET|HEAD        mahasiswa/materials/{material} .................................................. mahasiswa.materials.show › Mahasiswa\MaterialController@show  
  GET|HEAD        mahasiswa/submissions/{submission} .......................................... mahasiswa.submissions.show › Mahasiswa\SubmissionController@show  
  PUT|PATCH       mahasiswa/submissions/{submission} ...................................... mahasiswa.submissions.update › Mahasiswa\SubmissionController@update  
  GET|HEAD        mahasiswa/submissions/{submission}/edit ..................................... mahasiswa.submissions.edit › Mahasiswa\SubmissionController@edit  
  GET|HEAD        mahasiswa/submissions/{submission}/grades ............................... mahasiswa.submissions.grades.index › Mahasiswa\GradeController@index  
  GET|HEAD        notifications ............................................................................. notifications.index › NotificationController@index
  PATCH           notifications/{notification} ............................................................ notifications.update › NotificationController@update  
  GET|HEAD        tentang .......................................................................................................... tentang › routes/web.php:64  
```

2. Tandai setiap route yang menerima parameter model (`{course}`, `{assignment}`, `dst`).

**Jawaban:** 
```text
1. Admin
- `GET|HEAD   admin/courses/{course}` (Parameter: `{course}`)
- `PUT|PATCH  admin/courses/{course}` (Parameter: `{course}`)
- `DELETE     admin/courses/{course}` (Parameter: `{course}`)
- `GET|HEAD   admin/courses/{course}/edit` (Parameter: `{course}`)
- `GET|HEAD   admin/courses/{course}/enrollments` (Parameter: `{course}`)
- `POST       admin/courses/{course}/enrollments` (Parameter: `{course}`)
- `GET|HEAD   admin/courses/{course}/enrollments/create` (Parameter: `{course}`)
- `DELETE     admin/enrollments/{enrollment}` (Parameter: `{enrollment}`)
- `PUT|PATCH  admin/users/{user}` (Parameter: `{user}`)
- `DELETE     admin/users/{user}` (Parameter: `{user}`)
- `GET|HEAD   admin/users/{user}/edit` (Parameter: `{user}`)

2. Dosen
- `GET|HEAD   dosen/courses/{course}` (Parameter: `{course}`)
- `GET|HEAD   dosen/courses/{course}/assignments` (Parameter: `{course}`)
- `POST       dosen/courses/{course}/assignments` (Parameter: `{course}`)
- `GET|HEAD   dosen/courses/{course}/assignments/create` (Parameter: `{course}`)
- `GET|HEAD   dosen/courses/{course}/materials` (Parameter: `{course}`)
- `POST       dosen/courses/{course}/materials` (Parameter: `{course}`)
- `GET|HEAD   dosen/courses/{course}/materials/create` (Parameter: `{course}`)
- `GET|HEAD   dosen/materials/{material}` (Parameter: `{material}`)
- `PUT|PATCH  dosen/materials/{material}` (Parameter: `{material}`)
- `DELETE     dosen/materials/{material}` (Parameter: `{material}`)
- `GET|HEAD   dosen/materials/{material}/edit` (Parameter: `{material}`)
- `GET|HEAD   dosen/assignments/{assignment}` (Parameter: `{assignment}`)
- `PUT|PATCH  dosen/assignments/{assignment}` (Parameter: `{assignment}`)
- `DELETE     dosen/assignments/{assignment}` (Parameter: `{assignment}`)
- `GET|HEAD   dosen/assignments/{assignment}/edit` (Parameter: `{assignment}`)
- `GET|HEAD   dosen/assignments/{assignment}/submissions` (Parameter: `{assignment}`)
- `GET|HEAD   dosen/submissions/{submission}` (Parameter: `{submission}`)
- `POST       dosen/submissions/{submission}/grades` (Parameter: `{submission}`)
- `PUT|PATCH  dosen/grades/{grade}` (Parameter: `{grade}`)

3. Mahasiswa
- `GET|HEAD   mahasiswa/courses/{course}` (Parameter: `{course}`)
- `GET|HEAD   mahasiswa/courses/{course}/assignments` (Parameter: `{course}`)
- `GET|HEAD   mahasiswa/courses/{course}/materials` (Parameter: `{course}`)
- `GET|HEAD   mahasiswa/materials/{material}` (Parameter: `{material}`)
- `GET|HEAD   mahasiswa/assignments/{assignment}` (Parameter: `{assignment}`)
- `GET|HEAD   mahasiswa/assignments/{assignment}/submissions` (Parameter: `{assignment}`)
- `POST       mahasiswa/assignments/{assignment}/submissions` (Parameter: `{assignment}`)
- `GET|HEAD   mahasiswa/assignments/{assignment}/submissions/create` (Parameter: `{assignment}`)
- `GET|HEAD   mahasiswa/submissions/{submission}` (Parameter: `{submission}`)
- `PUT|PATCH  mahasiswa/submissions/{submission}` (Parameter: `{submission}`)
- `GET|HEAD   mahasiswa/submissions/{submission}/edit` (Parameter: `{submission}`)
- `GET|HEAD   mahasiswa/submissions/{submission}/grades` (Parameter: `{submission}`)

4. Notifikasi
- `PATCH      notifications/{notification}` (Parameter: `{notification}`)

```

3. Untuk setiap route bertanda, jawab: siapa saja yang seharusnya boleh mengaksesnya, dan apa yang saat ini mencegah orang lain? Kemungkinan besar jawabannya "belum ada apa-apa" — itu wajar, dan itulah pekerjaan minggu ini dan minggu 7.

**Jawaban:** <br>
**Route Admin (`{course}`, `{user}`, `{enrollment}`):**
   * Boleh akses: Hanya Admin.
   * Pencegah saat ini: Middleware `role:admin`.

**Route Dosen (`{course}`, `{material}`, `{assignment}`, `{submission}`, `{grade}`):**
   * Boleh akses: Hanya Dosen pengampu mata kuliah tersebut.
   * Pencegah saat ini: Belum ada apa-apa (hanya dibatasi `role:dosen`, sesama dosen masih bisa saling akses via ID).

**Route Mahasiswa (`{course}`, `{material}`, `{assignment}`, `{submission}`):**
   * Boleh akses: Mahasiswa yang terdaftar di kelas tersebut, dan khusus `{submission}` hanya mahasiswa pemilik tugas.
   * Pencegah saat ini: Belum ada apa-apa (hanya dibatasi `role:mahasiswa`, sesama mahasiswa masih bisa saling intip/ubah data via ID).

**Route Notifikasi (`{notification}`):**
   * Boleh akses: User pemilik notifikasi.
   * Pencegah saat ini: Belum ada apa-apa (hanya dibatasi `auth`).


4. Buat tabel di `docs/minggu-05-<nama>.md` berjudul "Daftar Titik Rawan IDOR". Tabel ini akan Anda pakai lagi di minggu 7 dan saat interview.

**Jawaban:** 

### Daftar Titik Rawan IDOR

| No | Route & Method | Parameter | Aktor yang Berhak | Potensi Celah IDOR | Pencegahan Saat Ini |
| :---: | :--- | :---: | :--- | :--- | :--- |
| 1 | `PUT/PATCH/DELETE admin/users/{user}` | `{user}` | Admin | Admin bisa mengedit/menghapus akun admin lain jika multi-admin | Middleware `role:admin` |
| 2 | `GET/PUT/DELETE admin/courses/{course}` | `{course}` | Admin | Modifikasi mata kuliah di luar wewenang | Middleware `role:admin` |
| 3 | `DELETE admin/enrollments/{enrollment}` | `{enrollment}` | Admin | Menghapus data pendaftaran mahasiswa lain | Middleware `role:admin` |
| 4 | `GET dosen/courses/{course}` | `{course}` | Dosen pengampu mata kuliah | Dosen lain bisa melihat kursus yang bukan diampunya dengan mengganti ID | Belum ada (hanya `role:dosen`) |
| 5 | `GET/PUT/DELETE dosen/materials/{material}` | `{material}` | Dosen pengampu mata kuliah | Dosen lain bisa melihat, mengedit, atau menghapus materi dosen lain | Belum ada (hanya `role:dosen`) |
| 6 | `GET/PUT/DELETE dosen/assignments/{assignment}` | `{assignment}` | Dosen pengampu mata kuliah | Dosen lain bisa mengubah/menghapus tugas milik kelas dosen lain | Belum ada (hanya `role:dosen`) |
| 7 | `GET dosen/submissions/{submission}` | `{submission}` | Dosen pengampu mata kuliah | Dosen lain bisa melihat berkas tugas yang dikumpulkan mahasiswa kelas lain | Belum ada (hanya `role:dosen`) |
| 8 | `PUT/PATCH dosen/grades/{grade}` | `{grade}` | Dosen pengampu mata kuliah | Dosen lain bisa memanipulasi nilai mahasiswa kelas lain | Belum ada (hanya `role:dosen`) |
| 9 | `GET mahasiswa/courses/{course}` | `{course}` | Mahasiswa yang terdaftar (*enrolled*) | Mahasiswa di luar kelas bisa mengintip isi perkuliahan | Belum ada (hanya `role:mahasiswa`) |
| 10 | `GET mahasiswa/materials/{material}` | `{material}` | Mahasiswa yang terdaftar (*enrolled*) | Mahasiswa bisa mengakses materi kelas yang tidak diikutinya | Belum ada (hanya `role:mahasiswa`) |
| 11 | `GET mahasiswa/assignments/{assignment}` | `{assignment}` | Mahasiswa yang terdaftar (*enrolled*) | Mahasiswa bisa melihat soal tugas dari kelas lain | Belum ada (hanya `role:mahasiswa`) |
| 12 | `GET/PUT/PATCH mahasiswa/submissions/{submission}` | `{submission}` | **Mahasiswa pemilik tugas** | **Kritis:** Mahasiswa lain bisa melihat, mencontek, atau mengubah tugas milik temannya | Belum ada (hanya `role:mahasiswa`) |
| 13 | `PATCH notifications/{notification}` | `{notification}` | Pemilik notifikasi | User lain bisa menandai sudah dibaca notifikasi orang lain | Belum ada (hanya `auth`) |

---

### Break
| Yang Dirusak | Prediksi | Hasil Sebenarnya |
| :--- | :--- | :--- |
| Akses `/mahasiswa/submissions/{id}` milik mahasiswa lain saat login sebagai Mahasiswa A (Uji IDOR pada Submission) | Tanpa proteksi kepemilikan, data mahasiswa lain bisa dibaca via URL (HTTP 200). Jika diproteksi, akan diblokir 403. | Tanpa `abort_unless`, IDOR lolos (HTTP 200). Saat `abort_unless` aktif, berhasil diblokir 403 Forbidden. |
| Hapus `->scopeBindings()` pada route group Dosen, lalu akses assignment milik course lain (`/dosen/courses/1/assignments/4` di mana assignment 4 milik course 2) | Model di-resolve mandiri tanpa cek relasi, sehingga URL beda parent tetap terbuka (HTTP 200). | Berhasil dibuka (HTTP 200). Assignment Course 2 bisa diakses di URL Course 1 karena relasi tidak divalidasi. |
| Aktifkan kembali `->scopeBindings()`, lalu akses kembali child model yang bukan milik parent (`/dosen/courses/1/assignments/4`) | Laravel otomatis mengecek relasi parent-child. Karena data tidak cocok, akan menghasilkan 404. | Mengembalikan 404 Not Found. Laravel otomatis menolak request karena child bukan milik parent. |
| Coba cari dan daftarkan middleware kustom di `app/Http/Kernel.php` pada Laravel 12 | Berkas `Kernel.php` tidak ditemukan di `app/Http/` karena struktur baru Laravel 12. | Berkas `Kernel.php` memang ditiadakan di Laravel 12. Registrasi middleware dipusatkan di `bootstrap/app.php`. |
| Login sebagai Dosen, lalu coba akses route yang dilindungi `role:admin` (`/admin/courses`) | Middleware `role:admin` mendeteksi peran dosen, sehingga request diblokir 403. | Diblokir dengan 403 Forbidden ("Anda tidak memiliki akses ke halaman ini."). Filter peran bekerja. |
| Login sebagai Dosen A, lalu akses halaman detail course milik Dosen B (`/dosen/courses/{id_course_b}`) | Lolos dari middleware `role:dosen` karena sesama dosen. Tanpa cek kepemilikan, data Dosen B bisa diakses. | Lolos middleware `role:dosen`. Perlu cek `lecturer_id` (Policy) di controller agar terproteksi 403. |
---
