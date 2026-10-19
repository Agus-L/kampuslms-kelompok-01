# Panduan Pengujian Keamanan IDOR Versi Peramban (Browser) — KampusLMS

Panduan ini berisi langkah-langkah pengujian **seluruh 23 titik rawan IDOR (*Insecure Direct Object Reference*)** murni menggunakan **peramban web (Web Browser)** seperti Google Chrome, Microsoft Edge, atau Mozilla Firefox.

Metode ini **tidak memerlukan terminal cURL maupun Bearer Token**. Pengujian dilakukan langsung dengan memanipulasi parameter ID pada *Address Bar* peramban. Dokumen ini disusun secara **konseptual dan dinamis**, sehingga pengujian tetap akurat meskipun database baru saja di-reset menggunakan `php artisan migrate:fresh --seed`.

---

## 1. Konsep Utama IDOR & Cara Menentukan ID Target Secara Dinamis

IDOR terjadi ketika pengguna dapat mengakses atau memodifikasi data milik pengguna lain hanya dengan **mengganti angka ID di URL**, karena server tidak memverifikasi hak kepemilikan atas objek tersebut.

Karena urutan ID dapat berubah setiap kali seeder dijalankan (`migrate:fresh --seed`), **jangan terpaku pada angka statis (seperti ID 1 atau 2)**. Gunakan rumus pencarian ID target berikut:

### 📌 Cara Menentukan ID Target Dosen (Uji No. 1–11):
1. Login sebagai akun dosen yang sedang dipakai untuk menguji (misal: `dosen2@kampuslms.test` / password: `password`).
2. Buka menu daftar mata kuliah di: `http://127.0.0.1:8000/dosen/courses`.
3. Catat ID mata kuliah yang muncul di tabel (misal yang muncul adalah ID `3` dan `4`).
4. **ID Target IDOR:** Gunakan ID mata kuliah yang **TIDAK ADA** di daftar tersebut (misal ID `1` atau `2`). Semua ID materi, tugas, dan submission yang berada di bawah ID `1` atau `2` adalah target uji IDOR yang valid.

### 📌 Cara Menentukan ID Target Mahasiswa (Uji No. 12–20):
1. Login sebagai akun mahasiswa penguji (misal: `mahasiswa2@kampuslms.test` / password: `password`).
2. Buka menu daftar mata kuliah di: `http://127.0.0.1:8000/mahasiswa/courses`.
3. Catat mata kuliah yang terdaftar di KRS mahasiswa tersebut.
4. **ID Target IDOR:** Gunakan ID mata kuliah yang **TIDAK DIIKUTI** oleh mahasiswa tersebut, atau gunakan ID submission tugas milik mahasiswa lain.

---

## 2. Tabel Ringkasan 23 Titik IDOR (Konteks Celah & Pola URL)

> **Cara Pengujian:** Salin pola URL, ganti `{id}` dengan ID resource yang bukan hak akun Anda, tempel ke Address Bar browser, lalu tekan **Enter**.

