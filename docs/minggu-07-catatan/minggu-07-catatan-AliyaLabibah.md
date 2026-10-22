# Nama: Aliya Labibah
## NIM: 10241007 

## READ — Bedah starter kit (30 menit)

### 1. Berkas mana yang menangani POST login? Method apa?

* **Berkas Controller:** `app/Http/Controllers/Auth/LoginController.php`
* **Method:** `public function login(Request $request)` (baris 26–60)
* **Pendaftaran Route:** Didaftarkan pada berkas `routes/web.php` baris 82:
  ```php
  Route::post('/login', [LoginController::class, 'login']);
  ```
* **Penjelasan Alur:**
  Method `login()` menerima data kiriman formulir, memvalidasi input `login` (yang mendukung format Email atau NIM/NIP) dan `password`, menyusun kredensial, lalu memproses autentikasi ke basis data sebelum mengarahkan pengguna ke dashboard sesuai perannya.

---

### 2. Di baris mana `Auth::attempt()` atau setaranya dipanggil?

* **Berkas:** `app/Http/Controllers/Auth/LoginController.php`
* **Baris Pemanggilan:** **Baris 47**
  ```php
  if (Auth::attempt($credentials, $remember)) {
      $request->session()->regenerate();
      $user = Auth::user();

      return redirect()->intended(route('dashboard'))
          ->with('success', 'Selamat datang kembali, ' . $user->name . ' (' . ucfirst($user->role) . ')!');
  }
  ```
* **Penjelasan:**
  Fasade `Auth::attempt()` menerima array `$credentials` (berisi field `email` atau `nim_nip` beserta `password` teks polos) dan parameter boolean `$remember`. Di balik layar, Laravel otomatis mencari record user yang cocok, mencocokkan hash kata sandi menggunakan Bcrypt, dan jika valid, sistem akan langsung menginisialisasi sesi login pengguna.

---

### 3. Temukan `session()->regenerate()`. Kenapa ia ada di situ?

* **Berkas & Baris:** `app/Http/Controllers/Auth/LoginController.php` **baris 48**:
  ```php
  $request->session()->regenerate();
  ```
* **Alasan Keberadaannya (Pencegahan Session Fixation):**
  Method `session()->regenerate()` wajib dipanggil tepat setelah autentikasi berhasil untuk mencegah serangan siber **Session Fixation (Fiksasi Sesi)**.
  * **Anatomi Serangan:** Tanpa regenerasi ID sesi, penyerang bisa menjebak korban dengan memberikan Session ID tertentu (misalnya lewat URL atau injeksi cookie sebelum korban login). Jika korban kemudian login menggunakan Session ID yang sama, penyerang yang sudah memegang Session ID tersebut akan otomatis ikut masuk (*terbawa masuk*) ke dalam sesi korban yang terotentikasi.
  * **Cara Kerja Proteksi:** `session()->regenerate()` mengganti ID sesi lama (yang dipakai saat berstatus tamu/guest) dengan ID sesi yang benar-benar baru, acak, dan unik. Dengan demikian, Session ID lama yang diketahui penyerang menjadi hangus dan tidak dapat disalahgunakan untuk membajak akun.

---

### 4. Di mana kata sandi di-hash? Cari cast `hashed` di model User.

* **Berkas:** `app/Models/User.php`
* **Letak Baris:** **Baris 46–53** pada method `casts()`:
  ```php
  protected function casts(): array
  {
      return [
          'email_verified_at' => 'datetime',
          'password' => 'hashed',
          'deleted_at' => 'datetime',
      ];
  }
  ```
* **Penjelasan Mekanisme:**
  Pada Laravel 11 dan 12, casting `'password' => 'hashed'` menggantikan kebutuhan pemanggilan manual `Hash::make()`. Setiap kali atribut `password` diisi atau diperbarui pada instance model `User` (misalnya saat registrasi, seeder, atau ubah password), Laravel Eloquent secara otomatis mengenkripsi teks sandi asli menjadi format hash aman (Bcrypt) sebelum disimpan ke basis data.

---

### 5. Buka DevTools → Cookies sebelum dan sesudah login. Bandingkan nilai cookie session.

Pengamatan dilakukan melalui browser pada tab **DevTools (F12) → Application/Storage → Cookies → `http://127.0.0.1:8000`**:

