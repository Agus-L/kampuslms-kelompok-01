# Panduan Responsi & Jawaban Lengkap 12 Soal Ujian KampusLMS

> **Catatan Penilaian Penguji:**
> 1. Penguji memilih 2–3 fitur secara acak dan meminta anggota penulisnya menjelaskan.
> 2. Yang dinilai adalah **keputusan desain** (*mengapa ditulis begitu, apa alternatifnya, apa akibatnya bila dihapus*), **bukan hafalan sintaks**.
> 3. Khusus soal No. 12: Jawaban wajib menggunakan format **"Saya memakai AI untuk X, lalu mengubah Y karena Z"**. Jawaban *"saya tidak tahu, itu dari AI"* bernilai 0.
> 4. Seluruh demonstrasi HTTP menggunakan format terminal **PowerShell Windows** dengan `curl.exe`.

---

## DAFTAR ANGGOTA & PEMBAGIAN BRANCH (Berdasarkan Git Log Branch `dev`)

| No | Branch Git | Nama Anggota | NIM | Fokus Modul & Fitur Utama |
|:--:|---|---|:---:|---|
| 1 | `liberty-1708` | **Agus Liberty Purba** | 10241005 | Migrasi Skema Database Core, Multi-role Auth (`LoginController`), Laravel 12 Bootstrap (`bootstrap/app.php`), CI GitHub Actions |
| 2 | `tasya-1009` | **Anastasya Salsabila Khoirunnisa** | 10241009 | 5 Class Authorization Policy, Integrasi Otorisasi Backend vs Blade UI `@can`, CRUD Mata Kuliah, Dokumentasi API (`docs/api.md`) |
| 3 | `Aliya-Labibah` | **Aliya Labibah** | 10241007 | CRUD Materi & Tugas dengan Otorisasi Backend (`bd1f682`), API Controllers & Endpoints V1 (`d4a37de`), Ownership Check Controller (`3062404`), Form Requests |
| 4 | `aan-mardiah` | **Aan Mardiah** | 10241001 | Query Scoping Seluruh Index Controller, Filter Pencarian Query String, Eager Loading (Pencegahan N+1), Tabel Audit 23 Titik IDOR (`docs/keamanan.md`) |
| 5 | `Dwi_11` | **Annisa Dwi Lestari Sonny** | 10241011 | Penyempurnaan Model Eloquent & Whitelist `$fillable` (`User.php`, `Course.php`, dll. - Commit `4ac982a`), Skrip Otomasi Pengujian Otorisasi (`scripts/test-authz.sh`), Halaman Kustom 403 Forbidden |

---

## TABEL RANGKUMAN CEPAT PEMETAAN SOAL (MURNI BERDASARKAN GIT LOG)