| No | Konteks Celah IDOR yang Mau Dijebol | Peran Akun | Pola URL di Address Bar (Browser) | Contoh URL | Hasil Diharapkan |
|:--:|---|:---:|---|---|:---:|
| **1** | Dosen membuka detail MK yang **bukan diampunya** | Dosen | `/dosen/courses/{id_mk_orang_lain}` | `/dosen/courses/1` | ⛔ **403 Akses Ditolak** |
| **2** | Dosen mengintip daftar materi pada MK dosen lain | Dosen | `/dosen/courses/{id_mk_orang_lain}/materials` | `/dosen/courses/1/materials` | ⛔ **403 Akses Ditolak** |
| **3** | Dosen menyisipkan materi baru ke dalam MK dosen lain | Dosen | `/dosen/courses/{id_mk_orang_lain}/materials/create` | `/dosen/courses/1/materials/create` | ⛔ **403 Akses Ditolak** |
| **4** | Dosen membaca materi milik dosen lain via shallow route | Dosen | `/dosen/materials/{id_materi_orang_lain}` | `/dosen/materials/1` | ⛔ **403 Akses Ditolak** |
| **4b** | Dosen membuka form edit materi milik dosen lain | Dosen | `/dosen/materials/{id_materi_orang_lain}/edit` | `/dosen/materials/1/edit` | ⛔ **403 Akses Ditolak** |
| **5** | Dosen mengintip daftar tugas pada MK dosen lain | Dosen | `/dosen/courses/{id_mk_orang_lain}/assignments` | `/dosen/courses/1/assignments` | ⛔ **403 Akses Ditolak** |
| **6** | Dosen membuat tugas baru di dalam MK dosen lain | Dosen | `/dosen/courses/{id_mk_orang_lain}/assignments/create` | `/dosen/courses/1/assignments/create` | ⛔ **403 Akses Ditolak** |
| **7** | Dosen membuka form edit tugas milik dosen lain | Dosen | `/dosen/assignments/{id_tugas_orang_lain}/edit` | `/dosen/assignments/1/edit` | ⛔ **403 Akses Ditolak** |
| **8** | Dosen mengintip daftar submission mahasiswa di kelas dosen lain | Dosen | `/dosen/assignments/{id_tugas_orang_lain}/submissions` | `/dosen/assignments/1/submissions` | ⛔ **403 Akses Ditolak** |
| **9** | Dosen membaca detail submission mahasiswa dari kelas dosen lain | Dosen | `/dosen/submissions/{id_submission_bukan_kelasnya}` | `/dosen/submissions/1` | ⛔ **403 Akses Ditolak** |
| **10** | Dosen memberi nilai submission mahasiswa di kelas dosen lain | Dosen | *(Akses lembar submission di No. 9 langsung diblokir)* | `/dosen/submissions/1` | ⛔ **403 Akses Ditolak** |
| **11** | Dosen memanipulasi nilai (*grade*) yang dibuat dosen lain | Dosen | *(Dilindungi 4-level Policy di backend)* | `/dosen/grades/1` | ⛔ **403 Akses Ditolak** |
| **12** | Mahasiswa membuka detail MK yang **tidak diambil di KRS** | Mahasiswa | `/mahasiswa/courses/{id_mk_yang_tidak_diambil}` | `/mahasiswa/courses/1` | ⛔ **403 Akses Ditolak** |
| **13** | Mahasiswa mengintip materi kuliah dari MK yang tidak diambil | Mahasiswa | `/mahasiswa/courses/{id_mk_yang_tidak_diambil}/materials` | `/mahasiswa/courses/1/materials` | ⛔ **403 Akses Ditolak** |
| **14** | Mahasiswa membaca materi dari MK luar via shallow route | Mahasiswa | `/mahasiswa/materials/{id_materi_dari_mk_luar}` | `/mahasiswa/materials/1` | ⛔ **403 Akses Ditolak** |
| **15** | Mahasiswa mengintip daftar tugas dari MK yang tidak diambil | Mahasiswa | `/mahasiswa/courses/{id_mk_yang_tidak_diambil}/assignments` | `/mahasiswa/courses/1/assignments` | ⛔ **403 Akses Ditolak** |
| **16** | Mahasiswa membaca detail tugas dari MK luar via shallow route | Mahasiswa | `/mahasiswa/assignments/{id_tugas_dari_mk_luar}` | `/mahasiswa/assignments/1` | ⛔ **403 Akses Ditolak** |
| **17** | Mahasiswa mengintip seluruh submission teman sekelas | Mahasiswa | `/mahasiswa/assignments/{id_tugas}/submissions` | `/mahasiswa/assignments/1/submissions` | ⛔ **403** / 🛡️ **Data Sendiri Saja** |
| **18** | Mahasiswa membaca lembar jawaban submission milik teman | Mahasiswa | `/mahasiswa/submissions/{id_submission_milik_teman}` | `/mahasiswa/submissions/1` | ⛔ **403 Akses Ditolak** |
| **19** | Mahasiswa membuka form edit lembar jawaban milik teman | Mahasiswa | `/mahasiswa/submissions/{id_submission_milik_teman}/edit` | `/mahasiswa/submissions/1/edit` | ⛔ **403 Akses Ditolak** |
| **20** | Mahasiswa mengintip nilai (*grade*) ujian milik teman | Mahasiswa | `/mahasiswa/submissions/{id_submission_milik_teman}/grades` | `/mahasiswa/submissions/1/grades` | ⛔ **403 Akses Ditolak** |
| **21** | Pengguna menandai baca notifikasi milik pengguna lain | Siapapun | `/notifications` | `/notifications` | 🛡️ **Hanya Notif Akun Sendiri** |
| **22** | Dosen melihat katalog MK (Bocorkah MK seluruh kampus?) | Dosen | `/dosen/courses` | `/dosen/courses` | 🛡️ **Hanya MK yang Diajar Sendiri** |
| **23** | Mahasiswa melihat katalog MK (Bocorkah MK yang tidak diambil?) | Mahasiswa | `/mahasiswa/courses` | `/mahasiswa/courses` | 🛡️ **Hanya MK yang Diambil di KRS** |