| Kondisi | Nama Cookie | Karakteristik Nilai (*Value*) | Keterangan Status |
|---|---|---|---|
| **Sebelum Login (Guest)** | `kampuslms_session` / `laravel_session` | String terenkripsi awal, contoh: `eyJpdiI6Inl...S1_GUEST_ID` | Berisi data sesi anonim (hanya membawa token CSRF untuk keamanan form). |
| **Sesudah Login (Authenticated)** | `kampuslms_session` / `laravel_session` | String terenkripsi baru yang **berubah total**, contoh: `eyJpdiI6Imt...S2_AUTH_ID` | Berisi identitas user yang sah (`login_web_xxx`) setelah melewati proses `session()->regenerate()`. |

* **Kesimpulan Analisis:**
  Perubahan nilai cookie secara total membuktikan bahwa Laravel berhasil membuang identitas sesi guest dan menerbitkan identitas sesi terotentikasi yang baru, sesuai dengan prinsip pencegahan *session fixation*.

---

### 6. Logout, lalu tekan tombol *back*. Apa yang terjadi? Kenapa?

* **Gejala yang Terlihat di Layar:**
  Setelah menekan tombol Logout (diarahkan ke `/login`), kemudian menekan tombol **Back** di peramban (browser), halaman dashboard atau halaman terlindungi sebelumnya **tampak muncul kembali di layar**. Namun, begitu pengguna mencoba mengklik menu apa pun atau melakukan refresh (F5), peramban **langsung melempar pengguna kembali ke halaman `/login`**.
* **Penyebab Teknis (*Root Cause*):**
  1. **Di Sisi Peramban (BFcache - Back-Forward Cache):**
     Saat tombol *Back* ditekan, browser tidak mengirimkan permintaan HTTP baru ke server Laravel. Browser hanya menampilkan cuplikan rekaman visual (*snapshot render*) yang masih tersimpan di memori cache lokal peramban.
  2. **Di Sisi Server (Sesi Sebenarnya Sudah Hangus):**
     Pada method `logout()` di `LoginController.php` (baris 67–70), Laravel telah mengeksekusi:
     ```php
     Auth::logout();
     $request->session()->invalidate();
     $request->session()->regenerateToken();
     ```
     Artinya, seluruh data sesi di server telah dihancurkan (*invalidated*). Begitu terjadi interaksi aktif (klik tautan/F5) yang memicu HTTP Request nyata, middleware `auth` langsung mendeteksi bahwa pengguna sudah tidak memiliki sesi aktif dan otomatis menolak akses dengan me-redirect ke rute login.

## BREAK — Delapan kerusakan (50 menit)

