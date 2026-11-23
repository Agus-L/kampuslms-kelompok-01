# Dokumentasi Analisis Debugging dan Uji Regresi (Minggu 14)

## Identitas Mahasiswa

Nama: Anastasya Salsabila Khoirunnisa  
NIM: 10241009  
Peran: Pengembang 5 (Dokumentasi Bug & Pengawal CI)  

---

## Pendahuluan

Sesuai dengan panduan Modul Minggu 14 Subbab 14.1, proses debugging pada perangkat lunak bukanlah bakat bawaan atau tebak-tebakan acak, melainkan sebuah metode penyelidikan yang terstruktur dan terukur. Seluruh penanganan masalah dalam dokumen ini mengikuti 5 tahapan debugging sistematis:

1. **Reproduksi:** Mencatat langkah-langkah presisi untuk memicu bug secara konsisten.
2. **Persempit:** Mengisolasi batas wilayah kerja sistem antara bagian yang masih benar dan bagian yang mulai salah.
3. **Rumuskan Hipotesis Tunggal:** Menyusun satu dugaan teknis utama ("Terjadi masalah X karena faktor Y").
4. **Uji Satu Hipotesis:** Melakukan pemeriksaan terkecil dan termurah untuk mengonfirmasi atau menggugurkan hipotesis.
5. **Perbaiki dan Tulis Automated Test:** Menerapkan solusi definitif dan menguncinya dengan feature test agar bug serupa tidak dapat muncul kembali di masa depan (*regression prevention*).

Berikut adalah dokumentasi mendalam atas 3 kasus bug nyata yang dihadapi kelompok sepanjang semester ini, lengkap dengan analisis diagnostik, solusi perbaikan, dan automated regression test pencegahnya.

---

## Kasus Bug 1: Kebocoran Data Sensitif Email Mahasiswa pada Respons Transkrip Nilai (Data Exposure)

### 1. Gejala Permasalahan
Pada saat dosen pengampu membuka laporan transkrip nilai mahasiswa untuk mata kuliah yang diampunya (baik melalui permintaan browser maupun panggilan endpoint JSON), data yang dikembalikan server menyertakan seluruh atribut kolom model `User`, termasuk kolom `email`, `email_verified_at`, dan metadata akun mahasiswa.

Hal ini menimbulkan risiko keamanan serius terhadap privasi data mahasiswa (pelanggaran prinsip *Data Minimization* dan regulasi pelindungan data pribadi), karena dosen dan pengguna yang memiliki hak inspeksi jaringan dapat memanen alamat email seluruh mahasiswa tanpa ada kebutuhan fungsional akademik.

### 2. Langkah Reproduksi
1. Login ke aplikasi sebagai Dosen Pengampu kelas (`actingAs($dosen)`).
2. Akses endpoint laporan transkrip nilai mahasiswa melalui permintaan HTTP:
   `GET /dosen/courses/{course}/students/{student}/transcript` atau `GET /dosen/transcript`.
3. Lakukan inspeksi pada muatan respons JSON (*payload response*).
4. Amati bahwa field `"email": "mahasiswa@kampuslms.test"` ikut terkirim secara terbuka.

### 3. Cara Menemukan Penyebab (Metode Debugging Sistematis)
* **Persempit Wilayah:** Pemeriksaan difokuskan pada controller transkrip dosen (`app/Http/Controllers/Dosen/TranscriptController.php`).
* **Investigasi Kueri:** Melalui pemantauan kueri SQL dengan `DB::listen()`, ditemukan bahwa controller mengeksekusi pemanggilan Eloquent:
  ```php
  $students = $course->students()->get();
  ```
  Kueri ini mengeksekusi `SELECT users.*`, sehingga seluruh kolom database di tabel `users` otomatis dimuat ke dalam objek model dan diserialisasi menjadi JSON.
* **Hipotesis Tunggal:** Kueri Eloquent pada controller transkrip tidak menerapkan pembatasan kolom (*column projection*), sehingga atribut privat ikut terserialisasi ke dalam antarmuka web dan respons API.