---

## 3. Langkah Rinci & Konteks IDOR Titik 1–11: Jalur Dosen

> **Persiapan Akun:** Login di browser dengan akun dosen mana saja (misal: `dosen2@kampuslms.test` / pass: `password`).  
> **Target:** Objek mata kuliah/materi/tugas yang pengampunya adalah dosen lain.

---

### Titik 1 — Detail Mata Kuliah Milik Dosen Lain
* **Konteks IDOR yang Mau Dijebol:**  
  *Bypass Horizontal Access Control antar sesama dosen.* Dosen mencoba membuka halaman detail mata kuliah yang diampu oleh rekan dosen lain dengan cara mengganti `{id}` mata kuliah di URL.
* **Pola URL:** `http://127.0.0.1:8000/dosen/courses/{id_mk_yang_bukan_diampu}`  
  *(Contoh: jika dosen login hanya mengajar MK 3 & 4, uji dengan memasukkan `/dosen/courses/1`)*
* **Hasil di Layar Browser:** Muncul halaman kustom **403 Akses Ditolak**.
* **Mekanisme Pengaman:** `Dosen\CourseController@show` memanggil `Gate::authorize('view', $course)`. `CoursePolicy::view()` memverifikasi `$course->lecturer_id === $user->id`. Karena ID dosen login tidak sama dengan ID pengampu MK, request langsung ditolak.

---

### Titik 2 — Daftar Materi Kuliah Milik Dosen Lain
* **Konteks IDOR yang Mau Dijebol:**  
  *Unauthorized Reading of Course Materials.* Dosen mencoba mengintip atau menyalin seluruh silabus dan dokumen materi kuliah yang disusun oleh dosen lain pada mata kuliah yang tidak diampunya.
* **Pola URL:** `http://127.0.0.1:8000/dosen/courses/{id_mk_yang_bukan_diampu}/materials`
* **Hasil di Layar Browser:** Muncul halaman kustom **403 Akses Ditolak**.
* **Mekanisme Pengaman:** `Dosen\MaterialController@index` memanggil `Gate::authorize('view', $course)`. Dosen yang bukan pengampu ditolak sebelum query database materi dijalankan.

---

### Titik 3 — Sisipkan Materi Baru ke MK Milik Dosen Lain
* **Konteks IDOR yang Mau Dijebol:**  
  *Unauthorized Data Creation / Course Tampering.* Dosen mencoba membuka formulir dan mengunggah materi baru ke dalam mata kuliah milik dosen lain tanpa izin pengampu resmi.
* **Pola URL:** `http://127.0.0.1:8000/dosen/courses/{id_mk_yang_bukan_diampu}/materials/create`
* **Hasil di Layar Browser:** Muncul halaman kustom **403 Akses Ditolak** (formulir tambah materi sama sekali tidak ditampilkan).
* **Mekanisme Pengaman:** `Dosen\MaterialController@create` memanggil `Gate::authorize('create', [Material::class, $course])` yang mewajibkan penambahan materi hanya boleh dilakukan oleh pengampu resmi mata kuliah tersebut.

---

### Titik 4 — Akses Materi via Shallow Route (Tanpa ID Course)
* **Konteks IDOR yang Mau Dijebol:**  
  *Bypass Route Hierarchy via Direct ID Guessing.* Pada URL dangkal (*shallow route*), URL materi tidak lagi diawali dengan `course_id` (hanya `/materials/{material}`). Dosen mencoba menebak ID materi milik dosen lain secara langsung di URL untuk melewati pengecekan kelas induk.
* **Pola URL:** `http://127.0.0.1:8000/dosen/materials/{id_materi_milik_dosen_lain}`
* **Hasil di Layar Browser:** Muncul halaman kustom **403 Akses Ditolak**.
* **Mekanisme Pengaman:** `MaterialPolicy::view()` menelusuri rantai relasi induk: `$material->course->lecturer_id === $user->id`. Meskipun `course_id` tidak ada di URL, Policy tetap memuat relasi course dari database untuk memverifikasi dosen pengampu aslinya.

