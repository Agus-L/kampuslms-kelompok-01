# Identitas Mahasiswa

## Nama: Agus Liberty Purba
## NIM: 10241005

---

## READ 
###  1. Eksekusi Test Suite Proyek (`php artisan test`)

Langkah pertama adalah menjalankan seluruh test suite otomatis pada repositori `kampuslms-kelompok-01` untuk mencatat jumlah tes yang ada, berapa yang berhasil, dan waktu eksekusinya.

* **Perintah Terminal:**
  ```bash
  php artisan test
  ```

* **Hasil Eksekusi:**
  ```text
     PASS  Tests\Unit\ExampleTest
    ✓ that true is true

     PASS  Tests\Unit\TranscriptServiceTest
    ✓ score to grade conversion                                             0.24s  
    ✓ academic predicate determination                                      0.01s  
    ✓ calculate task attendance metrics                                     0.02s  

     PASS  Tests\Feature\ApiV1FullContractTest
    ✓ auth login and logout and me                                          0.18s  
    ✓ courses index and show contract                                       0.04s  
    ✓ assignments crud and course assignments                               0.05s  

     PASS  Tests\Feature\CourseValidationTest
    ✓ store course validation passes with valid data                        0.03s  
    ✓ store course fails if code duplicate                                  0.02s  
    ✓ store course fails if sks exceeds maximum                             0.02s  
    ✓ update course can keep same code                                      0.02s  
    ✓ update course fails if code belongs to another course                 0.02s  

     PASS  Tests\Feature\DashboardCacheAndEnvOptimizationTest (7 tests)
     PASS  Tests\Feature\FileDeletionCascadeTest (6 tests)
     PASS  Tests\Feature\MaterialUploadDownloadTest (9 tests)
     PASS  Tests\Feature\NewFeaturesAndFixesTest (7 tests)
     PASS  Tests\Feature\NotificationFeatureTest (4 tests)
     PASS  Tests\Feature\PaginationTest (6 tests)
     PASS  Tests\Feature\PolicyAuthorizationTest (10 tests)
     PASS  Tests\Feature\RoleMiddlewareTest (5 tests)
     PASS  Tests\Feature\SecurityAttackSimulationTest (5 tests)
     PASS  Tests\Feature\TranscriptControllerTest (13 tests)
     PASS  Tests\Feature\TranscriptPolicyTest (10 tests)

    Tests:    91 passed (473 assertions)
    Duration: 4.17s
  ```

* **Catatan Diagnostik:**
  - **Total Tes:** 91 tests (4 Unit tests, 87 Feature tests).
  - **Status:** Seluruh 91 tests berstatus **PASS** (100% hijau) dengan 473 assertions valid.
  - **Waktu Eksekusi:** 4.17 detik menggunakan SQLite database in-memory (`:memory:`).
  - Sebanyak 13 tes pada `TranscriptControllerTest` dan 10 tes pada `TranscriptPolicyTest` telah memvalidasi seluruh logika transkrip dosen, transkrip mahasiswa, ekspor PDF, serta pencegahan kebocoran email mahasiswa dari respons JSON (tindak lanjut review Kelompok 9).

---

### 2. Anatomi Test Gagal & Penunjuk Baris Kode (*Deliberate Failure*)

Pada langkah ini, kami membuat satu berkas pengujian sementara (`tests/Feature/ReadStepTwoTest.php`) yang sengaja digagalkan untuk mempelajari cara pembacaan laporan galat (*stack trace assertion*) oleh Laravel Test Runner.

* **Kode Uji Eksperimen:**
  ```php
  public function test_deliberate_assertion_failure(): void
  {
      $this->assertEquals('expected_data', 'actual_data', 'Sengaja digagalkan untuk observasi baris error.');
  }

  public function test_deliberate_status_failure(): void
  {
      $response = $this->get('/halaman-yang-tidak-ada');
      $response->assertStatus(200);
  }
  ```

