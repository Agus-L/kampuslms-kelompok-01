## Nama: Aan Mardiah
## NIM:10241001

### READ
1. Berkas mana yang menangani POST login? Method apa?  
Jawab: Berkas Controller`app/Http/Controllers/Auth/LoginController.php`didefinisikan di `routes/web.php`. Method yang mengeksekusi logika ini adalah  `public function login(Request $request)` di dalam class `LoginController`.

2. Di baris mana `Auth::attempt()` atau setaranya dipanggil?
Jawab:Dipanggil pada baris 47 di `app/Http/Controllers/Auth/LoginController.php`:
     ```php
     if (Auth::attempt($credentials, $remember)) {
     ```
    `Auth::attempt()` bertugas mengambil array `$credentials` (berisi field email/nim_nip dan plaintext password), mencari user terkait di database, lalu secara otomatis mencocokkan password menggunakan algoritma hashing (Bcrypt/Argon2). Jika cocok dan valid, Laravel membuat sesi login untuk user tersebut.

3. Temukan `session()->regenerate()`. Kenapa ia ada di situ?  
Jawab: Berkas app/Http/Controllers/Auth/LoginController.php pada baris 48. Untuk mencegah kerentanan keamanan Session Fixation Attack (Fiksasi Sesi). Saat user masih berstatus tamu (guest), browser telah memegang ID sesi sementara. Dengan memanggil `session()->regenerate()`, Laravel akan membuang ID sesi lama dan menerbitkan ID sesi baru yang unik tepat setelah kredensial berhasil diverifikasi, sehingga penyerang yang memegang ID sesi sebelum login tidak dapat membajak akun pengguna.

4. Di mana kata sandi di-hash? Cari cast `hashed` di model User.  
Jawab: Berkas `app/Models/User.php` pada method `casts()` (baris 46–53):
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
    Cast `'password' => 'hashed'` secara otomatis melakukan hashing kata sandi (menggunakan `Hash::make()` dengan algoritma Bcrypt/Argon2) setiap kali atribut password ditetapkan pada model `User`, sehingga kata sandi tidak pernah tersimpan dalam bentuk *plaintext*.

5. Buka DevTools → Cookies sebelum dan sesudah login. Bandingkan nilai cookie session.  
Jawab: Sebelum Login Cookie session (`kampuslms_session`) berisi string hash terenkripsi yang merepresentasikan sesi tamu (*guest/anonymous*). Sesudah Login Nilai string cookie `kampuslms_session` berubah total menjadi nilai token baru. Hal ini merupakan dampak langsung dari pemanggilan `$request->session()->regenerate()` yang mengaitkan ID sesi baru dengan data identitas pengguna yang sudah terotentikasi di server.

6. Logout, lalu tekan tombol back. Apa yang terjadi? Kenapa?
Jawab: Halaman sebelumnya (dashboard) mungkin masih terlihat sekilas di layar browser. Namun, ketika pengguna melakukan refresh atau mengklik tautan/menu di dalamnya, browser langsung mengalihkan (redirect) kembali ke halaman `/login`. Alasannya yang pertama dari sisi browser, browser menggunakan fitur Back-Forward Cache lokal untuk menampilkan snapshot halaman yang tersimpan di memori cache tanpa meminta ulang ke server. Dari sisi server, sesi pengguna sebenarnya sudah dihancurkan sepenuhnya saat logout melalui method `Auth::logout()` dan `$request->session()->invalidate()`. Begitu ada request HTTP baru, middleware `auth` mendeteksi bahwa user sudah tidak memiliki sesi aktif dan langsung menolak akses ke halaman tersebut.

### BREAK

| # | Yang dicoba | Prediksi | Hasil Sebenarnya |
| --- | --- | --- | --- | 
### BREAK