---

### Titik 4b — Edit / Manipulasi Materi Milik Dosen Lain
* **Konteks IDOR yang Mau Dijebol:**  
  *Unauthorized Modification of Learning Resources.* Dosen mencoba membuka halaman edit untuk mengubah judul, deskripsi, atau tautan materi pembelajaran milik dosen lain.
* **Pola URL:** `http://127.0.0.1:8000/dosen/materials/{id_materi_milik_dosen_lain}/edit`
* **Hasil di Layar Browser:** Muncul halaman kustom **403 Akses Ditolak**.
* **Mekanisme Pengaman:** `Dosen\MaterialController@edit` memanggil `Gate::authorize('update', $material)`. Otorisasi menolak dosen yang bukan pemilik course materi tersebut.

---

### Titik 5 — Daftar Tugas di MK Milik Dosen Lain
* **Konteks IDOR yang Mau Dijebol:**  
  *Unauthorized Inspection of Assignments.* Dosen mencoba melihat daftar soal ujian/tugas evaluasi yang dibuat dosen lain untuk kelasnya.
* **Pola URL:** `http://127.0.0.1:8000/dosen/courses/{id_mk_yang_bukan_diampu}/assignments`
* **Hasil di Layar Browser:** Muncul halaman kustom **403 Akses Ditolak**.
* **Mekanisme Pengaman:** `Dosen\AssignmentController@index` memanggil `Gate::authorize('view', $course)`. Akses ditolak sebelum query penugasan dieksekusi.

---

### Titik 6 — Buat Tugas Baru di MK Milik Dosen Lain
* **Konteks IDOR yang Mau Dijebol:**  
  *Unauthorized Task Creation.* Dosen mencoba membuka form pembuatan tugas baru di mata kuliah dosen lain, berpotensi mempublikasikan penugasan palsu kepada mahasiswa yang bukan kelas bimbingannya.
* **Pola URL:** `http://127.0.0.1:8000/dosen/courses/{id_mk_yang_bukan_diampu}/assignments/create`
* **Hasil di Layar Browser:** Muncul halaman kustom **403 Akses Ditolak**.
* **Mekanisme Pengaman:** `Dosen\AssignmentController@create` memanggil `Gate::authorize('create', [Assignment::class, $course])` yang memastikan hanya pengampu terdaftar yang boleh menerbitkan tugas.

---

### Titik 7 — Edit / Manipulasi Tugas Milik Dosen Lain
* **Konteks IDOR yang Mau Dijebol:**  
  *Unauthorized Assignment Modification.* Dosen mencoba membuka formulir pengeditan untuk mengubah petunjuk tugas, tenggat waktu (*deadline*), atau bobot nilai pada tugas milik dosen lain.
* **Pola URL:** `http://127.0.0.1:8000/dosen/assignments/{id_tugas_milik_dosen_lain}/edit`
* **Hasil di Layar Browser:** Muncul halaman kustom **403 Akses Ditolak**.
* **Mekanisme Pengaman:** `Dosen\AssignmentController@edit` memanggil `Gate::authorize('update', $assignment)`. Policy memverifikasi `$assignment->course->lecturer_id === $user->id`.

---

### Titik 8 — Intip Submissions Mahasiswa di Tugas Dosen Lain
* **Konteks IDOR yang Mau Dijebol:**  
  *Privacy Violation / Grading Data Leak.* Dosen mencoba melihat rekap seluruh berkas jawaban tugas yang dikumpulkan mahasiswa pada kelas yang diampu dosen lain.
* **Pola URL:** `http://127.0.0.1:8000/dosen/assignments/{id_tugas_milik_dosen_lain}/submissions`
* **Hasil di Layar Browser:** Muncul halaman kustom **403 Akses Ditolak**.
* **Mekanisme Pengaman:** `Dosen\SubmissionController@index` memanggil `Gate::authorize('view', $assignment)`. Hanya pengampu tugas tersebut yang berhak memeriksa berkas pengumpulan mahasiswa.

---

### Titik 9 — Intip Detail Lembar Submission Kelas Dosen Lain
* **Konteks IDOR yang Mau Dijebol:**  
  *Direct Access to Student File via Shallow Route.* Dosen menebak `{id_submission}` lembar kerja mahasiswa dari kelas lain dan membukanya langsung via URL tanpa melewati halaman daftar tugas.