### 4. Perbaikan yang Diterapkan
Pada method controller terkait, pengambilan data relasi mahasiswa diperketat hanya untuk kolom publik yang diperlukan dalam transkrip nilai (`id`, `name`, dan `nim_nip`), serta secara eksplisit meniadakan kolom `email`:
```php
$course->students()->select(['users.id', 'users.name', 'users.nim_nip'])->get();
```

### 5. Automated Test Pencegah Regresi
Pengujian dikunci pada berkas `tests/Feature/BugRegressionTest.php` melalui method:
`test_bug_1_transcript_api_response_does_not_leak_student_email()`

Test ini mengirimkan request ke endpoint transkrip dan memvalidasi bahwa atribut `email` tidak boleh muncul pada respons JSON (`assertJsonMissing`).

---

## Kasus Bug 2: Integritas Basis Data dan Kegagalan Validasi Kolom Deskripsi Mata Kuliah (Database Constraint Violation)

### 1. Gejala Permasalahan
Ketika administrator menambahkan data mata kuliah baru melalui formulir antarmuka web, jika kolom deskripsi dikosongkan oleh pengguna, aplikasi tidak menampilkan pesan validasi ramah, melainkan langsung tumbang dengan layar kesalahan fatal HTTP 500 (*Unhandled Exception*).

### 2. Langkah Reproduksi
1. Login sebagai Administrator (`actingAs($admin)`).
2. Buka formulir pembuatan mata kuliah pada rute `/admin/courses/create`.
3. Masukkan data kode mata kuliah, nama mata kuliah, dan SKS, namun biarkan kolom input deskripsi dalam keadaan kosong.
4. Klik tombol simpan.
5. Aplikasi mengalami crash dengan galat database.

### 3. Cara Menemukan Penyebab (Metode Debugging Sistematis)
* **Pemeriksaan Berkas Log:** Membuka berkas catatan galat pada `storage/logs/laravel.log`. Ditemukan rekaman exception:
  ```text
  testing.ERROR: SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: courses.description
  ```
* **Penelusuran Stack Trace:** Mengabaikan baris tumpukan pustaka internal framework di `vendor/` dan langsung membaca baris pertama yang menyebut folder aplikasi (`app/`).
* **Temuan Baris Kode:** Tertera pada `app/Http/Controllers/CourseController.php:57`:
  ```php
  Course::create($request->all());
  ```
* **Hipotesis Tunggal:** Skema migrasi tabel `courses` menetapkan kolom `description` bersifat `NOT NULL` tanpa nilai default, sementara controller belum memiliki validasi Form Request yang menyaring masukan kosong sebelum perintah SQL `INSERT` dikirimkan ke database engine.

### 4. Perbaikan yang Diterapkan
1. Memastikan aturan Form Request pada `App\Http\Requests\StoreCourseRequest` menangani kolom `description` secara eksplisit (`'description' => ['nullable', 'string']` atau mewajibkan pengisian sesuai spesifikasi bisnis).
2. Memastikan skema migrasi database konsisten dalam menangani input opsional, sehingga kesalahan pengguna di antarmuka disaring oleh lapisan validasi Laravel (menghasilkan status HTTP 422 atau redirect dengan flash message error), bukan meledak sebagai HTTP 500 di tingkat database.

### 5. Automated Test Pencegah Regresi
Pengujian dikunci pada berkas `tests/Feature/BugRegressionTest.php` melalui method:
`test_bug_2_course_creation_handles_description_safely_without_database_crash()`

Test ini memverifikasi bahwa pengiriman data mata kuliah ditangani secara aman oleh sistem validasi dan tidak menimbulkan crash `QueryException` pada database engine.

---

## Kasus Bug 3: Celah IDOR pada Pengunduhan Berkas Pengumpulan Tugas (Insecure Direct Object Reference)