| # | Yang dicoba | Yang harus diamati / Prediksi | Hasil Sebenarnya & Analisis Teknis |
|---|---|---|---|
| 1 | Hapus `session()->regenerate()` dari login | Session fixation terbuka | **Nilai cookie `laravel_session` tidak berubah sebelum dan sesudah login.** Ketika baris `$request->session()->regenerate()` di [app/Http/Controllers/Auth/LoginController.php](file:///d:/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/Auth/LoginController.php#L48) dimatikan, peramban mempertahankan ID sesi yang sama persis antara fase *guest* dan fase *authenticated*. Hal ini memicu kerentanan **Session Fixation**: penyerang dapat menjebak korban menggunakan ID sesi yang sudah diketahuinya, lalu setelah korban login, penyerang tetap bisa memakai sesi tersebut untuk membajak akun. Saat `session()->regenerate()` aktif, Laravel menerbitkan ID sesi baru yang acak dan memutus rantai serangan. |
| 2 | Hapus `Gate::authorize()` dari update, tapi biarkan `@can` di Blade | Tombol hilang, URL tetap jalan | **Tombol "Edit" hilang di tampilan antarmuka, tetapi form edit tetap bisa diakses dan disimpan lewat URL langsung.** Pada penugasan dosen, tombol edit di Blade [resources/views/assignments/show.blade.php](file:///d:/kampuslms-kelompok-01/kampuslms-kelompok-01/resources/views/assignments/show.blade.php#L37) disembunyikan menggunakan `@can('update', $assignment)`. Ketika baris `Gate::authorize('update', $assignment)` di [app/Http/Controllers/Dosen/AssignmentController.php](file:///d:/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/Dosen/AssignmentController.php#L129) dimatikan, Dosen A yang mengetik langsung URL `/dosen/assignments/{id_dosen_b}/edit` di browser berhasil membuka form dan mengubah tugas milik Dosen B. Ini membuktikan bahwa menyembunyikan tombol di UI (*Security through Obscurity*) sama sekali bukan keamanan jika tidak divalidasi oleh `Gate::authorize()` di backend. |
| 3 | Sebagai dosen A, kirim PUT ke tugas / mata kuliah dosen B lewat curl | Uji Policy Anda sungguhan | **Muncul respon status HTTP 403 Forbidden dengan pesan *"Anda tidak memiliki akses ke sumber daya ini."*** Dosen A (Dr. Budi Santoso) berhasil login dan mengantongi token autentikasi yang sah. Namun, ketika ia mencoba mengirim perintah HTTP `PUT` lewat curl untuk mengubah tugas ID 7 milik Dosen B (Prof. Siti Rahayu), server langsung menolak mentah-mentah permintaannya. Hal ini terjadi karena [AssignmentPolicy.php](file:///d:/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Policies/AssignmentPolicy.php#L79-L81) memeriksa kepemilikan secara ketat di backend (`lecturer_id === $user->id`). Percobaan ini membuktikan bahwa meskipun seseorang mencoba "jalan pintas" menembak server langsung lewat terminal/curl tanpa melalui browser, data tetap aman terlindungi karena Policy di sisi server benar-benar memvalidasi hak kepemilikan data. |
| 4 | Sebagai mahasiswa, buka submission mahasiswa lain (IDOR minggu 5) | Sekarang harus 403 | **Server mengembalikan respon 403 Forbidden (Akses Ditolak).** Saat login sebagai mahasiswa (Andi Pratama) lalu mencoba mengakses detail pengumpulan tugas milik mahasiswa lain di `/mahasiswa/submissions/2` (atau via endpoint API `/api/v1/submissions/2`), server langsung memblokir akses. Celah IDOR (*Insecure Direct Object Reference*) yang sempat ada di minggu ke-5 kini berhasil ditutup berkat proteksi [SubmissionPolicy.php](file:///d:/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Policies/SubmissionPolicy.php#L42-L44) yang memverifikasi kepemilikan mutlak (`$submission->user_id === $user->id`). |
| 5 | Ubah index menjadi `Course::paginate()` polos, login sebagai mahasiswa | Kebocoran tingkat daftar | **Seluruh mata kuliah di kampus bocor dan tampil di daftar kelas mahasiswa.** Ketika query relasi `$request->user()->courses()` di [app/Http/Controllers/Mahasiswa/CourseController.php](file:///d:/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/Mahasiswa/CourseController.php#L31) diubah menjadi `Course::paginate(15)` polos tanpa filter kepesertaan, mahasiswa yang login ke halaman `/mahasiswa/courses` dapat melihat seluruh mata kuliah prodi dan semester lain yang tidak pernah ia ambil. Eksperimen ini mendemonstrasikan bahaya *List-Level Authorization Leakage* (kebocoran data pada daftar agregat jika otorisasi hanya difokuskan pada halaman detail). |
| 6 | Kirim `role=admin` pada form edit profil | Mass assignment kembali diuji | **Nilai atribut `role` di database tetap tidak berubah (eskalasi peran gagal).** Saat pengujian manipulasi data profil dilakukan dengan menyuntikkan parameter `role=admin` ke dalam payload HTTP request, Eloquent secara otomatis mengabaikan kolom `role`. Hal ini karena atribut `role` sengaja tidak didaftarkan ke dalam array `$fillable` di model [app/Models/User.php](file:///d:/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Models/User.php#L24-L29), sehingga sistem kebal terhadap serangan eskalasi hak akses (*Privilege Escalation via Mass Assignment*). |
| 7 | Ganti cast `hashed` menjadi tidak ada, buat user baru, lihat kolom password di database | Kata sandi polos di database | **Kata sandi tersimpan sebagai teks polos (*plain text*) di tabel database SQLite.** Ketika konfigurasi `'password' => 'hashed'` di `casts()` [app/Models/User.php](file:///d:/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Models/User.php#L50) dinonaktifkan sementara, user baru yang dibuat menyimpan string kata sandi asli (misal `"rahasia123"`) tanpa dienkripsi menjadi hash Bcrypt (`$2y$12$...`). Jika database bocor atau terjadi SQL Injection, seluruh kredensial akun mahasiswa dan dosen langsung terbongkar seketika. |
| 8 | Login, salin cookie session, tempel di browser lain | Kenapa cookie harus HttpOnly & Secure | **Browser kedua langsung login otomatis sebagai pengguna tersebut tanpa memasukkan password (*Session Hijacking*).** Nilai cookie `laravel_session` dari browser utama disalin dan ditempelkan ke peramban kedua (misal mode *Incognito*). Begitu halaman di-refresh, browser kedua langsung mengambil alih sesi akun secara penuh. Eksperimen ini membuktikan bahwa session cookie adalah kunci identitas di server. Oleh karena itu, cookie wajib dilindungi flag **`HttpOnly`** (mencegah pencurian cookie lewat skrip JavaScript / XSS) dan **`Secure`** (memastikan cookie hanya dikirimkan lewat protokol terenkripsi HTTPS).