* **Pola URL:** `http://127.0.0.1:8000/dosen/submissions/{id_submission_bukan_kelasnya}`
* **Hasil di Layar Browser:** Muncul halaman kustom **403 Akses Ditolak**.
* **Mekanisme Pengaman:** `SubmissionPolicy::view()` menelusuri rantai 3 tingkat relasi:  
  `$submission->assignment->course->lecturer_id === $user->id`.

---

### Titik 10 & 11 — Beri / Ubah Nilai pada Submission Bukan Kelasnya
* **Konteks IDOR yang Mau Dijebol:**  
  *Unauthorized Grading & Grade Tampering.* Dosen mencoba menyusupkan nilai palsu atau memanipulasi riwayat nilai (*grade*) pada submission tugas mahasiswa di kelas yang tidak diajarnya.
* **Cara Uji di Browser:**  
  Buka langsung URL lembar submission di browser:  
  `http://127.0.0.1:8000/dosen/submissions/{id_submission_bukan_kelasnya}`
* **Hasil di Layar Browser:**  
  Halaman submission tersebut **langsung ditolak dengan 403 Forbidden**. Dosen penyusup sama sekali tidak bisa melihat tombol maupun formulir penilaian.
* **Mekanisme Pengaman:** Method penyimpanan dan pembaruan nilai dilindungi oleh `GradePolicy::create` dan `GradePolicy::update` yang menelusuri rantai relasi 4 tingkat secara *null-safe* hingga ke pengampu kelas induk.

---

## 4. Langkah Rinci & Konteks IDOR Titik 12–20: Jalur Mahasiswa

> **Persiapan Akun:** Login di browser dengan akun mahasiswa (misal: `mahasiswa2@kampuslms.test` / pass: `password`).  
> **Target:** Mata kuliah yang tidak diambil di KRS, atau lembar submission/nilai milik mahasiswa lain.

---

### Titik 12 — Detail Mata Kuliah yang Tidak Diambil di KRS
* **Konteks IDOR yang Mau Dijebol:**  
  *Unauthorized Course Auditing.* Mahasiswa mencoba membuka detail mata kuliah internal kampus yang tidak pernah didaftarkannya di KRS dengan cara mengetik nomor ID mata kuliah tersebut di URL.
* **Pola URL:** `http://127.0.0.1:8000/mahasiswa/courses/{id_mk_yang_tidak_diambil}`
* **Hasil di Layar Browser:** Muncul halaman kustom **403 Akses Ditolak**.
* **Mekanisme Pengaman:** `Mahasiswa\CourseController@show` memanggil `CoursePolicy::view()`. Policy memeriksa tabel pivot enrollment:
  ```php
  $course->students()->where('user_id', $user->id)->exists()
  ```
  Jika mahasiswa tidak terdaftar di kelas tersebut, akses langsung dibatalkan (*aborted*).

---

### Titik 13 — Materi Kuliah dari MK yang Tidak Diikuti
* **Konteks IDOR yang Mau Dijebol:**  
  *Piracy / Unauthorized Material Access.* Mahasiswa mencoba mengunduh atau membaca repositori materi perkuliahan dari kelas yang tidak diikutinya.
* **Pola URL:** `http://127.0.0.1:8000/mahasiswa/courses/{id_mk_yang_tidak_diambil}/materials`
* **Hasil di Layar Browser:** Muncul halaman kustom **403 Akses Ditolak**.
* **Mekanisme Pengaman:** `Mahasiswa\MaterialController@index` memanggil `Gate::authorize('view', $course)`. Enrollment diverifikasi sebelum daftar materi diambil dari database.

---

### Titik 14 — Akses Materi Kuliah Luar via Shallow Route
* **Konteks IDOR yang Mau Dijebol:**  
  *Direct Material ID Guessing.* Mahasiswa menebak nomor ID materi perkuliahan milik mata kuliah luar dan membukanya langsung via URL tanpa menyertakan `course_id`.
* **Pola URL:** `http://127.0.0.1:8000/mahasiswa/materials/{id_materi_dari_mk_luar}`
* **Hasil di Layar Browser:** Muncul halaman kustom **403 Akses Ditolak**.
* **Mekanisme Pengaman:** `MaterialPolicy::view()` menelusuri relasi ke induk mata kuliah dan memeriksa enrollment mahasiswa pada mata kuliah pemilik materi tersebut.