| No Soal | Pertanyaan | Anggota Penulis Utama | Branch & Bukti Commit | Berkas Terkait & Baris Kode Riil | Verifikasi Git |
|:---:|---|---|---|---|:---:|
| **1** | Migrasi & Constraint DB | **Agus Liberty** | `liberty-1708` (`4ee252b`, PR #6) | `database/migrations/2025_01_02_000001_create_courses_table.php` | ✅ Terverifikasi |
| **2** | Composite Unique `course_user` | **Agus Liberty** & **Annisa Dwi** | `liberty-1708` (`4ee252b`, PR #6) & `Dwi_11` (PR #7) | `database/migrations/2025_01_02_000002_create_course_user_table.php` (baris 21) | ✅ Terverifikasi |
| **3** | Mass Assignment & Pencegahnya | **Annisa Dwi** | `Dwi_11` (`4ac982a`) | `app/Models/User.php` (baris 24–29) | ✅ Terverifikasi |
| **4** | Mengapa `role` tidak di `$fillable` | **Annisa Dwi** & **Agus Liberty** | `Dwi_11` (`4ac982a`) & `liberty-1708` (`a7d07d7`, PR #57/#31) | `app/Models/User.php` & `app/Http/Controllers/UserController.php` (baris 101–110) | ✅ Terverifikasi |
| **5** | Tunjukkan 1 Policy & Jelaskan | **Anastasya** | `tasya-1009` (`96136a2`, PR #58) | `app/Policies/CoursePolicy.php` | ✅ Terverifikasi |
| **6** | Mengapa `@can` di Blade tidak cukup | **Aliya Labibah**, **Tasya**, & **Annisa Dwi** | `Aliya-Labibah` (`bd1f682`, `3062404`), `tasya-1009` (`f3a5561`), `Dwi_11` (`8a52a66`, PR #61) | Controller `Gate::authorize()`, Blade `@can`, & `scripts/test-authz.sh` | ✅ Terverifikasi |
| **7** | HTTP 401 vs 403 pada API | **Aliya Labibah**, **Liberty**, & **Annisa Dwi** | `Aliya-Labibah` (`d4a37de`, PR #45), `liberty-1708` (`70da940`, PR #44), `Dwi_11` (`8a52a66`, PR #61) | API V1 Controllers, `bootstrap/app.php` (baris 25–36), & `scripts/test-authz.sh` | ✅ Terverifikasi |
| **8** | `$request->validated()` vs `all()` | **Aliya Labibah** & **Agus Liberty** | `Aliya-Labibah` (`bd1f682`, PR #60) & `liberty-1708` (`88de89f`, PR #25) | `StoreAssignmentRequest.php`, `StoreMaterialRequest.php`, `StoreCourseRequest.php` | ✅ Terverifikasi |
| **9** | Filter di Query String vs Session | **Annisa Dwi**, **Aan**, & **Agus Liberty** | `Dwi_11` (`74c8488`), `aan-mardiah` (`47dd7cb`, PR #59) | `app/Http/Controllers/CourseController.php` (baris 30–39 & simulasi sesi baris 41–64) | ✅ Terverifikasi |
| **10** | `session()->regenerate()` saat login | **Agus Liberty** | `liberty-1708` (`fabf0e7`) | `app/Http/Controllers/Auth/LoginController.php` (baris 47–49) | ✅ Terverifikasi |
| **11** | Middleware di Laravel 12 | **Agus Liberty** | `liberty-1708` (`a7d07d7`, PR #31) | `bootstrap/app.php` (baris 18–22) | ✅ Terverifikasi |
| **12** | Bantuan AI & Perubahannya | **Semua Anggota** | *(Sesuai fiturnya masing-masing)* | Logika kepemilikan, constraint DB, sanitasi input, negative test, scoping | ✅ Terverifikasi |

---

## PANDUAN CEPAT PERINTAH `curl.exe` (POWERSHELL WINDOWS)

Gunakan template perintah ini saat diminta memperagakan di terminal:

```powershell
# 1. Login Dosen 1 (Mengambil Token)
curl.exe -X POST http://127.0.0.1:8000/api/v1/auth/login -H "Accept: application/json" -H "Content-Type: application/json" -d '{\"email\":\"dosen@kampuslms.test\",\"password\":\"password\"}'

# 2. Login Mahasiswa (Mengambil Token)
curl.exe -X POST http://127.0.0.1:8000/api/v1/auth/login -H "Accept: application/json" -H "Content-Type: application/json" -d '{\"email\":\"mahasiswa@kampuslms.test\",\"password\":\"password\"}'

# 3. Cek Profil /me
curl.exe -i http://127.0.0.1:8000/api/v1/me -H "Accept: application/json" -H "Authorization: Bearer <TOKEN_KAMU>"

# 4. Uji 401 Unauthorized (Tanpa Token)
curl.exe -i http://127.0.0.1:8000/api/v1/courses -H "Accept: application/json"

# 5. Uji 403 Forbidden (Mahasiswa Akses Submission Orang Lain / IDOR)
curl.exe -i http://127.0.0.1:8000/api/v1/submissions/2 -H "Accept: application/json" -H "Authorization: Bearer <TOKEN_MAHASISWA>"

# 6. Uji 403 Forbidden (Dosen 2 Ubah Tugas Dosen 1 / Bypass UI)
curl.exe -i -X PUT http://127.0.0.1:8000/api/v1/assignments/1 -H "Accept: application/json" -H "Content-Type: application/json" -H "Authorization: Bearer <TOKEN_DOSEN2>" -d '{\"title\":\"Tugas Dibajak Dosen 2\"}'
```

---

## JAWABAN LENGKAP 12 SOAL RESPONSI

---

### Soal 1: Tunjukkan migrasi yang Anda tulis. Jelaskan setiap constraint di dalamnya.
* **Branch Penulis:** `liberty-1708` (Agus Liberty Purba — Commit `4ee252b`, PR #6 `15e790f`), disempurnakan di `74c8488` (Dwi) & `88de89f` (Liberty)
* **Berkas:** `database/migrations/2025_01_02_000001_create_courses_table.php`

```php
Schema::create('courses', function (Blueprint $table) {
    $table->id();
    $table->string('code')->unique();
    $table->string('name');
    $table->text('description')->nullable();
    $table->tinyInteger('sks');
    $table->foreignId('lecturer_id')->constrained('users')->restrictOnDelete();
    $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
    $table->timestamps();
});
```

#### Penjelasan Constraint & Keputusan Desain:
1. **`$table->id()` (`PRIMARY KEY`)**:
   * *Keputusan:* Menggunakan unsigned big integer auto-increment untuk menjamin keunikan identitas baris secara absolut.
2. **`$table->string('code')->unique()` (`UNIQUE CONSTRAINT`)**:
   * *Keputusan:* Kode mata kuliah (misal `IF101`) adalah identifier akademik yang tidak boleh ganda.
   * *Alternatif:* Tanpa constraint unik, hanya mengandalkan validasi form. *Ditolak* karena tidak menjamin integritas data bila ada input data dari seeder atau integrasi sistem lain.
   * *Akibat bila dihapus:* Terjadi duplikasi kode mata kuliah di katalog.
3. **`$table->text('description')->nullable()` (`NULLABLE`)**:
   * *Keputusan:* Memberikan fleksibilitas agar mata kuliah baru dapat dibuat meski silabus/deskripsi belum selesai disusun.
4. **`$table->tinyInteger('sks')` (`CHECK/TYPE CONSTRAINT`)**:
   * *Keputusan:* Membatasi kapasitas tipe data (1 byte) yang efisien untuk rentang SKS (1–6).
5. **`$table->foreignId('lecturer_id')->constrained('users')->restrictOnDelete()` (`FOREIGN KEY RESTRICT`)**:
   * *Keputusan:* Menjaga integritas referensial. Dosen yang masih memiliki mata kuliah aktif **dilarang keras untuk dihapus** dari database.
   * *Alternatif:* 
     * `cascadeOnDelete()`: *Ditolak* karena jika user dosen terhapus, seluruh mata kuliahnya ikut lenyap beserta materi, tugas, dan nilai mahasiswa di dalamnya.
     * `nullOnDelete()`: *Ditolak* karena mata kuliah wajib memiliki pengampu yang bertanggung jawab.
   * *Akibat bila dihapus:* Akun dosen bisa terhapus, meninggalkan data mata kuliah "yatim" (*orphaned records*) yang memicu crash aplikasi (`null pointer` saat memanggil `$course->lecturer->name`).
6. **`$table->enum('status', ['draft', 'active', 'archived'])->default('draft')` (`ENUM & DEFAULT VALUE`)**:
   * *Keputusan:* Menjamin konsistensi status siklus hidup mata kuliah di tingkat database dan menetapkan status awal otomatis sebagai `draft`.

#### 💡 Kaitan dengan Soal 12 (Penggunaan & Koreksi Terhadap AI):
> *"Saya memakai AI untuk men-generate kerangka skema migrasi tabel courses, lalu mengaudit dan mengubah foreign key lecturer_id dari cascadeOnDelete() menjadi restrictOnDelete(), serta menambahkan unique() pada kode mata kuliah karena cascade dapat melenyapkan seluruh data historis akademik saat akun dosen dihapus."*
* **Kode AI Awal:** `$table->foreignId('lecturer_id')->constrained()->cascadeOnDelete();`
* **Koreksi Manusia (Liberty — Commit `4ee252b`):** `$table->foreignId('lecturer_id')->constrained('users')->restrictOnDelete();`
* **Alasan Desain & Integritas:** Menghindari *orphaned records* dan menjamin dosen yang masih memiliki mata kuliah aktif tidak dapat dihapus sembarangan dari sistem.

---

### Soal 2: Mengapa course_user memiliki unique composite? Peragakan apa yang terjadi tanpa constraint tersebut.
* **Branch Penulis:** `liberty-1708` (skema migrasi) & `Dwi_11` (relasi pivot di PR #7)
* **Berkas:** `database/migrations/2025_01_02_000002_create_course_user_table.php` baris 21

```php
$table->unique(['course_id', 'user_id']);
```

#### Keputusan Desain:
* **Mengapa Ditulis Begitu?**
  Tabel pivot `course_user` mencatat data enrollment. Secara logika akademik, seorang mahasiswa hanya boleh terdaftar **satu kali** pada mata kuliah yang sama.
* **Apa Alternatifnya?**
  * *Alternatif: Pengecekan manual di controller PHP (`if ($user->courses()->where(...)->exists())`).*
  * *Alasan Ditolak:* Rentan terhadap **Race Condition**. Jika mahasiswa melakukan *double-click* cepat pada tombol "Daftar", dua request HTTP masuk secara paralel dalam fraksi milidetik sebelum query pertama selesai ditulis, sehingga kedua baris tetap tersimpan. Proteksi wajib dikunci di level ACID database engine.
* **Apa Akibatnya Bila Dihapus?**
  1. Mahasiswa terdaftar ganda di mata kuliah yang sama.
  2. Perhitungan agregasi kuota kelas menjadi salah (`$course->students()->count()` menghitung 2x untuk 1 orang).
  3. Mahasiswa melihat tugas ganda dan muncul duplikasi penilaian.

#### Peragaan di Terminal (Tinker):
```bash
php artisan tinker
```
```php
// Percobaan 1: Berhasil tersimpan
DB::table('course_user')->insert(['course_id' => 1, 'user_id' => 5]);

// Percobaan 2 (Duplikasi): Langsung DITOLAK oleh database!
DB::table('course_user')->insert(['course_id' => 1, 'user_id' => 5]);
```
**Hasil Output:**
`QueryException: UNIQUE constraint failed: course_user.course_id, course_user.user_id (SQLSTATE[23000])`

#### 💡 Kaitan dengan Soal 12 (Penggunaan & Koreksi Terhadap AI):
> *"Saya memakai AI untuk merancang validasi pendaftaran mata kuliah, lalu mengaudit dan mengubah proteksi dari sekadar pengecekan manual di controller menjadi composite unique key di tingkat database engine karena validasi controller rentan terhadap Race Condition saat user melakukan double-click cepat."*
* **Kode AI Awal:** Hanya pengecekan manual PHP di controller: `if ($user->courses()->where('course_id', $id)->exists()) ...`
* **Koreksi Manusia (Liberty & Dwi — Commit `4ee252b` & PR #7):** Menambahkan `$table->unique(['course_id', 'user_id']);` di skema migrasi `course_user`.
* **Alasan Desain & Integritas:** Mengunci proteksi duplikasi di level ACID database engine agar kebal dari request HTTP paralel yang masuk dalam fraksi milidetik bersamaan.

---

### Soal 3: Apa itu mass assignment? Tunjukkan bagian kode Anda yang mencegahnya.
* **Branch Penulis:** `Dwi_11` (Annisa Dwi — Model `User.php` Commit `4ac982a`) & `liberty-1708` / `tasya-1009` (`UserController.php`)

#### Keputusan Desain:
* **Pengertian Mass Assignment:**
  Mekanisme Eloquent untuk mengisi banyak atribut model sekaligus melalui array asosiatif (contoh: `User::create($request->all())`).
* **Bahaya Keamanan:**
  Jika penyerang menyisipkan field berbahaya (seperti `role=admin`, `is_admin=true`, atau `lecturer_id=99`) dalam payload request, dan model tidak membatasinya, penyerang dapat mengelevasi hak aksesnya secara instan (*Privilege Escalation*).
* **Apa Alternatifnya?**
  * *Alternatif: Menggunakan `$guarded = []`.*
  * *Alasan Ditolak:* Membuka seluruh kolom tanpa proteksi (*black-box approach*). Jika ada penambahan kolom sensitif baru di masa depan, kolom tersebut langsung rentan dimanipulasi.
* **Apa Akibatnya Bila Dihapus?**
  Model terbuka untuk disusupi data apa pun yang dikirim melalui request.

#### Bagian Kode Pencegah di KampusLMS:
1. **Whitelist `$fillable` di `app/Models/User.php` baris 24–29 (Dibuat oleh Annisa Dwi - Commit `4ac982a`):**
   ```php
   protected $fillable = [
       'name',
       'email',
       'password',
       'nim_nip',
   ];
   ```
2. **Pengisian Eksplisit di `app/Http/Controllers/UserController.php` baris 101–108 (Dibuat oleh Liberty & Tasya):**
   Hanya kolom tertentu yang diisi dari request, sementara field sensitif seperti `role` diisolasi khusus untuk admin.

#### 💡 Kaitan dengan Soal 12 (Penggunaan & Koreksi Terhadap AI):
> *"Saya memakai AI untuk membuat model Eloquent User dan Course, lalu mengaudit dan mengubah pendekatan proteksi mass assignment dari $guarded = [] menjadi whitelist ketat $fillable karena $guarded membuka seluruh kolom tanpa pengawasan sehingga rentan disusupi manipulasi atribut."*
* **Kode AI Awal:** Menggunakan `protected $guarded = [];` agar cepat dan tidak perlu mendaftarkan kolom satu per satu.
* **Koreksi Manusia (Annisa Dwi — Commit `4ac982a`):** Menggantinya menjadi `protected $fillable = ['name', 'email', 'password', 'nim_nip'];`.
* **Alasan Desain & Keamanan:** Menerapkan prinsip *Defense in Depth* dan *Least Privilege* pada lapisan data model agar terlindung dari penyusupan payload liar.

---

### Soal 4: Mengapa role tidak ada di $fillable? Di mana nilainya diisi?
* **Branch Penulis:** `Dwi_11` (Annisa Dwi — Model `User.php` Commit `4ac982a`) & `liberty-1708` (`UserController.php` Commit `a7d07d7` / PR #57)

#### Keputusan Desain:
* **Mengapa `role` Tidak Ada di `$fillable`?**
  `role` adalah kunci penentu otorisasi seluruh gerbang sistem (`admin`, `dosen`, `mahasiswa`). Mengizinkan `role` berada di `$fillable` berarti mengizinkan siapa saja yang mengakses endpoint pendaftaran atau pembaruan profil untuk menyuntikkan `role=admin`.
* **Apa Alternatifnya?**
  * *Alternatif: Menaruh `role` di `$fillable` lalu menyaringnya lewat validasi form.*
  * *Alasan Ditolak:* Melanggar prinsip *Defense in Depth* (pertahanan berlapis). Jika suatu saat developer lupa menuliskan rules validasi di salah satu form, keamanan sistem langsung jebol.
* **Apa Akibatnya Bila Dihapus dari Controller (dan dimasukkan ke `$fillable`)?**
  Penyerang dapat mengirim payload `{"role":"admin"}` dan otomatis berubah menjadi administrator sistem.

#### Di Mana Nilainya Diisi?
1. **Secara Eksplisit oleh Administrator di `app/Http/Controllers/UserController.php` baris 108:**
   ```php
   // Kolom reguler dari $fillable
   $user = new User();
   $user->name     = $validated['name'];
   $user->email    = $validated['email'];
   $user->password = $validated['password'];
   $user->nim_nip  = $validated['nim_nip'] ?? null;

   // Role diisi secara EKSPLISIT — bukan mass assignment
   $user->role = $validated['role'];
   $user->save();
   ```
2. **Nilai Default Database di `database/migrations/0001_01_01_000000_create_users_table.php` baris 19:**
   `$table->enum('role', ['admin', 'dosen', 'mahasiswa'])->default('mahasiswa');`
3. **Di Seeder Sistem:** `database/seeders/KampusLMSSeeder.php` baris 68 untuk pembuatan akun demo terverifikasi.

#### 💡 Kaitan dengan Soal 12 (Penggunaan & Koreksi Terhadap AI):
> *"Saya memakai AI untuk membuat formulir pendaftaran pengguna, lalu mengaudit dan mengubah model User dengan mengecualikan 'role' dari $fillable dan menuliskannya secara eksplisit di controller karena membiarkan 'role' di $fillable memicu celah Privilege Escalation di mana pengguna biasa bisa menyuntikkan role=admin."*
* **Kode AI Awal:** Menaruh `'role'` di `$fillable` agar praktis memakai `User::create($request->all())`.
* **Koreksi Manusia (Dwi & Liberty — Commit `4ac982a` & `a7d07d7`):** Menghapus `'role'` dari `$fillable` dan menulis pengisian eksplisit `$user->role = $validated['role'];` khusus pada method administrator di `UserController.php`.
* **Alasan Desain & Keamanan:** Menjaga gerbang otorisasi utama sistem dari serangan eskalasi hak akses (*Privilege Escalation*) jika validasi form terlewat.

---

### Soal 5: Tunjukkan satu Policy yang Anda tulis dan jelaskan setiap barisnya.
* **Branch Penulis:** `tasya-1009` (Anastasya S. K. — Commit `96136a2`, PR #58)
* **Berkas:** `app/Policies/CoursePolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, Course $course): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $course->lecturer_id === $user->id;
        }

        if ($user->role === 'mahasiswa') {
            return $course->students()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Course $course): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->role === 'admin';
    }
}
```

#### Penjelasan Baris per Baris & Keputusan Desain:
* **Baris 11–14 (`viewAny`):**
  * *Keputusan:* Mengembalikan `true` hanya untuk `admin`.
  * *Mengapa?* Dosen dan mahasiswa tidak boleh melihat katalog global internal admin, melainkan diarahkan ke rute perannya masing-masing.
* **Baris 21–43 (`view`):**
  * *Baris 30–32:* Admin diizinkan melihat mata kuliah mana pun untuk kebutuhan tata kelola akademik.
  * *Baris 34–36:* Untuk Dosen, dilakukan verifikasi kepemilikan mutlak: `$course->lecturer_id === $user->id`. Dosen A ditolak jika mencoba melihat mata kuliah yang diampu Dosen B (menutup celah IDOR).
  * *Baris 38–40:* Untuk Mahasiswa, diperiksa keikutsertaan aktif: `$course->students()->where('user_id', $user->id)->exists()`. Mahasiswa hanya boleh melihat mata kuliah yang ia ikuti.
  * *Baris 42:* Mengembalikan `false` (HTTP 403) jika tidak memenuhi aturan di atas.
* **Baris 46–76 (`create`, `update`, `delete`):**
  * *Keputusan:* Hak cipta, ubah, dan hapus mata kuliah dibatasi mutlak hanya untuk `admin`.

#### 💡 Kaitan dengan Soal 12 (Penggunaan & Koreksi Terhadap AI):
> *"Saya memakai AI untuk membuat kerangka Policy CoursePolicy, lalu mengaudit dan mengubah logika otorisasi internalnya dari sekadar pemeriksaan role statis ($user->role === 'dosen') menjadi verifikasi relasi kepemilikan data ($course->lecturer_id === $user->id) karena tanpa verifikasi kepemilikan, Dosen A dapat mengakses dan memodifikasi mata kuliah milik Dosen B (celah IDOR)."*
* **Kode AI Awal:** Hanya memeriksa peran: `return $user->role === 'dosen';`.
* **Koreksi Manusia (Tasya — Commit `96136a2` & PR #58):** Memvalidasi relasi database: `$course->lecturer_id === $user->id` (dosen) dan `$course->students()->where('user_id', $user->id)->exists()` (mahasiswa).
* **Alasan Desain & Keamanan:** Menutup celah Insecure Direct Object Reference (IDOR) dan menjamin isolasi hak akses data multitenan antar user dengan peran yang sama.

---

### Soal 6: Mengapa @can di Blade tidak cukup untuk otorisasi? Peragakan.
* **Branch Penulis:** `Aliya-Labibah` (Otorisasi Backend Controller Commit `bd1f682` & `3062404`), `tasya-1009` (Policy & View Blade `@can` Commit `f3a5561`), dan `Dwi_11` (`scripts/test-authz.sh`)

#### Keputusan Desain:
* **Mengapa `@can` Tidak Cukup?**
  Direktif `@can` di Blade hanyalah penyaring visual pada **lapisan presentasi (UI Cosmetic)** yang menerapkan prinsip *Security through Obscurity*. Menyembunyikan tombol "Edit" di layar peramban tidak menghentikan pengguna mengirimkan request HTTP secara langsung.
* **Apa Alternatifnya?**
  * *Alternatif: Hanya mengandalkan `@can` di Blade tanpa otorisasi controller.*
  * *Alasan Ditolak:* Siapa pun dapat mengetikkan URL endpoint langsung di *address bar* atau menembak server via cURL / Postman / fetch DevTools. Otorisasi sejati wajib ditegakkan di backend melalui `Gate::authorize()` atau Policy.
* **Apa Akibatnya Bila `Gate::authorize()` Dihapus dari Controller?**
  Penyerang cukup mengirim HTTP request langsung, dan server akan mengeksekusi manipulasi data tersebut meskipun tombol di antarmuka web tidak pernah ditampilkan.

#### Peragaan dengan `curl.exe` (PowerShell Windows):
1. **Dosen 2 login untuk memperoleh token:**
   ```powershell
   curl.exe -X POST http://127.0.0.1:8000/api/v1/auth/login -H "Accept: application/json" -H "Content-Type: application/json" -d '{\"email\":\"dosen2@kampuslms.test\",\"password\":\"password\"}'
   ```
2. **Dosen 2 mencoba menembak langsung tugas ID 1 milik Dosen 1:**
   ```powershell
   curl.exe -i -X PUT http://127.0.0.1:8000/api/v1/assignments/1 -H "Accept: application/json" -H "Content-Type: application/json" -H "Authorization: Bearer <TOKEN_DOSEN2>" -d '{\"title\":\"Tugas Dibajak Dosen 2\"}'
   ```
**Hasil Nyata dari Server:**
```http
HTTP/1.1 403 Forbidden
Content-Type: application/json

{"message":"Anda tidak memiliki akses ke sumber daya ini."}
```
> **Kesimpulan Peragaan:** Server langsung menolak dengan status **403 Forbidden** karena backend memiliki `Gate::authorize('update', $assignment)`, membuktikan bahwa proteksi backend-lah yang sesungguhnya melindungi sistem.

#### 💡 Kaitan dengan Soal 12 (Penggunaan & Koreksi Terhadap AI):
> *"Saya memakai AI untuk membuat antarmuka halaman tugas dan materi di Blade, lalu mengaudit dan mengubah arsitektur keamanannya dengan menambahkan Gate::authorize() di controller backend karena AI awalnya hanya menyembunyikan tombol menggunakan @can di Blade yang merupakan lapisan kosmetik semata dan dapat dibypass langsung melalui cURL atau Postman."*
* **Kode AI Awal:** Hanya membungkus tombol di Blade: `@can('update', $assignment) <a href="...">Edit</a> @endcan` tanpa proteksi di method `update()` controller.
* **Koreksi Manusia (Aliya & Tasya — Commit `bd1f682` & `f3a5561`):** Memasang `Gate::authorize('update', $assignment)` di backend controller dan memvalidasinya secara otomatis lewat skrip bash `test-authz.sh` (`8a52a66` oleh Dwi).
* **Alasan Desain & Keamanan:** Mencegah prinsip *Security through Obscurity*; otorisasi sejati wajib ditegakkan di sisi server (*Server-Side Authorization*).

---

### Soal 7: Apa perbedaan HTTP 401 dan 403? Tunjukkan masing-masing satu contoh pada API Anda.
* **Branch Penulis:** `Aliya-Labibah` (API Controller Commit `d4a37de`, PR #45), `liberty-1708` (Exception Handler di `bootstrap/app.php` baris 25–36, Commit `70da940` / `0982562`, PR #44), dan `Dwi_11` (Skrip Otomasi Pengujian Otorisasi `scripts/test-authz.sh` Commit `8a52a66`, PR #61)

#### Keputusan Desain & Perbedaan Konseptual:
* **HTTP 401 Unauthorized (*Unauthenticated*):**
  Server tidak mengetahui identitas pengguna. Token otentikasi tidak ada, tidak valid, atau kedaluwarsa. Maknanya: *"Silakan login terlebih dahulu."*
* **HTTP 403 Forbidden (*Unauthorized Access*):**
  Server mengenali identitas pengguna dengan sah (token valid), tetapi pengguna **tidak memiliki hak akses/izin** terhadap data tersebut. Melakukan login ulang dengan akun yang sama tidak akan menyelesaikan masalah.
* **Apa Alternatifnya?**
  * *Alternatif: Menyamakan semua kegagalan menjadi 401 atau 404.*
  * *Alasan Ditolak:* Klien frontend SPA / Mobile akan mengalami *infinite login loop* jika error 403 dianggap sebagai sesi habis (401).

#### Contoh Peragaan Nyata Menggunakan `curl.exe` (PowerShell Windows):

#### A. Contoh HTTP 401 Unauthorized (Tanpa Token):
```powershell
curl.exe -i http://127.0.0.1:8000/api/v1/courses -H "Accept: application/json"
```
**Respon Server:**
```http
HTTP/1.1 401 Unauthorized
Content-Type: application/json

{"message":"Unauthenticated."}
```

#### B. Contoh HTTP 403 Forbidden (Mahasiswa Akses Submission Orang Lain / IDOR):
1. Login sebagai mahasiswa untuk mengambil token:
   ```powershell
   curl.exe -X POST http://127.0.0.1:8000/api/v1/auth/login -H "Accept: application/json" -H "Content-Type: application/json" -d '{\"email\":\"mahasiswa@kampuslms.test\",\"password\":\"password\"}'
   ```
2. Tembak submission milik mahasiswa lain (ID: 2):
   ```powershell
   curl.exe -i http://127.0.0.1:8000/api/v1/submissions/2 -H "Accept: application/json" -H "Authorization: Bearer <TOKEN_MAHASISWA>"
   ```
**Respon Server:**
```http
HTTP/1.1 403 Forbidden
Content-Type: application/json

{"message":"Anda tidak memiliki akses ke sumber daya ini."}
```

#### 💡 Kaitan dengan Soal 12 (Penggunaan & Koreksi Terhadap AI):
> *"Saya memakai AI untuk membuat respons penanganan error API, lalu mengaudit dan mengubah exception handler di bootstrap/app.php agar memisahkan secara tegas HTTP 401 (Unauthenticated) dan HTTP 403 (Forbidden) karena jika pelanggaran IDOR disamaratakan mengembalikan 401, aplikasi klien akan terjebak dalam infinite login loop."*
* **Kode AI Awal:** Mengembalikan halaman HTML default Laravel atau menganggap seluruh kegagalan akses otorisasi sebagai 401 Unauthorized.
* **Koreksi Manusia (Aliya, Liberty, & Dwi — Commit `d4a37de`, `70da940`, `8a52a66`):** Menulis custom exception rendering di `bootstrap/app.php` baris 25–36 yang merespons JSON 401 saat token tidak ada, dan JSON 403 saat token sah tetapi tidak memiliki izin objek (IDOR).
* **Alasan Desain & UX Frontend:** Menjamin kepatuhan standar REST API di mana status 401 meminta klien re-login, sedangkan 403 melarang akses data secara permanen.

---

### Soal 8: Mengapa $request->validated() lebih aman daripada $request->all()?
* **Branch Penulis:** `Aliya-Labibah` (Form Requests Materi/Tugas Commit `bd1f682`) & `liberty-1708` (`StoreCourseRequest.php` Commit `88de89f`)
* **Berkas:** `app/Http/Requests/StoreCourseRequest.php` & `app/Http/Requests/StoreMaterialRequest.php`

#### Keputusan Desain:
* **Mengapa Menggunakan `$request->validated()`?**
  Method ini **hanya mengembalikan data yang secara eksplisit didefinisikan dan lolos aturan validasi** pada Form Request class.
* **Apa Alternatifnya?**
  * *Alternatif: Menggunakan `$request->all()`.*
  * *Alasan Ditolak:* `$request->all()` mengambil seluruh data mentah kiriman client, termasuk parameter berbahaya yang disuntikkan penyerang lewat HTTP body atau URL query parameter. Jika diteruskan ke `Model::create($request->all())`, celah Mass Assignment terbuka lebar.
* **Apa Akibatnya Bila Dihapus (Diganti `$request->all()`)?**
  Input liar yang tidak divalidasi akan lolos ke lapisan model dan query database. Integritas data rusak dan data sensitif rentan ditimpa secara ilegal.

#### 💡 Kaitan dengan Soal 12 (Penggunaan & Koreksi Terhadap AI):
> *"Saya memakai AI untuk membuat method penyimpanan formulir tugas dan materi, lalu mengaudit dan mengubah penggunaan $request->all() menjadi $request->validated() berbasis Form Request class karena $request->all() meloloskan parameter liar dari request body yang rentan membuka celah Mass Assignment."*
* **Kode AI Awal:** `Assignment::create($request->all());` langsung dari controller.
* **Koreksi Manusia (Aliya & Liberty — Commit `bd1f682` & `88de89f`):** Membuat kelas Form Request (`StoreAssignmentRequest`, `StoreMaterialRequest`, `StoreCourseRequest`) dan memanggil `$validated = $request->validated();`.
* **Alasan Desain & Keamanan:** Menjamin hanya data yang telah divalidasi dan lolos whitelist aturan yang dapat diproses ke penyimpanan database.

---

### Soal 9: Mengapa filter pencarian disimpan di query string, bukan di session?
* **Branch Penulis:** `Dwi_11` (Fitur Pencarian & Paginasi Commit `74c8488`), `aan-mardiah` (Query Scoping Commit `47dd7cb`), & `liberty-1708` (Eksperimen CP No. 4 di `CourseController.php`)
* **Berkas:** `app/Http/Controllers/CourseController.php` baris 30–39

```php
$courses = Course::query()
    ->with('lecturer')
    ->when($request->filled('q'), fn ($query) =>
        $query->where('name', 'like', '%' . $request->q . '%')
              ->orWhere('code', 'like', '%' . $request->q . '%'))
    ->when($request->filled('status'), fn ($query) =>
        $query->where('status', $request->status))
    ->latest()
    ->paginate(15)
    ->withQueryString();
```

#### Keputusan Desain:
1. **Shareable & Bookmarkable (Dapat Dibagikan & Disimpan):**
   URL `/courses?q=Basis+Data&status=active` merepresentasikan kondisi data secara utuh. Jika tautan ini disalin ke orang lain, mereka akan melihat hasil pencarian yang sama persis.
2. **Stateless Sesuai Standar REST & HTTP GET:**
   HTTP GET dirancang bersifat *idempotent* dan *stateless*. Session hanya digunakan untuk menyimpan identitas autentikasi user, bukan kueri data.
3. **Mendukung Fitur Browser (Multi-tab & Back/Forward):**
   Jika filter disimpan di session, saat pengguna membuka Tab 1 (mencari "Algoritma") dan Tab 2 (mencari "Jaringan"), pencarian di Tab 2 akan menimpa filter di Tab 1 (*Session Collision*). Dengan query string, setiap tab bekerja independen.
4. **Mencegah Bug *Sticky Filter* (Filter Menempel Terus):**
   Pada `CourseController.php` baris 41–64 terdapat simulasi kode rusak: ketika filter disimpan di session, saat pengguna kembali mengklik menu "Daftar Mata Kuliah", filter lama tetap menempel dan menyembunyikan data mata kuliah lainnya. Dengan query string, mengklik menu utama langsung membersihkan filter secara otomatis.

#### 💡 Kaitan dengan Soal 12 (Penggunaan & Koreksi Terhadap AI):
> *"Saya memakai AI untuk membuat fitur filter pencarian mata kuliah, lalu mengaudit dan mengubah mekanisme penyimpanan filter dari Session ke URL Query String dengan withQueryString() karena filter di session memicu bug Sticky Filter dan Session Collision pada multi-tab browser."*
* **Kode AI Awal:** Menyimpan filter ke session: `session(['course_search' => $request->q]);` lalu membaca `session('course_search')`.
* **Koreksi Manusia (Dwi & Aan — Commit `74c8488` & `47dd7cb`):** Menggunakan query builder murni `when($request->filled('q'), ...)->withQueryString()` dan menerapkan scoped query per role.
* **Alasan Desain & UX:** Memastikan sifat stateless HTTP GET terpenuhi, tautan hasil pencarian dapat dibagikan (*shareable/bookmarkable*), dan tidak merusak navigasi browser.

---

### Soal 10: Mengapa session()->regenerate() dipanggil setelah login?
* **Branch Penulis:** `liberty-1708` (Agus Liberty Purba — `LoginController.php` Commit `fabf0e7`)
* **Berkas:** `app/Http/Controllers/Auth/LoginController.php` baris 47–53 (di dalam method `login()`)

```php
if (Auth::attempt($credentials, $remember)) {
    $request->session()->regenerate();
    $user = Auth::user();

    return redirect()->intended(route('dashboard'))
        ->with('success', 'Selamat datang kembali, ' . $user->name . ' (' . ucfirst($user->role) . ')!');
}
```

#### Keputusan Desain:
* **Mengapa Ditulis Begitu?**
  Untuk mencegah serangan siber **Session Fixation (Fiksasi Sesi)**.
* **Anatomi Serangan Session Fixation:**
  Penyerang membuat sesi kosong (guest session ID), lalu memancing korban untuk membuka link dengan ID sesi tersebut (misal lewat URL atau cookie injection). Jika aplikasi tidak meregenerasi Session ID saat login, maka setelah korban login, sesi tersebut dinaikkan tingkatnya menjadi sesi terotentikasi **dengan ID yang sama**. Penyerang yang memegang ID tersebut dapat langsung mengambil alih akun korban (*Session Hijacking*).
* **Apa Alternatifnya?**
  * *Alternatif: Membiarkan Session ID tetap sama sebelum dan sesudah login.*
  * *Alasan Ditolak:* Membiarkan pintu pembajakan akun terbuka lebar.
* **Apa Akibatnya Bila Dihapus?**
  Nilai cookie `laravel_session` tidak berubah antara fase guest dan authenticated, menyebabkan aplikasi rentan terhadap serangan pembajakan sesi.

#### 💡 Kaitan dengan Soal 12 (Penggunaan & Koreksi Terhadap AI):
> *"Saya memakai AI untuk membuat alur autentikasi login pengguna, lalu mengaudit dan menambahkan pemanggilan $request->session()->regenerate() tepat setelah Auth::attempt() berhasil karena tanpa regenerasi sesi, aplikasi rentan terhadap serangan Session Fixation di mana penyerang dapat membajak akun korban menggunakan guest session ID yang telah disiapkan sebelumnya."*
* **Kode AI Awal:** `if (Auth::attempt($credentials)) { return redirect('/dashboard'); }` tanpa meregenerasi ID sesi.
* **Koreksi Manusia (Liberty — Commit `fabf0e7`):** Menambahkan `$request->session()->regenerate();` pada `LoginController.php` baris 48.
* **Alasan Desain & Keamanan:** Memaksa browser dan server menerbitkan token sesi baru yang unik segera setelah identitas pengguna terverifikasi, memutus rantai pembajakan sesi (*Session Hijacking*).

---

### Soal 11: Di mana middleware didaftarkan pada Laravel 12? Mengapa berbeda dari tutorial versi lama?
* **Branch Penulis:** `liberty-1708` (`bootstrap/app.php` PR #31)
* **Berkas:** `bootstrap/app.php` baris 18–22

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(...)
    ->create();
```

#### Keputusan Desain:
* **Di Mana Didaftarkan?**
  Didaftarkan di dalam berkas `bootstrap/app.php` pada callback closure `->withMiddleware(function (Middleware $middleware) { ... })` menggunakan method `$middleware->alias([...])`.
* **Mengapa Berbeda dari Tutorial Versi Lama?**
  * Pada Laravel versi lama (v10 ke bawah), middleware didaftarkan di dalam class `app/Http/Kernel.php` (pada array `$middlewareAliases` atau `$routeMiddleware`).
  * Mulai Laravel 11 dan 12, arsitektur framework disederhanakan (*Slim Application Skeleton*). Berkas `app/Http/Kernel.php` **telah dihapus sepenuhnya**.
  * Seluruh konfigurasi *routing*, middleware, dan *exception rendering* disatukan secara terpusat dan deklaratif di dalam `bootstrap/app.php` untuk memangkas *boilerplate code*.

#### 💡 Kaitan dengan Soal 12 (Penggunaan & Koreksi Terhadap AI):
> *"Saya memakai AI untuk mendaftarkan role-based middleware EnsureUserHasRole, lalu mengaudit dan mengubah lokasinya dari app/Http/Kernel.php ke bootstrap/app.php karena pada arsitektur Laravel 12 berkas Kernel sudah dihapus total dan konfigurasi middleware dipusatkan secara deklaratif di bootstrap/app.php."*
* **Kode AI Awal:** Menyarankan menambahkan `'role' => EnsureUserHasRole::class` pada array `$routeMiddleware` di file `app/Http/Kernel.php` (gaya usang Laravel versi 10 ke bawah).
* **Koreksi Manusia (Liberty — Commit `a7d07d7` & PR #31):** Mengonfigurasi callback `->withMiddleware(fn ($middleware) => $middleware->alias(['role' => EnsureUserHasRole::class]))` di `bootstrap/app.php` baris 18–22.
* **Alasan Desain & Arsitektur:** Menyesuaikan dengan standar resmi arsitektur modern Laravel 12 (*Slim Application Skeleton*).

---

### Soal 12: (Wajib, Selalu Ditanyakan) Tunjukkan bagian yang Anda tulis dengan bantuan AI. Apa yang Anda ubah, dan mengapa?

> ⚠️ **Aturan Emas Penilaian Penguji (Bobot Kritis):**
> * Penguji **TIDAK** melarang penggunaan AI, tetapi menguji apakah Anda **memahami dan mengaudit kode** yang dihasilkan.
> * Format jawaban **WAJIB**: **"Saya memakai AI untuk [X], lalu mengaudit dan mengubah [Y] karena [Z]"**.
> * Jawaban seperti *"saya tidak tahu, itu dibuat oleh AI"* atau *"saya terima apa adanya"* otomatis bernilai **0**.

---

#### A. JAWABAN SIAP-BICARA PER ANGGOTA KELOMPOK (BERDASARKAN BRANCH GIT)

##### 1. Branch `liberty-1708` — Agus Liberty Purba (NIM: 10241005)
> *"Saya menggunakan bantuan AI untuk men-generate kerangka skema migrasi database core, alur login multi-role, dan konfigurasi middleware. **Namun saya mengaudit dan mengubah tiga hal mendasar:**
> 1. **Foreign Key Dosen (Soal 1):** AI awalnya menyarankan `lecturer_id` memakai `cascadeOnDelete()`. Saya ubah manual menjadi **`restrictOnDelete()`** (Commit `4ee252b`) karena secara logika akademik, dosen yang masih mengampu mata kuliah aktif tidak boleh dihapus sembarangan agar tidak memicu orphaned records.
> 2. **Pendaftaran Middleware (Soal 11):** AI menyarankan mendaftarkan middleware di `app/Http/Kernel.php` sesuai tutorial lama. Saya mengoreksinya ke **`bootstrap/app.php` baris 18–22** (Commit `a7d07d7`) karena pada Laravel 12 berkas Kernel sudah dihapus total.
> 3. **Session Fixation Guard (Soal 10):** AI lupa meregenerasi sesi saat login; saya menambahkan **`$request->session()->regenerate()`** di `LoginController.php` baris 48 (Commit `fabf0e7`) agar terhindar dari pembajakan sesi."*

##### 2. Branch `tasya-1009` — Anastasya Salsabila Khoirunnisa (NIM: 10241009)
> *"Saya menggunakan bantuan AI untuk membuat template 5 class Authorization Policy (Course, Material, Assignment, Submission, Grade). **Namun saya mengaudit dan mengubah logika internalnya secara drastis:**
> 1. **Pengecekan Kepemilikan Relasional (Soal 5):** AI hanya memberikan pengecekan peran dangkal seperti `return $user->role === 'dosen';`. Saya merombaknya dengan menambahkan **verifikasi relasi kepemilikan mutlak** seperti `$course->lecturer_id === $user->id` dan verifikasi enrollment mahasiswa `$course->students()->where('user_id', $user->id)->exists()` di `CoursePolicy.php` (Commit `96136a2`) untuk menutup celah IDOR.
> 2. **Integrasi Backend vs UI (Soal 6):** AI awalnya hanya menyembunyikan tombol di Blade menggunakan `@can`. Saya memperbaiki arsitekturnya dengan mewajibkan pemanggilan **`Gate::authorize()`** di controller (Commit `f3a5561`) agar sistem kebal dari serangan bypass URL langsung dan cURL."*

##### 3. Branch `Aliya-Labibah` — Aliya Labibah (NIM: 10241007)
> *"Saya menggunakan bantuan AI untuk men-generate kerangka Controller CRUD Materi & Tugas serta API Endpoints V1. **Namun saya menemukan celah keamanan dan logika bisnis yang fatal pada kode buatan AI, sehingga saya mengubah tiga hal:**
> 1. **Pencegahan Mass Assignment (Soal 8):** AI awalnya menyarankan menggunakan `$request->all()` langsung ke model (`Assignment::create($request->all())`). Saya ubah manual menjadi **`$request->validated()`** menggunakan Form Request (`StoreAssignmentRequest` & `StoreMaterialRequest` di Commit `bd1f682`) agar terlindung dari injeksi atribut liar.
> 2. **Penegakan Otorisasi Server-Side (Soal 6):** AI hanya mengandalkan logika kepemilikan manual yang rapuh; saya menggantinya dengan penegakan otorisasi backend **`Gate::authorize('update', $assignment)`** dan pengecekan **`abort_unless`** (Commit `3062404`) agar kebal terhadap eksploitasi IDOR.
> 3. **Validasi Bisnis API (Soal 7):** Pada API V1 pengumpulan tugas (`AssignmentSubmissionController.php` di Commit `d4a37de`), saya menambahkan validasi batas waktu (`due_at`) dan pencegahan duplikasi submission yang awalnya diabaikan oleh AI."*

##### 4. Branch `aan-mardiah` — Aan Mardiah (NIM: 10241001)
> *"Saya menggunakan bantuan AI untuk membuat contoh query builder pada method `index()` controller. **Namun saya mengaudit dan mengubahnya secara menyeluruh:**
> 1. **Query Scoping (Soal 9):** Kode bawaan AI menggunakan `Course::paginate()` secara polos tanpa filter kepesertaan. Saya ubah menjadi **Scoped Query** (`$user->courses()->paginate()` untuk mahasiswa dan `$user->taughtCourses()->paginate()` untuk dosen di Commit `47dd7cb`) karena jika dibiarkan polos, mahasiswa dapat melihat seluruh mata kuliah kampus yang tidak diikutinya (*List-Level Authorization Leakage*).
> 2. **Pencegahan N+1:** Saya menambahkan **Eager Loading** (`with('lecturer')`) pada seluruh kueri relasi untuk membasmi pemborosan query database.
> 3. **Audit IDOR:** Saya menyusun tabel audit 23 titik rawan IDOR di `docs/keamanan.md` untuk memetakan penutupan celah keamanan di Milestone 2."*

##### 5. Branch `Dwi_11` — Annisa Dwi Lestari Sonny (NIM: 10241011)
> *"Saya menggunakan bantuan AI untuk merapikan model Eloquent dan membuat kerangka skrip pengujian bash cURL. **Namun saya mengaudit dan mengubah dua hal mendasar:**
> 1. **Eksklusi 'role' dari $fillable (Soal 3 & 4):** AI awalnya menyarankan memasukkan atribut `'role'` ke dalam array `$fillable` di model `User.php` demi kepraktisan. Saya menolak saran tersebut dan **mengecualikan `'role'` dari `$fillable`** (Commit `4ac982a`) agar nilai role hanya dapat diisi secara eksplisit oleh admin dan kebal terhadap Privilege Escalation.
> 2. **Pengujian Negatif Otomatis (Soal 6 & 7):** Pada skrip pengujian `test-authz.sh` (Commit `8a52a66`), AI hanya membuat skenario positif (*Happy Path* 200 OK). Saya mengubah dan memperluasnya dengan menambahkan **seluruh skenario pengujian negatif (*Negative Test Cases*)**, membuktikan bahwa serangan IDOR ditolak tepat dengan **HTTP 403 Forbidden**, dan akses tanpa token ditolak dengan **401 Unauthorized**."*

---

#### B. TABEL MATRIKS INTEGRASI LENGKAP: BANTUAN AI VS KOREKSI TIM (SOAL 1–11)

Tabel berikut memetakan korelasi antara **Soal 1 s.d. 11** dengan perlakuan terhadap kode AI:

| No Soal | Topik Pembahasan | Penulis & Branch | Kode Awal Bawaan AI | Celah / Kelemahan Kode AI | Koreksi Manual Tim Kami | Alasan & Bukti Git Log |
|:---:|---|---|---|---|---|---|
| **1** | Migrasi & Constraint DB | **Agus Liberty** (`liberty-1708`) | `$table->foreignId('lecturer_id')->cascadeOnDelete();` | Jika akun dosen dihapus, seluruh mata kuliah & nilainya lenyap permanen. | Diubah ke `restrictOnDelete()` & ditambah `unique('code')`. | Menjaga integritas referensial akademik (Commit `4ee252b`). |
| **2** | Composite Unique `course_user` | **Agus Liberty** & **Dwi** (`liberty-1708`, `Dwi_11`) | Hanya validasi PHP manual di controller: `if ($user->courses()->exists())` | Rentan **Race Condition** saat double-click bersamaan. | Ditambah `$table->unique(['course_id', 'user_id'])`. | Mengunci proteksi di level ACID database engine (Commit `4ee252b`). |
| **3** | Mass Assignment Guard | **Annisa Dwi** (`Dwi_11`) | Model menggunakan `protected $guarded = [];` | Membuka proteksi total (*black-box approach*) pada model. | Diganti whitelist ketat `protected $fillable = [...]`. | Mencegah parameter berbahaya masuk ke model (Commit `4ac982a`). |
| **4** | Proteksi Atribut `role` | **Annisa Dwi** & **Liberty** (`Dwi_11`, `liberty-1708`) | Menaruh `'role'` di dalam `$fillable` model `User.php`. | Celah **Privilege Escalation** (user menyuntikkan `role=admin`). | `'role'` dihapus dari `$fillable`, diisi eksplisit di controller. | Prinsip *Defense in Depth* keamanan otorisasi (Commit `4ac982a` & `a7d07d7`). |
| **5** | Authorization Policy | **Anastasya** (`tasya-1009`) | Cek role dangkal: `return $user->role === 'dosen';` | Dosen 1 bisa mengakses & mengubah mata kuliah Dosen 2 (IDOR). | Cek relasi: `$course->lecturer_id === $user->id`. | Menegakkan isolasi data multitenan antar-pengguna (Commit `96136a2`). |
| **6** | Otorisasi Backend vs UI | **Aliya Labibah** & **Tasya** (`Aliya-Labibah`, `tasya-1009`) | Hanya sembunyikan tombol di Blade: `@can(...) ... @endcan`. | Celah bypass: penyerang bisa menembak API/URL langsung via cURL. | Wajib panggil `Gate::authorize()` di method controller. | Menegakkan keamanan server-side sejati (Commit `bd1f682` & `f3a5561`). |
| **7** | HTTP 401 vs 403 API | **Aliya**, **Liberty**, & **Dwi** (`Aliya-Labibah`, `liberty-1708`, `Dwi_11`) | Menyamakan semua error menjadi 401 atau mengembalikan HTML generic. | Memicu *infinite login loop* pada aplikasi frontend/mobile SPA. | Custom exception handler di `bootstrap/app.php` L25–36. | Membedakan unauthenticated (401) vs unauthorized (403) (Commit `70da940`, `8a52a66`). |
| **8** | Sanitasi Input Form | **Aliya Labibah** & **Liberty** (`Aliya-Labibah`, `liberty-1708`) | Pakai `$request->all()` langsung ke method `Model::create()`. | Seluruh parameter liar dari payload lolos ke query database. | Menggunakan Form Request & method `$request->validated()`. | Menjamin hanya data tervalidasi yang diproses (Commit `bd1f682` & `88de89f`). |
| **9** | Filter Query String vs Session | **Annisa Dwi** & **Aan** (`Dwi_11`, `aan-mardiah`) | Simpan filter pencarian ke session: `session(['q' => ...])`. | Bug **Sticky Filter** & **Session Collision** pada multi-tab browser. | Pakai URL Query String murni dengan `withQueryString()`. | Menjaga sifat stateless HTTP & URL dapat di-bookmark (Commit `74c8488` & `47dd7cb`). |
| **10** | Session Fixation Guard | **Agus Liberty** (`liberty-1708`) | `Auth::attempt()` langsung redirect tanpa regenerasi sesi. | Rentan **Session Fixation** (penyerang membajak akun via guest session ID). | Tambah `$request->session()->regenerate()` tepat saat login sukses. | Mengganti token sesi seketika saat privilege naik (Commit `fabf0e7` L48). |
| **11** | Middleware Laravel 12 | **Agus Liberty** (`liberty-1708`) | Mendaftarkan middleware di file `app/Http/Kernel.php`. | Berkas `Kernel.php` sudah dihapus di arsitektur Laravel 12. | Didaftarkan deklaratif di `bootstrap/app.php` via `$middleware->alias()`. | Menyesuaikan arsitektur resmi Laravel 12 Slim Skeleton (Commit `a7d07d7` L18–22). |

---

#### C. STRATEGI MENJAWAB SAAT INTERVIEW RESPONSI

Jika penguji bertanya secara acak tentang salah satu fitur:
1. **Langkah 1:** Jelaskan **keputusan desain** terlebih dahulu (*mengapa kodenya ditulis seperti itu*).
2. **Langkah 2:** Sebutkan **apa alternatifnya** dan **mengapa alternatif itu ditolak**.
3. **Langkah 3:** Sambungkan langsung ke **aspek AI**:
   > *"Awalnya saat merancang fitur ini dengan AI, saran awal yang diberikan adalah [alternatif yang ditolak / kode rentan]. Namun setelah kami audit, kami menemukan kelemahan [sebutkan celahnya], sehingga kami mengubahnya menjadi [kode perbaikan] di Commit [sebutkan commit-nya]."*
4. **Langkah 4:** Tunjukkan bukti baris kodenya di editor atau demonstrasikan dengan terminal `curl.exe` / `tinker`. Dengan pola ini, Anda mendemonstrasikan pemahaman level *software engineer*, bukan sekadar *copy-paste*.