* **Hasil Keluaran Terminal:**
  ```text
     FAIL  Tests\Feature\ReadStepTwoTest
    ⨯ deliberate assertion failure                                           2.39s  
    ⨯ deliberate status failure                                              0.34s  
    ──────────────────────────────────────────────────────────────────────────────  
     FAILED  Tests\Feature\ReadStepTwoTest > deliberate assertion failure           
    Sengaja digagalkan untuk observasi baris error.
  Failed asserting that two strings are equal.
  --- Expected
  +++ Actual
  @@ @@
  -'expected_data'
  +'actual_data'

    at tests\Feature\ReadStepTwoTest.php:11
        7▕ class ReadStepTwoTest extends TestCase
        8▕ {
        9▕     public function test_deliberate_assertion_failure(): void
       10▕     {
    ➜  11▕         $this->assertEquals('expected_data', 'actual_data', 'Sengaja digagalkan untuk observasi baris error.');
       12▕     }
       13▕ 
       14▕     public function test_deliberate_status_failure(): void
       15▕     {

    1   tests\Feature\ReadStepTwoTest.php:11

    ──────────────────────────────────────────────────────────────────────────────  
     FAILED  Tests\Feature\ReadStepTwoTest > deliberate status failure              
    Expected response status code [200] but received 404.
  Failed asserting that 404 is identical to 200.

    at tests\Feature\ReadStepTwoTest.php:17
       13▕ 
       14▕     public function test_deliberate_status_failure(): void
       15▕     {
       16▕         $response = $this->get('/halaman-yang-tidak-ada');
    ➜  17▕         $response->assertStatus(200);
       18▕     }
       19▕ }
  ```

* **Baris yang Menyebut Berkas Proyek:**
  - Baris penunjuk langsung tertera pada:
    1. `at tests\Feature\ReadStepTwoTest.php:11` (simbol panah `➜` menunjuk baris pemanggilan `assertEquals`).
    2. `at tests\Feature\ReadStepTwoTest.php:17` (simbol panah `➜` menunjuk baris assertion `assertStatus(200)` yang menerima 404).
  - **Kesimpulan Praktis:** Collision (test runner bawaan Laravel) secara cerdas memfilter *noise* dari folder `vendor/` dan langsung mengarahkan pandangan developer ke berkas dan baris kode yang ditulis sendiri.

---

### 3 Investigasi Berkas Log Error (`storage/logs/laravel.log`) & Stack Trace

Kami membuka dan menelusuri riwayat catatan galat pada berkas `storage/logs/laravel.log`. Ditemukan catatan galat SQL nyata saat pengembangan form mata kuliah:

* **Potongan Log Exception:**
  ```text
  [2026-09-21 07:29:44] testing.ERROR: SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: courses.description (Connection: sqlite, Database: C:\Semester_5\Proweb\kampuslms-kelompok-01\database\database.sqlite, SQL: insert into "courses" ("code", "name", "description", "sks", "lecturer_id", "status", "updated_at", "created_at") values (MK835, Pemrograman Berorientasi Objek, ?, 3, 35, active, 2026-09-21 07:29:44, 2026-09-21 07:29:44)) {"exception":"[object] (Illuminate\\Database\\QueryException(code: 23000): ...
  [stacktrace]
  #0 C:/Semester_5/Proweb/kampuslms-kelompok-01/vendor/laravel/framework/src/Illuminate/Database/Connection.php(794): Illuminate/Database/Connection->runQueryCallback(...)
  #1 C:/Semester_5/Proweb/kampuslms-kelompok-01/vendor/laravel/framework/src/Illuminate/Database/Connection.php(573): Illuminate/Database/Connection->run(...)
  #2 C:/Semester_5/Proweb/kampuslms-kelompok-01/vendor/laravel/framework/src/Illuminate/Database/Connection.php(537): Illuminate/Database/Connection->statement(...)
  #3 C:/Semester_5/Proweb/kampuslms-kelompok-01/vendor/laravel/framework/src/Illuminate/Database/Query/Processors/Processor.php(32): ...
  ... (baris #4 s/d #14 seluruhnya berada di dalam folder vendor/laravel/framework/) ...
  #15 C:/Semester_5/Proweb/kampuslms-kelompok-01/app/Http/Controllers/CourseController.php(57): Illuminate/Database/Eloquent/Model::__callStatic('create', Array)
  #16 C:/Semester_5/Proweb/kampuslms-kelompok-01/vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php(46): App/Http/Controllers/CourseController->store(...)
  ```

