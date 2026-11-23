# Catatan Minggu 14 — Debugging dan Testing

## Identitas Mahasiswa

Nama: Anastasya Salsabila Khoirunnisa  
NIM: 10241009  

---

## READ 

Prinsip utama materi Minggu 14 adalah: **"Debugging adalah metode, bukan bakat"**. Siklus perbaikan sistem tidak dilakukan dengan mengubah kode secara acak, melainkan melalui 5 langkah sistematis: (1) Reproduksi bug secara konsisten, (2) Persempit area masalah, (3) Rumuskan hipotesis tunggal, (4) Uji satu hipotesis dengan pengujian termurah, dan (5) Perbaiki serta tulis automated test agar bug yang sama tidak pernah kembali.

Pemeriksaan dan diagnosis dilakukan langsung pada repositori proyek `kampuslms-kelompok-01` menggunakan perkakas bawaan Laravel Test Runner, berkas log sistem, dan pemantauan kueri SQL.

---

### 1. Eksekusi Test Suite Proyek (`php artisan test`)

Langkah awal adalah menjalankan seluruh rangkaian automated test pada aplikasi KampusLMS untuk mencatat ketersediaan tes, rasio kelulusan, dan waktu eksekusi.

* **Perintah Terminal:**
  ```bash
  php artisan test
  ```