| # | Yang dicoba | Prediksi | Hasil Sebenarnya |
| --- | --- | --- | --- | 
| 1 | Hapus `session()->regenerate()` dari login | Nilai Session ID (cookie session) tidak akan berubah antara sebelum dan sesudah login. | Nilai cookie session (`kampuslms_session`) tetap sama persis antara status guest dan authenticated. Aplikasi rentan terhadap serangan Session Fixation, di mana penyerang yang mengetahui ID sesi sebelum korban login dapat langsung membajak akun korban setelah login tanpa perlu mengetahui password. |
| 2 | Hapus `Gate::authorize()` dari `update`, tapi biarkan `@can` di Blade | Saat login sebagai dosen yang bukan pemilik (Dosen B), tombol "Edit Tugas" di antarmuka tidak akan muncul. Namun, jika URL edit/update diakses langsung, form tetap terbuka dan data berhasil diperbarui karena ketiadaan otorisasi backend. | Terbukti tombol "Edit Tugas" di antarmuka (`assignments/show.blade.php`) disembunyikan oleh directive `@can('update', $assignment)`. Namun ketika Dosen B mengakses langsung URL `/dosen/assignments/1/edit` di address bar browser, halaman form edit tetap terbuka normal tanpa dicegat status 403 Forbidden, dan perubahan data berhasil disimpan. Ini membuktikan bahwa proteksi visual di Blade hanyalah kenyamanan antarmuka (*UI convenience*), bukan keamanan yang sesungguhnya. Otorisasi mutlak wajib ditegakkan di sisi controller/backend via `Gate::authorize()`. |
| 3 | Sebagai dosen A, kirim PUT ke mata kuliah dosen B lewat `curl` | Permintaan update dari Dosen A (Dosen 2) akan ditolak oleh sistem dengan status HTTP 403 Forbidden karena ia bukan pemilik/pengampu dari data tersebut. | Terbukti saat login sebagai Dosen 2 (`dosen2@kampuslms.test`) dan mengirimkan request `PUT` via `curl` ke tugas milik Dosen 1 (`/api/v1/assignments/1`) menggunakan Bearer Token, server langsung merespons dengan status HTTP/1.1 403 Forbidden (`{"message":"Anda tidak memiliki akses ke sumber daya ini."}`). Aturan Policy kepemilikan di backend berhasil mencegat request sebelum menyentuh database, membuktikan Policy efektif menangkal manipulasi antar-dosen (IDOR). |
| 4 | Sebagai mahasiswa, buka submission mahasiswa lain (IDOR minggu 5) | Sekarang harus 403 | *Server mengembalikan respon 403 Forbidden (Akses Ditolak).* Saat login sebagai mahasiswa (Andi Pratama) lalu mencoba mengakses detail pengumpulan tugas milik mahasiswa lain di /mahasiswa/submissions/2 (atau via endpoint API /api/v1/submissions/2), server langsung memblokir akses. Celah IDOR (Insecure Direct Object Reference) yang sempat ada di minggu ke-5 kini berhasil ditutup berkat proteksi [SubmissionPolicy.php](file:///d:/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Policies/SubmissionPolicy.php#L42-L44) yang memverifikasi kepemilikan mutlak ($submission->user_id === $user->id). |
| 5 | Ubah index menjadi Course::paginate() polos, login sebagai mahasiswa | Kebocoran tingkat daftar | *Seluruh mata kuliah di kampus bocor dan tampil di daftar kelas mahasiswa.* Ketika query relasi $request->user()->courses() di [app/Http/Controllers/Mahasiswa/CourseController.php](file:///d:/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/Mahasiswa/CourseController.php#L31) diubah menjadi Course::paginate(15) polos tanpa filter kepesertaan, mahasiswa yang login ke halaman /mahasiswa/courses dapat melihat seluruh mata kuliah prodi dan semester lain yang tidak pernah ia ambil. Eksperimen ini mendemonstrasikan bahaya List-Level Authorization Leakage (kebocoran data pada daftar agregat jika otorisasi hanya difokuskan pada halaman detail). |
| 6 | Kirim role=admin pada form edit profil | Mass assignment kembali diuji | *Nilai atribut role di database tetap tidak berubah (eskalasi peran gagal).* Saat pengujian manipulasi data profil dilakukan dengan menyuntikkan parameter role=admin ke dalam payload HTTP request, Eloquent secara otomatis mengabaikan kolom role. Hal ini karena atribut role sengaja tidak didaftarkan ke dalam array $fillable di model [app/Models/User.php](file:///d:/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Models/User.php#L24-L29), sehingga sistem kebal terhadap serangan eskalasi hak akses (Privilege Escalation via Mass Assignment). |
| 7 | Ganti cast hashed menjadi tidak ada, buat user baru, lihat kolom password di database | Kata sandi polos di database | *Kata sandi tersimpan sebagai teks polos (*plain text) di tabel database SQLite.** Ketika konfigurasi 'password' => 'hashed' di casts() [app/Models/User.php](file:///d:/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Models/User.php#L50) dinonaktifkan sementara, user baru yang dibuat menyimpan string kata sandi asli (misal "rahasia123") tanpa dienkripsi menjadi hash Bcrypt ($2y$12$...). Jika database bocor atau terjadi SQL Injection, seluruh kredensial akun mahasiswa dan dosen langsung terbongkar seketika. |
| 8 | Login, salin cookie session, tempel di browser lain | Kenapa cookie harus HttpOnly & Secure | *Browser kedua langsung login otomatis sebagai pengguna tersebut tanpa memasukkan password (*Session Hijacking).* Nilai cookie laravel_session dari browser utama disalin dan ditempelkan ke peramban kedua (misal mode *Incognito). Begitu halaman di-refresh, browser kedua langsung mengambil alih sesi akun secara penuh. Eksperimen ini membuktikan bahwa session cookie adalah kunci identitas di server. Oleh karena itu, cookie wajib dilindungi flag *HttpOnly* (mencegah pencurian cookie lewat skrip JavaScript / XSS) dan *Secure* (memastikan cookie hanya dikirimkan lewat protokol terenkripsi HTTPS). |