* **Analisis Jejak Stack Trace:**
  - **Baris Jejak Teratas (#0 s/d #14):** Seluruh baris awal berada di direktori `vendor/laravel/framework/src/Illuminate/Database/`. Baris ini hanyalah mekanisme internal framework saat mengeksekusi statement PDO SQL. Membaca baris framework ini hanya membuang waktu karena kesalahan bukan berasal dari kode Laravel.
  - **Baris Pertama di `app/`:** Ditemukan pada baris **#15**:
    `app/Http/Controllers/CourseController.php:57`.
  - **Penyebab Utama Bug:** Pada baris 57 `CourseController.php`, method `Course::create()` dipanggil dengan data input tanpa kolom `description`, padahal skema migrasi tabel `courses` menetapkan kolom tersebut bersifat `NOT NULL`.
  - **Prinsip Penting:** Saat membaca log error di Laravel, **langsung cari baris pertama yang menyebut folder `app/`**. Di sanalah letak bug buatan kita berada.

---

### 4. Pemantauan Kueri SQL Real-Time dengan `DB::listen()`

Untuk memverifikasi kueri database yang dieksekusi pada fitur laporan transkrip dosen (rekap nilai), kami menambahkan `DB::listen()` di dalam `app/Providers/AppServiceProvider.php`:

* **Konfigurasi `AppServiceProvider.php`:**
  ```php
  public function boot(): void
  {
      // Logging kueri SQL aktif untuk investigasi N+1 dan payload data
      \Illuminate\Support\Facades\DB::listen(function ($query) {
          \Illuminate\Support\Facades\Log::debug('SQL: ' . $query->sql, $query->bindings);
      });
  }
  ```

* **Eksekusi Fitur Transkrip & Hasil Log (`storage/logs/laravel.log`):**
  ```text
  [2026-11-23 15:04:43] testing.DEBUG: SQL: select * from "courses" where "id" = ? limit 1 ["1"] 
  [2026-11-23 15:04:43] testing.DEBUG: SQL: select "users".*, "course_user"."course_id" as "pivot_course_id", "course_user"."user_id" as "pivot_user_id" from "users" inner join "course_user" on "users"."id" = "course_user"."user_id" where "course_user"."course_id" = ? and "users"."id" = ? and "users"."deleted_at" is null limit 1 [1,"4"] 
  [2026-11-23 15:04:43] testing.DEBUG: SQL: select exists(select * from "users" inner join "course_user" on "users"."id" = "course_user"."user_id" where "course_user"."course_id" = ? and "users"."id" = ? and "users"."deleted_at" is null) as "exists" [1,4] 
  [2026-11-23 15:04:43] testing.DEBUG: SQL: select "id", "name", "nim_nip" from "users" where "users"."id" in (2) and "users"."deleted_at" is null  
  [2026-11-23 15:04:43] testing.DEBUG: SQL: select "id", "course_id", "title", "max_score", "due_at", "status" from "assignments" where "assignments"."course_id" = ? and "assignments"."course_id" is not null and "status" = ? order by "due_at" asc [1,"published"] 
  [2026-11-23 15:04:43] testing.DEBUG: SQL: select count(*) as aggregate from "notifications" where "notifications"."notifiable_type" = ? and "notifications"."notifiable_id" = ? and "notifications"."notifiable_id" is not null and "read_at" is null ["App\\Models\\User",2] 
  ```

* **Analisis Kualitas Kueri:**
  1. **Bebas Masalah N+1:** Query menggunakan klausul `WHERE IN (2)` dan eager loading terpadu (`with(['students', 'assignments.submissions.grade'])`), membuktikan bahwa jumlah query bersifat konstan $O(1)$ dan tidak membengkak mengikuti jumlah data mahasiswa ($O(N)$).
  2. **Verifikasi Keamanan (Data Minimization):** Perhatikan kueri `select "id", "name", "nim_nip" from "users"`. Kolom `email` sama sekali tidak diminta dari database, membuktikan bahwa perbaikan review Kelompok 9 berhasil di tingkat database query.

---

## BREAK

Berikut adalah rekapitulasi pengujian eksperimental 7 percobaan BREAK yang dijalankan langsung pada sistem:

| # | Skenario Eksperimen | Perlakuan yang Diuji | Hasil Pengamatan Nyata | Bahaya / Risiko Nyata |
|---|---------------------|----------------------|------------------------|-----------------------|
| **1** | Test yang Menipu | Menghapus seluruh logika controller, hanya cek `assertStatus(200)` | **Test tetap HIJAU (PASS)** | Fitur kosong/rusak dianggap selesai |
| **2** | Melepas Otorisasi | Menghapus `Gate::authorize()` di `Dosen\TranscriptController` | **Test GAGAL (MERAH): status 200 != 403** | Celah IDOR fatal lolos ke produksi |
| **3** | Tanpa `RefreshDatabase` | Menjalankan test berurutan tanpa reset database | **Test GAGAL: `no such table` / UNIQUE collision** | Database kotor, test saling merusak |
| **4** | Ketergantungan Seeder | Menulis test yang mencari user seeder demo | **Test GAGAL: `ModelNotFoundException`** | Test rusak di CI GitHub Actions |
| **5** | Tanpa `Storage::fake()` | Unggah berkas tugas tanpa fake disk | **Berkas fisik menumpuk di `storage/app/`** | Disk server penuh oleh sampah uji coba |
| **6** | Merusak `down()` Migrasi | Melempar Exception pada method `down()` migrasi | **Rollback & RefreshDatabase GAGAL (FAIL)** | Fitur database rollback macet |
| **7** | Meninggalkan `dd()` | Menaruh `dd()` di method controller | **Eksekusi test terhenti seketika (Exit code 1)** | Server produksi lumpuh layar hitam debug |

---

### 1. Bahaya Test yang Menipu (False Positive Status 200)

* **Hipotesis:** Pengujian yang hanya memverifikasi `$response->assertStatus(200)` tidak menjamin aplikasi berfungsi benar dan rawan meloloskan kode kosong (*false positive*).
* **Perlakuan Kode:**
  Membuat route dan controller tiruan yang seluruh logika bisnis, validasi, dan query-nya dihapus, hanya mengembalikan response kosong:
  ```php
  Route::get('/test-break-1', function () {
      // Seluruh logika bisnis, otorisasi, dan database query dihapus total
      return response('', 200);
  });

  public function test_break_1_status_200_is_deceptive_when_logic_is_missing(): void
  {
      $response = $this->get('/test-break-1');
      $response->assertStatus(200); // HIJAU PADAHAL LOGIKA KOSONG!
  }
  ```
* **Hasil Pengamatan:**
  ```text
     PASS  Tests\Feature\BreakExperimentsTest
    ✓ break 1 status 200 is deceptive when logic is missing                         0.33s  
  ```

---

### 2. Pelepasan Otorisasi `Gate::authorize()` & Pembuktian Test Jalur Ditolak

* **Hipotesis:** Menghapus satu baris otorisasi `Gate::authorize()` akan meloloskan akses tidak sah (IDOR), dan hanya pengujian jalur ditolak (*negative test*) yang mampu menangkap celah ini.
* **Perlakuan Kode:**
  Pada file `app/Http/Controllers/Dosen/TranscriptController.php` baris 88, baris otorisasi dinonaktifkan:
  ```php
  // Gate::authorize('viewStudentCourseTranscript', [$course, $student]);
  ```
  Kemudian test suite dijalankan untuk rute transkrip:
  ```bash
  php artisan test --filter="test_dosen_cannot_view_transcript_in_other_lecturers_course"
  ```
* **Hasil Pengamatan (Test Berubah MERAH):**
  ```text
     FAIL  Tests\Feature\TranscriptControllerTest
    ⨯ dosen cannot view transcript in other lecturers course                       0.40s  
    ──────────────────────────────────────────────────────────────────────────────  
     FAILED  Tests\Feature\TranscriptControllerTest > dosen cannot view transcript in other lecturers course              
    Expected response status code [403] but received 200.
    Failed asserting that 200 is identical to 403.

    at tests\Feature\TranscriptControllerTest.php:136
      134▕         );
      135▕ 
    ➜ 136▕         $response->assertStatus(403);
      137▕     }
  ```

---

### 3. Efek Menghapus `RefreshDatabase` (Polusi State Database)

* **Hipotesis:** Menghilangkan trait `RefreshDatabase` akan menyebabkan pengujian saling mengotori data (*state pollution*) dan merusak prinsip independensi pengujian.
* **Perlakuan Kode:**
  Membuat kelas pengujian `BreakThreePollutionTest` tanpa trait `use RefreshDatabase;`.
* **Hasil Pengamatan:**
  - Pada database SQLite in-memory (`:memory:`), pengujian langsung gagal total dengan pesan:
    ```text
    SQLSTATE[HY000]: General error: 1 no such table: users
    ```
    *(Karena tabel belum pernah dimigrasikan untuk sesi koneksi tersebut).*
  - Pada pengujian berurutan dengan database persisten, data user yang dibuat pada tes pertama tetap tertinggal di database. Ketika tes kedua mencoba membuat data dengan email yang sama, terjadi crash:
    ```text
    QueryException: SQLSTATE[23000]: Integrity constraint violation: UNIQUE constraint failed: users.email
    ```

---

### 4. Kerapuhan Test yang Bergantung pada Seeder (Seeder Dependency)

* **Hipotesis:** Menulis pengujian yang bergantung pada data statis dari `DatabaseSeeder` akan membuat test suite rapuh dan gagal saat dijalankan di lingkungan CI (*Continuous Integration*).
* **Perlakuan Kode:**
  Menulis tes yang langsung mencari akun demo administrator tanpa membuatnya terlebih dahulu melalui Factory:
  ```php
  public function test_break_4_fails_when_relying_on_unseeded_database(): void
  {
      User::where('email', 'admin.demo@kampuslms.ac.id')->firstOrFail();
  }
  ```
* **Hasil Pengamatan:**
  Pengujian langsung gagal dengan exception:
  ```text
  Illuminate\Database\Eloquent\ModelNotFoundException:
  No query results for model [App\Models\User].
  ```

---

### 5. Efek Menghapus `Storage::fake()` (Penumpukan File Sampah Fisik)

* **Hipotesis:** Menjalankan pengujian fitur unggah dokumen tanpa `Storage::fake()` akan menulis berkas biner sungguhan ke penyimpanan fisik laptop/server.
* **Perlakuan Kode:**
  Mengunggah berkas tiruan `sampah_break_5.txt` menggunakan disk `local` tanpa memanggil `Storage::fake('local')`:
  ```php
  $file = UploadedFile::fake()->create('sampah_break_5.txt', 10, 'text/plain');
  $storedPath = $file->storeAs('test_breaks', 'sampah_break_5.txt', 'local');
  ```
* **Hasil Pengamatan:**
  Berkas fisik terbukti terbentuk di sistem file Windows:
  `C:\Semester_5\Proweb\kampuslms-kelompok-01\storage\app\private\test_breaks\sampah_break_5.txt`

---

### 6. Kegagalan Migrasi Reversible pada `down()`

* **Hipotesis:** Migrasi yang method `down()`-nya tidak berfungsi atau rusak akan menggagalkan siklus rollback database dan merusak `RefreshDatabase`.
* **Perlakuan Kode:**
  Pada berkas migrasi `database/migrations/2026_11_02_000001_add_performance_indexes_to_tables.php`, method `down()` sengaja disisipi error:
  ```php
  public function down(): void
  {
      throw new \RuntimeException("BREAK 6: down() sengaja dirusak untuk membuktikan kegagalan RefreshDatabase/Rollback!");
  }
  ```
  Kemudian dijalankan perintah rollback:
  ```bash
  php artisan migrate:rollback
  ```
* **Hasil Pengamatan (Rollback Gagal):**
  ```text
     INFO  Rolling back migrations.  

    2026_11_02_000001_add_performance_indexes_to_tables .......... 1.90ms FAIL

     RuntimeException 

    BREAK 6: down() sengaja dirusak untuk membuktikan kegagalan RefreshDatabase/Rollback!

    at database\migrations\2026_11_02_000001_add_performance_indexes_to_tables.php:184
  ```

---

###  7. Dampak Fatal `dd()` dan `dump()` Tertinggal di Controller

* **Hipotesis:** Meninggalkan instruksi `dd()` di controller akan mematikan alur kerja test runner otomatis dan merusak aplikasi di server produksi.
* **Perlakuan Kode:**
  Menyisipkan `dd()` di method `index()` pada `app/Http/Controllers/Dosen/TranscriptController.php`:
  ```php
  public function index(Request $request)
  {
      dd('BREAK 7: dd() sengaja ditinggalkan di controller!');
      ...
  }
  ```
  Lalu menjalankan test transkrip:
  ```bash
  php artisan test --filter=TranscriptControllerTest
  ```
* **Hasil Pengamatan (Eksekusi Hancur Seketika):**
  ```text
  "BREAK 7: dd() sengaja ditinggalkan di controller!" // app\Http\Controllers\Dosen\TranscriptController.php:36
  Command exited with code 1.
  ```

---