### 1. Gejala Permasalahan
Mahasiswa A dapat mengunduh berkas biner dokumen tugas (jawaban ujian/tugas PDF atau DOCX) milik Mahasiswa B hanya dengan menebak atau mengganti nomor ID submission pada parameter URL unduhan (`/mahasiswa/submissions/{submission_id}/download`).

Celah ini merupakan ancaman IDOR tingkat tinggi (*High Severity*) yang memungkinkan pencurian hasil karya, plagiarisme, dan kebocoran privasi akademik antar mahasiswa.

### 2. Langkah Reproduksi
1. Login sebagai Mahasiswa A (Budi).
2. Mengetahui atau menebak ID berkas submission milik Mahasiswa B (Ani), misalnya `submission_id = 15`.
3. Mahasiswa A mengakses URL:
   `GET /mahasiswa/submissions/15/download`.
4. Berkas jawaban tugas milik Ani langsung terunduh ke komputer Budi tanpa verifikasi kepemilikan.

### 3. Cara Menemukan Penyebab (Metode Debugging Sistematis)
* **Audit Titik Rawan Keamanan:** Ditemukan pada saat proses audit titik rawan otorisasi nomor 18 dan 19 di dokumen `docs/keamanan.md`.
* **Pemeriksaan Logika Controller:** Membuka method `download` pada `app/Http/Controllers/Mahasiswa/SubmissionController.php`. Ditemukan baris kode:
  ```php
  public function download(Submission $submission)
  {
      return Storage::disk('local')->download($submission->file_path);
  }
  ```
* **Hipotesis Tunggal:** Controller langsung menyerahkan berkas dari media penyimpanan tanpa memanggil otorisasi `Gate::authorize()` atau mengecek apakah identitas pengguna yang sedang login (`auth()->id()`) cocok dengan pemilik submission (`$submission->user_id`).

### 4. Perbaikan yang Diterapkan
1. Menyimpan seluruh berkas submission di *private storage* (`storage/app/private/`), bukan di folder publik.
2. Memasang pemeriksaan otorisasi mutlak di awal baris method controller:
   ```php
   Gate::authorize('download', $submission);
   ```
3. Memastikan method `download` pada `App\Policies\SubmissionPolicy` hanya mengizinkan pengunduhan jika:
   - Pengguna adalah mahasiswa pemilik berkas (`$user->id === $submission->user_id`).
   - Atau pengguna adalah dosen yang mengampu mata kuliah terkait.
   - Pihak lain di luar kriteria tersebut otomatis ditolak dengan status HTTP 403 Forbidden.

### 5. Automated Test Pencegah Regresi
Pengujian dikunci pada berkas `tests/Feature/BugRegressionTest.php` melalui method:
`test_bug_3_student_cannot_download_other_students_submission_preventing_idor()`

Test ini menguji skenario negatif di mana Mahasiswa A mencoba mengunduh submission milik Mahasiswa B, dan memverifikasi bahwa respons server mutlak mengembalikan status **403 Forbidden** (`assertForbidden()`).

---

## Pengawalan Pipeline CI/CD (GitHub Actions)

Sebagai pengawal integrasi berkelanjutan (CI Guard) pada Tahap BUILD nomor 4:

1. **Alur Kerja Pengujian Otomatis:** Repositori proyek dikawal oleh alur kerja GitHub Actions pada berkas `.github/workflows/ci.yml`.
2. **Kemandirian Lingkungan Pengujian:** CI menginisialisasi lingkungan pengujian bersih menggunakan basis data SQLite terisolasi, mengeksekusi migrasi dari awal (`migrate:fresh --seed`), serta menguji kemampuan pemulihan basis data (*reversibility check* lewat `migrate:refresh`).
3. **Eksekusi Pengujian Utama:** Perintah pengujian dijalankan secara otomatis pada setiap push dan pull request:
   ```bash
   php artisan test --stop-on-failure
   ```
4. **Jaminan Status Hijau:** Dengan penambahan test regresi pada `tests/Feature/BugRegressionTest.php`, seluruh 3 bug historis di atas dijamin tidak akan pernah muncul kembali tanpa memicu kegagalan build pada pipeline CI/CD.