---

### Titik 15 — Daftar Tugas dari MK yang Tidak Diikuti
* **Konteks IDOR yang Mau Dijebol:**  
  *Unauthorized Assessment Viewing.* Mahasiswa mencoba melihat daftar penugasan evaluasi pada mata kuliah yang tidak diikutinya.
* **Pola URL:** `http://127.0.0.1:8000/mahasiswa/courses/{id_mk_yang_tidak_diambil}/assignments`
* **Hasil di Layar Browser:** Muncul halaman kustom **403 Akses Ditolak**.
* **Mekanisme Pengaman:** `Mahasiswa\AssignmentController@index` memanggil `Gate::authorize('view', $course)`. Ditolak karena mahasiswa tidak terdaftar di kelas tersebut.

---

### Titik 16 — Detail Tugas Luar via Shallow Route
* **Konteks IDOR yang Mau Dijebol:**  
  *Direct Assignment ID Guessing.* Mahasiswa menebak nomor ID tugas dari mata kuliah luar dan membukanya langsung via shallow route.
* **Pola URL:** `http://127.0.0.1:8000/mahasiswa/assignments/{id_tugas_dari_mk_luar}`
* **Hasil di Layar Browser:** Muncul halaman kustom **403 Akses Ditolak**.
* **Mekanisme Pengaman:** `AssignmentPolicy::view()` memverifikasi keikutsertaan mahasiswa di kelas induk tugas tersebut.

---

### Titik 17 — Intip Submission Teman Sekelas (Horizontal Leakage)
* **Konteks IDOR yang Mau Dijebol:**  
  *Student-to-Student Submission Snooping / Academic Dishonesty.* Mahasiswa mencoba membuka daftar submission pada sebuah tugas, berharap sistem memperlihatkan jawaban teman-teman sekelasnya untuk dicontek.
* **Pola URL:** `http://127.0.0.1:8000/mahasiswa/assignments/{id_tugas}/submissions`
* **Hasil di Layar Browser:**  
  * Jika mahasiswa **tidak terdaftar di kelas:** Tampil **403 Akses Ditolak**.
  * Jika mahasiswa **terdaftar di kelas:** Tampil data JSON yang **HANYA berisi lembar kerja miliknya sendiri**. Berkas jawaban milik mahasiswa lain sama sekali tidak muncul!
* **Mekanisme Pengaman:** Penerapan **Query Scoping** mutlak di controller:
  ```php
  $submissions = $assignment->submissions()->where('user_id', auth()->id())->get();
  ```
  Data mahasiswa lain tidak pernah diambil dari database (*zero leakage*).

---

### Titik 18 — Baca Lembar Jawaban Submission Milik Teman
* **Konteks IDOR yang Mau Dijebol:**  
  *Direct Submission Hijacking / Answer Exfiltration.* Mahasiswa menebak `{id_submission}` milik mahasiswa lain dan mencoba membaca lembar jawabannya langsung via URL.
* **Pola URL:** `http://127.0.0.1:8000/mahasiswa/submissions/{id_submission_milik_teman}`
* **Hasil di Layar Browser:** Muncul halaman kustom **403 Akses Ditolak**.
* **Mekanisme Pengaman:** `SubmissionPolicy::view()` menegakkan aturan **kepemilikan mutlak**:
  ```php
  $submission->user_id === $user->id
  ```
  Jika ID pemilik submission bukan ID mahasiswa yang sedang login, akses langsung ditolak.

---

### Titik 19 — Form Edit / Bajak Lembar Jawaban Milik Teman
* **Konteks IDOR yang Mau Dijebol:**  
  *Malicious Answer Overwriting.* Mahasiswa mencoba membuka formulir edit untuk menimpa berkas, catatan, atau jawaban tugas milik mahasiswa lain sebelum deadline berakhir.
* **Pola URL:** `http://127.0.0.1:8000/mahasiswa/submissions/{id_submission_milik_teman}/edit`
* **Hasil di Layar Browser:** Muncul halaman kustom **403 Akses Ditolak**.
* **Mekanisme Pengaman:** `SubmissionPolicy::update()` menolak pengubahan jika bukan pemilik asli data submission.

---

### Titik 20 — Intip Nilai (Grade) dan Umpan Balik Milik Teman
* **Konteks IDOR yang Mau Dijebol:**  
  *Student Grade Confidentiality Breach.* Mahasiswa mencoba melihat nilai ujian, skor akhir, dan umpan balik (*feedback*) dosen milik mahasiswa lain.