* **Hasil Eksekusi Keseluruhan:**
  ```text
     PASS  Tests\Unit\ExampleTest
    - that true is true

     PASS  Tests\Unit\TranscriptServiceTest
    - score to grade conversion                                             0.24s  
    - academic predicate determination                                      0.01s  
    - calculate task attendance metrics                                     0.02s  

     PASS  Tests\Feature\ApiV1FullContractTest
    - auth login and logout and me                                          0.18s  
    - courses index and show contract                                       0.04s  
    - assignments crud and course assignments                               0.05s  

     PASS  Tests\Feature\CourseValidationTest
    - store course validation passes with valid data                        0.03s  
    - store course fails if code duplicate                                  0.02s  
    - store course fails if sks exceeds maximum                             0.02s  
    - update course can keep same code                                      0.02s  
    - update course fails if code belongs to another course                 0.02s  

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

* **Analisis dan Catatan Diagnostik:**
  - **Total Pengujian:** 91 tests (4 Unit tests dan 87 Feature tests).
  - **Status Hasil:** 100% PASS (91 lulus, 0 gagal) dengan total 473 assertions yang tervalidasi.
  - **Durasi Eksekusi:** 4.17 detik menggunakan basis data SQLite in-memory (`:memory:`).
  - Feature test mendominasi test suite proyek karena untuk aplikasi web berbasis alur request-response HTTP (seperti KampusLMS), feature test memberikan manfaat cakupan pengujian tertinggi per baris kode.
  - Modul otorisasi krusial seperti `PolicyAuthorizationTest` (10 tests), `SecurityAttackSimulationTest` (5 tests), serta modul transkrip nilai (`TranscriptControllerTest` dan `TranscriptPolicyTest`) memastikan seluruh pembatasan akses hak pengguna telah terlindungi oleh automated assertions.

---

### 2. Anatomi Test Gagal dan Penunjuk Baris Kode (Deliberate Failure)

Untuk memahami bagaimana Collision (test runner bawaan Laravel) menyajikan informasi galat, dibuat sebuah berkas pengujian sementara yang sengaja digagalkan (`tests/Feature/DeliberateFailureTest.php`).

* **Potongan Kode Uji yang Sengaja Digagalkan:**
  ```php
  public function test_deliberate_assertion_failure(): void
  {
      $this->assertEquals('nilai_harapan', 'nilai_aktual', 'Sengaja digagalkan untuk membaca laporan galat.');
  }

  public function test_deliberate_status_failure(): void
  {
      $response = $this->get('/rute-palsu-tidak-terdaftar');
      $response->assertStatus(200);
  }
  ```

* **Keluaran Laporan Galat pada Terminal:**
  ```text
     FAIL  Tests\Feature\DeliberateFailureTest
    - deliberate assertion failure                                           2.39s  
    - deliberate status failure                                              0.34s  
    ------------------------------------------------------------------------------  
     FAILED  Tests\Feature\DeliberateFailureTest > deliberate assertion failure           
    Sengaja digagalkan untuk membaca laporan galat.
    Failed asserting that two strings are equal.
    --- Expected
    +++ Actual
    @@ @@
    -'nilai_harapan'
    +'nilai_aktual'

    at tests/Feature/DeliberateFailureTest.php:11
        7| class DeliberateFailureTest extends TestCase
        8| {
        9|     public function test_deliberate_assertion_failure(): void
       10|     {
    -> 11|         $this->assertEquals('nilai_harapan', 'nilai_aktual', 'Sengaja digagalkan untuk membaca laporan galat.');
       12|     }

    ------------------------------------------------------------------------------  
     FAILED  Tests\Feature\DeliberateFailureTest > deliberate status failure              
    Expected response status code [200] but received 404.
    Failed asserting that 404 is identical to 200.

    at tests/Feature/DeliberateFailureTest.php:17
       15|     public function test_deliberate_status_failure(): void
       16|     {
    -> 17|         $response->assertStatus(200);
       18|     }
  ```

* **Baris yang Menyebut Berkas Aplikasi:**
  - Laporan galat secara akurat menunjuk baris berkas proyek kita:
    1. `at tests/Feature/DeliberateFailureTest.php:11` (panah menunjuk langsung pemanggilan `assertEquals`).
    2. `at tests/Feature/DeliberateFailureTest.php:17` (panah menunjuk assertion status 200 yang menerima respons 404).
  - **Kesimpulan Praktis:** Test runner modern Laravel secara otomatis memfilter tumpukan pustaka eksternal (*noise vendor*) dan memfokuskan developer langsung pada baris kode dan nomor baris berkas di direktori proyek kita sendiri.

---

### 3. Investigasi Berkas Log Error (`storage/logs/laravel.log`) dan Stack Trace

Pemeriksaan berkas log `storage/logs/laravel.log` dilakukan untuk menemukan rekaman exception nyata dan melatih penelusuran hierarki tumpukan eksekusi (*stack trace*).

* **Contoh Rekaman Exception Riil pada Log:**
  ```text
  [2026-09-21 07:29:44] testing.ERROR: SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: courses.description (Connection: sqlite, Database: C:\Proweb\kampuslms-kelompok-01\database\database.sqlite, SQL: insert into "courses" ("code", "name", "description", "sks", "lecturer_id", "status", "updated_at", "created_at") values (MK835, Pemrograman Berorientasi Objek, ?, 3, 35, active, 2026-09-21 07:29:44, 2026-09-21 07:29:44)) {"exception":"[object] (Illuminate\\Database\\QueryException(code: 23000): ...
  [stacktrace]
  #0 C:/Proweb/kampuslms-kelompok-01/vendor/laravel/framework/src/Illuminate/Database/Connection.php(794): Illuminate/Database/Connection->runQueryCallback(...)
  #1 C:/Proweb/kampuslms-kelompok-01/vendor/laravel/framework/src/Illuminate/Database/Connection.php(573): Illuminate/Database/Connection->run(...)
  #2 C:/Proweb/kampuslms-kelompok-01/vendor/laravel/framework/src/Illuminate/Database/Connection.php(537): Illuminate/Database/Connection->statement(...)
  #3 C:/Proweb/kampuslms-kelompok-01/vendor/laravel/framework/src/Illuminate/Database/Query/Processors/Processor.php(32): ...
  ... (baris #4 hingga #14 seluruhnya berada di dalam vendor/laravel/framework/) ...
  #15 C:/Proweb/kampuslms-kelompok-01/app/Http/Controllers/CourseController.php(57): Illuminate/Database/Eloquent/Model::__callStatic('create', Array)
  #16 C:/Proweb/kampuslms-kelompok-01/vendor/laravel/framework/src/Illuminate/Routing/ControllerDispatcher.php(46): App/Http/Controllers/CourseController->store(...)
  ```

* **Analisis Jejak Tumpukan (Stack Trace):**
  - **Baris Jejak Teratas (#0 s/d #14):** Seluruh baris ini menunjuk berkas inti framework di `vendor/laravel/framework/src/Illuminate/Database/`. Baris tersebut hanyalah mekanisme internal driver database PDO saat memproses pernyataan SQL.
  - **Baris Pertama yang Menyebut `app/`:** Ditemukan pada baris **#15**:
    `app/Http/Controllers/CourseController.php:57`.
  - **Akar Masalah Bug:** Pada baris 57 `CourseController.php`, method `Course::create()` dipanggil dengan data masukan tanpa atribut `description`, padahal kolom tersebut berstatus `NOT NULL` pada skema basis data.
  - **Pedoman Diagnostik:** Saat membaca stack trace yang panjang, abaikan baris yang berawalan `vendor/`. Selalu telusuri ke bawah hingga menemukan **baris pertama yang menyebut folder `app/`** karena di sanalah kode logika buatan kita dieksekusi.

---

### 4. Pemantauan Kueri SQL Real-Time dengan `DB::listen()`

Sesuai panduan konsep 14.1, dilakukan pemantauan kueri basis data pada fitur rekapitulasi nilai dan transkrip dosen dengan memasang listener pada `app/Providers/AppServiceProvider.php`:

* **Implementasi Listener pada `AppServiceProvider.php`:**
  ```php
  public function boot(): void
  {
      // Monitoring query database secara aktif ke log file
      \Illuminate\Support\Facades\DB::listen(function ($query) {
          \Illuminate\Support\Facades\Log::debug('SQL: ' . $query->sql, $query->bindings);
      });
  }
  ```

* **Potongan Log Kueri yang Terekam (`storage/logs/laravel.log`):**
  ```text
  [2026-11-23 15:04:43] testing.DEBUG: SQL: select * from "courses" where "id" = ? limit 1 ["1"] 
  [2026-11-23 15:04:43] testing.DEBUG: SQL: select "users".*, "course_user"."course_id" as "pivot_course_id", "course_user"."user_id" as "pivot_user_id" from "users" inner join "course_user" on "users"."id" = "course_user"."user_id" where "course_user"."course_id" = ? and "users"."id" = ? and "users"."deleted_at" is null limit 1 [1,"4"] 
  [2026-11-23 15:04:43] testing.DEBUG: SQL: select exists(select * from "users" inner join "course_user" on "users"."id" = "course_user"."user_id" where "course_user"."course_id" = ? and "users"."id" = ? and "users"."deleted_at" is null) as "exists" [1,4] 
  [2026-11-23 15:04:43] testing.DEBUG: SQL: select "id", "name", "nim_nip" from "users" where "users"."id" in (2) and "users"."deleted_at" is null  
  [2026-11-23 15:04:43] testing.DEBUG: SQL: select "id", "course_id", "title", "max_score", "due_at", "status" from "assignments" where "assignments"."course_id" = ? and "assignments"."course_id" is not null and "status" = ? order by "due_at" asc [1,"published"] 
  [2026-11-23 15:04:43] testing.DEBUG: SQL: select count(*) as aggregate from "notifications" where "notifications"."notifiable_type" = ? and "notifications"."notifiable_id" = ? and "notifications"."notifiable_id" is not null and "read_at" is null ["App\\Models\\User",2] 
  ```

* **Evaluasi Mutu Kueri:**
  1. **Bebas Masalah N+1:** Kueri memanfaatkan klausul `WHERE IN (...)` dan penanganan *eager loading* terpadu, membuktikan bahwa jumlah kueri bersifat konstan O(1) dan tidak bertambah seiring jumlah mahasiswa terdaftar.
  2. **Prinsip Minimisasi Data (Data Minimization):** Perhatikan kueri `select "id", "name", "nim_nip" from "users"`. Atribut sensitif seperti kata sandi maupun email mahasiswa sengaja tidak ditarik dari basis data, membuktikan kepatuhan terhadap perbaikan celah keamanan privasi.

---

## BREAK 

Berikut adalah hasil pengujian eksperimental terhadap 7 skenario pengujian BREAK yang dijalankan pada repositori proyek:

| # | Skenario yang Dicoba | Apa Prediksinya? | Apa yang Terjadi Sebenarnya? (Penjelasan Analisis & Dampak) |
|:---:|:---|:---|:---|
| **1** | Tulis test yang hanya `assertStatus(200)`, lalu hapus seluruh isi controller-nya | Test tetap berhasil hijau meskipun controller kosong tanpa logika bisnis. | **Test tetap HIJAU (PASS)!** Ini membuktikan bahaya *false positive* (test yang menipu). Menguji status kode HTTP 200 saja tidak menjamin bahwa data disimpan ke basis data, relasi dimuat, atau otorisasi diperiksa. Jika fitur dihapus tapi test tetap hijau, test tersebut tidak bernilai. |
| **2** | Hapus `Gate::authorize()` dari satu controller, lalu jalankan test | Pengujian jalur ditolak (*negative test*) akan mendeteksi celah dan menjadi merah. | **Test langsung GAGAL (MERAH): status 200 diterima padahal diharapkan 403!** Pada `TranscriptControllerTest`, ketika baris otorisasi dinonaktifkan, pengujian yang memverifikasi bahwa dosen lain tidak berhak melihat nilai langsung gagal. Ini membuktikan bahwa test jalur ditolak (*assertForbidden*) adalah benteng utama penutup celah IDOR. |
| **3** | Hapus trait `RefreshDatabase`, lalu jalankan seluruh test dua kali berturut-turut | Data antar tes akan saling menumpuk dan merusak independensi pengujian. | **Test GAGAL: Terjadi galat `no such table` atau tabrakan data UNIQUE!** Tanpa `RefreshDatabase`, data yang dibuat pada test pertama tetap tertinggal di basis data. Pada eksekusi berikutnya, pembuatan akun uji gagal karena tabrakan `UNIQUE constraint failed: users.email`. Test suite menjadi tidak deterministik. |
| **4** | Tulis test yang bergantung pada data seeder, lalu jalankan di lingkungan terisolasi / CI | Pengujian akan gagal karena basis data lingkungan pengujian otomatis belum memiliki data seeder. | **Test GAGAL dengan pesan `ModelNotFoundException`!** Pengujian yang mencari record statis (misal `User::where('email', 'admin@kampuslms.test')`) langsung crash saat seeder tidak dijalankan. Test yang tangguh wajib mandiri dengan membuat datanya sendiri memakai Model Factory. |
| **5** | Hapus `Storage::fake()`, lalu jalankan test fitur unggah berkas tugas | Berkas biner tiruan akan terbuat dan tertinggal di penyimpanan fisik disk lokal. | **Berkas sampah biner tertinggal di `storage/app/` fisik!** File seperti `dokumen_uji.pdf` sungguhan terbentuk di sistem berkas lokal laptop. Jika dijalankan ribuan kali di server CI/CD, ruang penyimpanan server akan penuh oleh sampah uji coba. `Storage::fake()` wajib dipakai agar berkas hanya hidup di memori virtual sementara. |
| **6** | Rusak method `down()` pada salah satu berkas migrasi, lalu jalankan test | Siklus pembersihan database dan rollback migrasi oleh `RefreshDatabase` akan gagal total. | **Eksekusi rollback gagal dengan `RuntimeException`!** `RefreshDatabase` mengandalkan kemampuan migrasi untuk di-rollback secara bersih. Jika method `down()` rusak atau tidak lengkap, reset database terhenti dan seluruh rangkaian pengujian berikutnya gagal berjalan. |
| **7** | Tinggalkan perintah `dd()` di method controller, lalu jalankan automated test | Proses testing akan terputus seketika dan seluruh pengujian setelahnya ikut gagal. | **Test runner berhenti mendadak dengan kode keluar error (Exit code 1)!** Perintah `dd()` menghentikan proses eksekusi PHP secara paksa dan mencetak dump ke terminal. Jika kode ini lolos ke produksi, pengguna akan dihadapkan pada layar hitam debug dan sistem gagal melayani permintaan. |

---

### Detail Investigasi Percobaan BREAK

#### 1. Bahaya Test yang Menipu (False Positive Status 200)
- **Perlakuan Kode:** Dibuat rute tiruan dan controller yang seluruh logika bisnisnya dihapus total, hanya mengembalikan `response('', 200)`. Test hanya melakukan assertion `$response->assertStatus(200)`.
- **Hasil:** Test runner menyatakan status **PASS**.
- **Analisis Dampak:** Pengujian seperti ini berbahaya karena memberikan rasa aman palsu kepada pengembang. Cara memverifikasi test yang bermakna sesuai konsep modul adalah: **rusak logika kodenya dengan sengaja, dan pastikan test-nya berubah menjadi merah**.

#### 2. Uji Pelepasan Otorisasi `Gate::authorize()`
- **Perlakuan Kode:** Baris `Gate::authorize('viewStudentCourseTranscript', [$course, $student])` pada `TranscriptController.php` dikomentari.
- **Hasil:** Test `dosen_cannot_view_transcript_in_other_lecturers_course` langsung gagal dengan laporan:
  ```text
  Expected response status code [403] but received 200.
  Failed asserting that 200 is identical to 403.
  ```
- **Analisis Dampak:** Tanpa test jalur ditolak (assert 403), pelepasan baris otorisasi ini tidak akan pernah terdeteksi sampai terjadi kebocoran data di server produksi.

#### 3. Dampak Tanpa `RefreshDatabase`
- **Perlakuan Kode:** Menghapus deklarasi `use RefreshDatabase;` pada kelas test yang membuat data user.
- **Hasil:** Pada database SQLite in-memory, pengujian gagal seketika dengan pesan `no such table: users` karena migrasi tidak dijalankan. Pada database fisik, eksekusi kedua menghasilkan error tabrakan data unik (`Integrity constraint violation`).
- **Analisis Dampak:** Setiap pengujian wajib bersifat atomik dan terisolasi. `RefreshDatabase` menjamin bahwa setiap unit/feature test dimulai dari kondisi basis data yang steril.

#### 4. Ketergantungan terhadap Data Seeder
- **Perlakuan Kode:** Pengujian memanggil `User::where('email', 'admin.demo@kampuslms.ac.id')->firstOrFail()` tanpa membuat user terlebih dahulu via Factory.
- **Hasil:** Test runner melempar `Illuminate\Database\Eloquent\ModelNotFoundException`.
- **Analisis Dampak:** Data seeder ditujukan untuk demo antarmuka manusia, bukan untuk automated test. Mengandalkan seeder menyebabkan test rapuh saat skema data demo diperbarui atau saat pipeline CI dijalankan pada lingkungan kosong.

#### 5. Eksperimen Tanpa `Storage::fake()`
- **Perlakuan Kode:** Pengujian unggah dokumen dijalankan langsung ke disk lokal default tanpa menginisialisasi `Storage::fake('local')`.
- **Hasil:** Ditemukan berkas nyata terbentuk di dalam subdirektori `storage/app/private/`.
- **Analisis Dampak:** Penumpukan file sampah pengujian akan menghabiskan *inode* dan kapasitas hard disk server, serta berpotensi menimpa berkas riil milik pengguna jika penamaan berkas bertabrakan.

#### 6. Kerusakan pada Method `down()` Migrasi
- **Perlakuan Kode:** Method `down()` pada migrasi diubah untuk melempar `throw new \RuntimeException('down() gagal dieksekusi')`.
- **Hasil:** Perintah `php artisan migrate:rollback` terhenti di tengah jalan dan `RefreshDatabase` gagal mereset skema database.
- **Analisis Dampak:** Migrasi yang tidak *reversible* menyulitkan pemulihan sistem (*disaster recovery*) saat proses deployment di server produksi mengalami kegagalan dan harus di-rollback ke versi sebelumnya.

#### 7. Eksekusi `dd()` Tertinggal di Controller
- **Perlakuan Kode:** Perintah `dd('Pemeriksaan debug')` disisipkan di dalam method controller aktif.
- **Hasil:** Test suite berhenti seketika di tengah jalan dan mengembalikan *exit status 1*, memutus pipeline CI/CD.
- **Analisis Dampak:** `dd()` dan `dump()` dilarang keras masuk ke cabang utama (`main`/`dev`) dan daftar periksa PR. Di lingkungan produksi, instruksi ini mengekspos struktur internal variabel aplikasi ke browser publik dan menghentikan alur aplikasi.

---

## Keterkaitan dengan Prompt Pack Minggu 14

Hasil analisis dan eksperimen READ serta BREAK di atas selaras dengan kerangka kerja Prompt Pack Modul Minggu 14:

1. **Keterkaitan dengan Prompt Pack 14.2 A (Perancangan Test Berimbang):**  
   Pengujian tidak hanya dirancang untuk alur sukses (status 200), melainkan wajib membagi alur menjadi tiga kategori: jalur sukses, jalur ditolak otorisasi (status 403), dan jalur validasi gagal (status 422). Tabel titik rawan pada `docs/keamanan.md` menjadi rujukan utama penentuan test otorisasi.
2. **Keterkaitan dengan Prompt Pack 14.2 B (Implementasi Test Mandiri):**  
   Implementasi test yang berkualitas wajib menggunakan trait `RefreshDatabase`, membuat data mandiri melalui Model Factory, memeriksa assertion spesifik (seperti `assertForbidden()` bukan sekadar tidak 200), serta mengisolasi efek samping dengan `Storage::fake()` dan `Notification::fake()`.
3. **Keterkaitan dengan Prompt Pack 14.2 C (Verifikasi Kualitas dan Makna Test):**  
   Eksperimen BREAK membuktikan bahwa indikator utama test yang bermakna adalah: jika fitur atau baris otorisasi dihapus dengan sengaja, test yang bersangkutan wajib berubah menjadi merah (*failing test*).
4. **Keterkaitan dengan Prompt Pack 14.2 D (Metode Debugging Sistematis):**  
   Ketika menjumpai kegagalan sistem, developer tidak boleh menebak secara acak. Log error harus ditelusuri langsung ke baris pertama di folder `app/`, hipotesis tunggal dirumuskan, dan pengujian termurah dilakukan untuk memverifikasi akar masalah sebelum menerapkan perbaikan permanen.

---

## Kesimpulan Singkat untuk Dipahami (Bahan Evaluasi / Interview Dosen)

1. **Debugging Sistematis vs Tebak-tebakan:** Penanganan galat dilakukan dengan melacak stack trace hingga menemukan baris kode pertama di folder `app/`, bukan tersesat di tumpukan kode pustaka `vendor/`.
2. **Keunggulan Pengujian Jalur Ditolak (Negative Testing):** Di aplikasi akademik KampusLMS, pengujian jalur ditolak (*assertForbidden / 403*) memiliki bobot urgensi lebih tinggi daripada jalur sukses karena merupakan satu-satunya cara otomatis untuk membuktikan bahwa celah IDOR tertutup rapat.
3. **Bahaya Test yang Menipu (*False Positive*):** Test yang hanya memeriksa kode status 200 tanpa memverifikasi data dan efek samping dapat meloloskan controller kosong atau fitur rusak. Kualitas test diuji dengan merusak fitur dan memastikan test menjadi merah.
4. **Peran Kunci `RefreshDatabase` dan Migrasi Reversible:** `RefreshDatabase` menjaga independensi pengujian dengan membersihkan basis data pada setiap tes, yang mensyaratkan method `down()` pada setiap migrasi berfungsi sempurna saat di-rollback.
5. **Isolasi Lingkungan Pengujian:** Penggunaan `Storage::fake()` dan Model Factory mencegah polusi sistem file fisik dan ketergantungan rapuh terhadap data seeder statis.
6. **Kedisiplinan Kode Bersih:** Perintah `dd()` dan `dump()` dilarang masuk ke Pull Request karena mematikan proses pengujian otomatis dan berisiko melumpuhkan antarmuka pengguna di server produksi; pencatatan wajib dialihkan ke `Log::info()` atau `Log::debug()`.