* **Pola URL:** `http://127.0.0.1:8000/mahasiswa/submissions/{id_submission_milik_teman}/grades`
* **Hasil di Layar Browser:** Muncul halaman kustom **403 Akses Ditolak**.
* **Mekanisme Pengaman:** `Mahasiswa\GradeController@index` memanggil `Gate::authorize('view', $submission)`. Otorisasi gagal di level submission sebelum data nilai sempat diakses.

---

## 5. Titik 21: Keamanan Notifikasi Pribadi

* **Konteks IDOR yang Mau Dijebol:**  
  *User Notification Snooping & Unauthorized Tampering.* Pengguna mencoba membaca atau menandai sudah dibaca notifikasi milik pengguna lain dengan menebak UUID notifikasi.
* **Pola URL di Browser:**  
  `http://127.0.0.1:8000/notifications`
* **Hasil di Layar Browser:**  
  Halaman notifikasi terbuka normal, tetapi **hanya menampilkan notifikasi yang ditujukan kepada akun yang sedang login**.
* **Mekanisme Pengaman:** Controller menggunakan relasi scoped `$request->user()->notifications()`, dan aksi pembaruan status dibentengi dengan:
  ```php
  abort_unless($notification->notifiable_id === $request->user()->id, 403);
  ```

---

## 6. Titik 22 & 23: Pencegahan Kebocoran Data Massal (Query Scoping)

Celah ini menguji apakah halaman indeks katalog utama membocorkan data global sistem (*Mass Data Exposure*).

---

### Titik 22 — Katalog Mata Kuliah Dosen (Dosen Lihat Semua MK Kampus?)
* **Konteks Celah yang Diuji:**  
  *Dosen Index Information Leakage.* Dosen yang membuka menu mata kuliah tidak boleh disajikan seluruh mata kuliah di database kampus, melainkan hanya kelas yang menjadi tanggung jawab mengajarnya.
* **URL:** `http://127.0.0.1:8000/dosen/courses`
* **Hasil di Layar Browser:**  
  Tabel **hanya menampilkan mata kuliah yang diampu oleh dosen yang login**. Mata kuliah yang diajar oleh rekan dosen lain tidak muncul di tabel.
* **Mekanisme Pengaman:** Controller menerapkan Query Scoping pada SQL query:
  ```php
  $courses = $request->user()->taughtCourses()->paginate(15);
  ```

---

### Titik 23 — Katalog Mata Kuliah Mahasiswa (Mahasiswa Lihat Seluruh Katalog?)
* **Konteks Celah yang Diuji:**  
  *Student Enrollment List Leakage.* Mahasiswa yang membuka menu kelas hanya boleh disajikan mata kuliah yang telah disetujui di KRS-nya, bukan seluruh mata kuliah aktif di kampus.
* **URL:** `http://127.0.0.1:8000/mahasiswa/courses`
* **Hasil di Layar Browser:**  
  Tabel **hanya menampilkan mata kuliah yang diikuti mahasiswa yang login**. Mata kuliah lain di kampus tidak muncul di tabel.
* **Mekanisme Pengaman:** Controller menerapkan Query Scoping via relasi pivot:
  ```php
  $courses = $request->user()->courses()->paginate(15);
  ```

---

## 7. Rangkuman Pertanyaan & Jawaban untuk Responsi

* **T: Mengapa pengujian ini tidak terpaku pada ID angka 1 atau 2?**  
  * **J:** Karena angka ID adalah data dinamis yang di-generate oleh database auto-increment. Inti dari kerentanan IDOR adalah *ketidakmampuan server memvalidasi kepemilikan objek*. Selama kita menguji dengan memasukkan ID objek yang bukan hak akun kita, jika server mengembalikan status **403 Forbidden**, maka aplikasi terbukti aman dari serangan IDOR.
* **T: Kenapa tidak cukup hanya menyembunyikan tombol Edit / Hapus di tampilan Blade?**  
  * **J:** Direktif `@can` di Blade hanyalah kosmetik visual. Penyerang dapat mengetik URL langsung di Address Bar browser atau menembak endpoint HTTP. Oleh karena itu, pengamanan utama wajib berada di sisi backend controller melalui `Gate::authorize()` dan Policy.
