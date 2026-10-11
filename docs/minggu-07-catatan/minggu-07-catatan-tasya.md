# Catatan Minggu 7

## Identitas Mahasiswa

Nama: Anastasya Salsabila Khoirunnisa  
NIM: 10241009  

---

## READ

### 1. BAGIAN READ — Bedah Starter Kit & Mekanisme Autentikasi

Berdasarkan penelusuran arsitektur Laravel 12 pada proyek KampusLMS, berikut analisis mendalam terhadap 6 pertanyaan telaah mekanisme autentikasi:

---

#### 1. Controller & Method Penangan Login
- **Berkas Controller:**  
  [`kampuslms-kelompok-01/app/Http/Controllers/Auth/LoginController.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/Auth/LoginController.php#L26-L60)
- **Method Penangan:**  
  `public function login(Request $request)` (baris 26–60).
- **Pendaftaran Route:**  
  Didaftarkan pada [`routes/web.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/routes/web.php#L82) di dalam grup middleware `guest`:
  ```php
  Route::post('/login', [LoginController::class, 'login']);
  ```
- **Tanggung Jawab:**  
  Method ini memvalidasi kelengkapan form masukan (`login` dan `password`), menentukan secara fleksibel apakah input berupa Email atau NIM/NIP, menyusun array kredensial, melakukan percobaan autentikasi via `Auth::attempt()`, meregenerasi session, dan mengarahkan pengguna ke dashboard perannya masing-masing.

---

#### 2. Verifikasi Kredensial
- **Lokasi Kode:**  
  [`app/Http/Controllers/Auth/LoginController.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/Auth/LoginController.php#L47) baris 47:
  ```php
  if (Auth::attempt($credentials, $remember)) {
  ```
- **Parameter yang Dikirimkan:**
  1. **`$credentials` (Array Asosiatif):**  
     Berisi pasangan kunci-nilai identitas pengguna dan kata sandi:
     ```php
     $loginInput = trim($validated['login']);
     $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'nim_nip';

     $credentials = [
         $field     => $loginInput,
         'password' => $validated['password'],
     ];
     ```
     - Kunci identitas bersifat dinamis: bertindak sebagai `email` jika format string berupa email valid, atau sebagai `nim_nip` jika formatnya angka/kode identitas (NIM mahasiswa atau NIP dosen/admin).
     - Kunci `password`: kata sandi mentah (*plaintext*) yang diinput pengguna. Di balik layar, Laravel Query Builder mencari record pengguna berdasarkan `email`/`nim_nip`, lalu membandingkan string kata sandi menggunakan `Hash::check()` terhadap string hash Bcrypt di basis data.
  2. **`$remember` (Boolean):**  
     Diambil dari checkbox *Ingat sesi saya* melalui `$request->boolean('remember')`. Jika bernilai `true`, Laravel otomatis menerbitkan cookie `remember_web_...` terenkripsi dengan masa berlaku panjang (5 tahun secara default), sehingga sesi login tetap dikenali meskipun browser ditutup.

---

#### 3. Session Fixation Prevention
- **Lokasi Kode:**  
  [`app/Http/Controllers/Auth/LoginController.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/Auth/LoginController.php#L48) baris 48:
  ```php
  $request->session()->regenerate();
  ```
- **Posisi Eksekusi:**  
  Dipanggil tepat setelah blok `Auth::attempt($credentials, $remember)` bernilai `true` (kredensial terverifikasi valid), dan sebelum instruksi pengalihan halaman (`redirect()->intended(...)`).
- **Penjelasan Konseptual Keamanan:**  
  *Session Fixation* adalah teknik serangan di mana penyerang menetapkan atau menyuntikkan ID session tertentu yang telah diketahuinya ke peramban korban sebelum korban melakukan proses autentikasi (misalnya melalui manipulasi query string URL, teknik *cross-subdomain cookie injection*, atau celah XSS).  
  Ketika korban mengakses situs dan berhasil login menggunakan kredensial valid miliknya:
  - **Jika ID session TIDAK diregenerasi:** ID session lama yang sudah diketahui penyerang otomatis dipromosikan statusnya menjadi sesi terautentikasi (*authenticated session*). Penyerang yang memegang ID session tersebut dapat langsung mengakses seluruh akun korban tanpa pernah mengetahui kata sandi korban.
  - **Dengan `session()->regenerate()`:** Laravel secara instan menghancurkan ID session lama dan menerbitkan ID session acak yang baru dengan entropi kriptografis tinggi. Hubungan antara ID session tamu awal milik penyerang dengan sesi terautentikasi korban terputus seketika, menggagalkan serangan *Session Fixation*.

---

#### 4. Hashing Kata Sandi
- **Lokasi Kode:**  
  [`kampuslms-kelompok-01/app/Models/User.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Models/User.php#L46-L53) baris 46–53:
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
- **Implementasi Standar Laravel 12:**  
  Pada Laravel 12, casting atribut model tidak lagi menggunakan properti lama `$casts = []`, melainkan melalui method resmi `casts(): array`.  
  Penyertaan cast `'password' => 'hashed'` memberikan proteksi mutlak di tingkat Eloquent ORM:
  1. Setiap kali atribut `password` diset pada model (baik melalui `$user->password = 'kataSandiBaru'` maupun operasi `User::create(['password' => ...])`), Eloquent otomatis mengeksekusi `Hash::make()` menggunakan algoritma Bcrypt (cost factor default 12) sebelum data dikirim ke database engine.
  2. Menghilangkan risiko kelalaian pengembang (*developer error*) yang lupa memanggil `Hash::make()` manual di controller.
  3. Memastikan sifat *one-way cryptographic hashing*: data di database tidak dapat didekripsi kembali menjadi teks polos, sehingga sistem pemulihan akun wajib menggunakan mekanisme tautan token reset, bukan pengiriman password lama.

---

#### 5. Analisis Cookie Session
- **Kondisi Sebelum Login (Guest / Tamu):**  
  Ketika pengguna membuka halaman `/login` pertama kali:
  - Server mengirimkan header HTTP response: `Set-Cookie: laravel_session=eyJpdiI6...; path=/; httponly; samesite=lax`.
  - Nilai cookie berupa payload token acak yang terenkripsi (panjang ±300 karakter).
  - Di sisi server, sesi ini berstatus sesi tamu (*guest session*) di mana nilai `user_id` bernilai `null`.
- **Kondisi Sesudah Login (Authenticated User):**  
  Tepat setelah tombol submit login ditekan dan `session()->regenerate()` dieksekusi:
  - Server mengirimkan header `Set-Cookie` baru dengan nilai `laravel_session` yang **sepenuhnya berbeda (ID baru yang teracak)**.
  - Sesi server kini menyimpan relasi identitas pengguna (`Auth::id()`), hak akses peran, serta token CSRF baru.
- **Karakteristik Keamanan Cookie Laravel:**
  - **Enkripsi Penuh:** Seluruh isi cookie dienkripsi secara simetris menggunakan `APP_KEY` melalui middleware `EncryptCookies`, mencegah manipulasi isi session oleh pengguna.
  - **`HttpOnly = true`:** Cookie tidak dapat dibaca oleh script sisi klien melalui `document.cookie`, menutup celah pencurian cookie via serangan *Cross-Site Scripting* (XSS).
  - **`SameSite = lax`:** Mencegah peramban melampirkan cookie session pada request lintas situs berbahaya (*cross-origin POST*), memberikan proteksi bawaan terhadap serangan CSRF.
  - **`Secure = true` (Lingkungan Produksi):** Mengharuskan cookie hanya ditransmisikan melalui jalur aman terenkripsi TLS/HTTPS.

---

#### 6. Siklus Session vs Browser Cache
- **Perilaku Saat Logout:**  
  Method `LoginController::logout()` memanggil:
  ```php
  Auth::logout();
  $request->session()->invalidate();
  $request->session()->regenerateToken();
  ```
  Ini menghapus data sesi pengguna dari media penyimpanan server (tabel `sessions` / storage file) dan membuang token CSRF yang aktif.
- **Mengapa Halaman Lama Masih Tampak Saat Menekan Tombol "Back"?**  
  Fenomena halaman dashboard masih terlihat sekilas saat menekan tombol "Back" di browser **BUKAN** karena sesi pengguna masih aktif di server, melainkan karena mekanisme **BFCache (Back-Forward Cache)** peramban modern.  
  Peramban menyimpan cuplikan (*snapshot*) DOM dan tampilan visual yang telah dirender di memori lokal RAM untuk mempercepat navigasi pengguna tanpa mengirimkan HTTP Request ulang ke jaringan.
- **Bukti Request Baru Tetap Diblokir:**  
  Segera setelah pengguna mencoba berinteraksi dengan halaman cached tersebut (misalnya menekan tombol navigasi, refresh halaman F5, atau submit formulir):
  1. Peramban mengirimkan HTTP Request baru ke server.
  2. Middleware `auth` (`Illuminate\Auth\Middleware\Authenticate`) mencegat request dan memeriksa keabsahan sesi: `Auth::check()`.
  3. Karena sesi server telah hancur total (`invalidated`), middleware segera melempar respon `AuthenticationException`, yang otomatis menghasilkan HTTP 302 Redirect kembali ke halaman `/login`.
  4. Jika request berupa AJAX / form submit, request akan langsung gagal dengan kode status **401 Unauthorized** atau **419 Page Expired** (karena token CSRF lama sudah basi).
- **Mitigasi Anti-BFCache untuk Data Sensitif Kampus:**  
  Dapat ditambahkan header HTTP pada respon dashboard privat:
  ```http
  Cache-Control: no-cache, no-store, max-age=0, must-revalidate
  Pragma: no-cache
  ```
  Sehingga peramban dilarang menyimpan cache lokal dan selalu memvalidasi status autentikasi ke server.

---

## BREAK

### 2. BAGIAN BREAK — Delapan Kerusakan (Diperkaya Konsep Prompt Pack 7.2)

Eksperimen delapan skenario kerusakan dilakukan untuk menguji ketahanan sistem keamanan KampusLMS, menghubungkan analisis praktis dengan konsep arsitektur Laravel 12 dan panduan *Prompt Pack 7.2 (B, C, D)*.

---

### BREAK 1 — Session Fixation Terbuka

- **Prediksi Sebelum Mencoba:**  
  Jika baris `session()->regenerate()` dihapus dari proses login, pengguna tetap dapat masuk ke dashboard, namun ID session yang diterbitkan pada status tamu (*guest*) tidak akan diperbarui. Nilai cookie `laravel_session` akan sama persis sebelum dan sesudah login.
- **Skenario / Baris Kode yang Diuji:**  
  Menonaktifkan regenerasi session pada [`app/Http/Controllers/Auth/LoginController.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/Auth/LoginController.php#L48):
  ```php
  // $request->session()->regenerate(); // BARIS DIHAPUS / DIKOMENTARI
  ```
- **Langkah Pengujian:**  
  1. Akses halaman login awal melalui cURL dan simpan cookie guest ke file `cookies.txt`:
     ```bash
     curl -i -c cookies.txt http://127.0.0.1:8000/login
     ```
  2. Periksa nilai cookie `laravel_session` pada file `cookies.txt`.
  3. Lakukan POST login menggunakan file cookie yang sama:
     ```bash
     curl -i -b cookies.txt -c cookies.txt -X POST http://127.0.0.1:8000/login \
       -d "login=admin@kampuslms.test" -d "password=password" -d "_token=<CSRF_TOKEN>"
     ```
  4. Bandingkan nilai cookie `laravel_session` sebelum dan sesudah login.
- **Hasil Aktual & Kode Status HTTP:**  
  - Status HTTP: **302 Found** (Redirect ke `/dashboard`).
  - Nilai cookie `laravel_session` pada header response **TIDAK BERGANTI**. ID session awal yang dibuat saat status *unauthenticated* dipertahankan dan diasosiasikan langsung dengan user ID `admin`.
- **Risiko Keamanan & Konsep Modul:**  
  Membuka kerentanan fatal *Session Fixation* (CWE-384). Penyerang dapat menyuntikkan session ID yang telah diketahuinya ke peramban korban (misal lewat link phising atau manipulasi cookie). Begitu korban login, session ID tersebut menjadi aktif dan terotentikasi, sehingga penyerang dapat langsung membajak akun korban dari perangkat lain tanpa perlu membobol kata sandi.
- **Kondisi Setelah Dikembalikan:**  
  Baris `$request->session()->regenerate();` dipulihkan utuh pada baris 48 `LoginController.php`.

---

### BREAK 2 — UI Obfuscation vs Enforced Security (Kaitan Prompt Pack 7.2 B)

- **Prediksi Sebelum Mencoba:**  
  Menyembunyikan tombol menggunakan direktif `@can` di Blade hanyalah rekayasa visual (*UI Obfuscation*). Jika `Gate::authorize()` di controller dihapus, pengguna yang tidak memiliki hak akses tetap dapat menembus endpoint backend secara langsung menggunakan cURL atau Postman.
- **Skenario / Baris Kode yang Diuji:**  
  1. Pada Blade view [`resources/views/courses/show.blade.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/resources/views/courses/show.blade.php), tombol edit tetap dibungkus:
     ```blade
     @can('update', $course)
         <a href="{{ route('courses.edit', $course) }}" class="btn btn-warning">Edit Mata Kuliah</a>
     @endcan
     ```
  2. Pada controller [`app/Http/Controllers/Dosen/CourseController.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/Dosen/CourseController.php#L30), hapus/komentari baris otorisasi:
     ```php
     // Gate::authorize('view', $course); // DIHAPUS SEMENTARA
     ```
- **Langkah Pengujian:**  
  1. Login sebagai Dosen B (`dosen2`, bukan pengampu dari Mata Kuliah ID=1 milik Dosen A).
  2. Buka tampilan browser: tombol "Edit" atau detail mata kuliah memang tidak muncul di antarmuka Dosen B.
  3. Kirimkan request langsung via cURL menggunakan cookie Dosen B:
     ```bash
     curl -i -b cookie_dosen_b.txt http://127.0.0.1:8000/dosen/courses/1
     ```
- **Hasil Aktual & Kode Status HTTP:**  
  - Status HTTP: **200 OK** (Bukan 403 Forbidden).
  - Data mata kuliah milik Dosen A berhasil ditarik dan dibaca oleh Dosen B meskipun tombol di Blade tersembunyi.
- **Risiko Keamanan & Konsep Modul (Prompt Pack 7.2 B):**  
  Kasus ini membuktikan peringatan krusial Prompt Pack 7.2 B: **`@can` di Blade BUKAN pengaman, melainkan kenyamanan antarmuka.** Keamanan sejati wajib ditegakkan di sisi server melalui `Gate::authorize()`. Mengandalkan proteksi hanya pada visibilitas tombol di frontend adalah ilusi keamanan (*Security through Obscurity*).
- **Kondisi Setelah Dikembalikan:**  
  `Gate::authorize('view', $course);` dan `Gate::authorize('update', $course);` dipulihkan utuh di seluruh controller terkait.

---

### BREAK 3 — Uji Tembus Dosen A ke Data Dosen B (Kaitan Prompt Pack 7.2 D)

- **Prediksi Sebelum Mencoba:**  
  Ketika `CoursePolicy` aktif dan dipanggil melalui `Gate::authorize()`, request manipulasi data dari Dosen A ke mata kuliah milik Dosen B akan ditolak mentah-mentah dengan status HTTP `403 Forbidden`.
- **Skenario / Baris Kode yang Diuji:**  
  Aturan kepemilikan pada [`app/Policies/CoursePolicy.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Policies/CoursePolicy.php#L35):
  ```php
  if ($user->role === 'dosen') {
      return $course->lecturer_id === $user->id;
  }
  ```
  Dipanggil pada controller via `Gate::authorize('update', $course)`.
- **Langkah Pengujian:**  
  1. Menggunakan akun seeder Dosen A: `dosen@kampuslms.test` (ID: 2).
  2. Mata kuliah target: Course ID 2 (milik Dosen B, `lecturer_id`: 5).
  3. Kirim request HTTP PUT untuk memperbarui data mata kuliah dosen lain:
     ```bash
     curl -i -b cookie_dosen_a.txt -X PUT http://127.0.0.1:8000/dosen/courses/2 \
       -d "name=Mata+Kuliah+Dibajak" -d "_token=<CSRF>"
     ```
  4. Validasi melalui unit test `PolicyAuthorizationTest::test_course_policy_permissions`:
     ```php
     $this->assertFalse($this->dosen2->can('update', $this->course1));
     ```
- **Hasil Aktual & Kode Status HTTP:**  
  - Status HTTP: **403 Forbidden**  
  - Pesan Exception: `This action is unauthorized.`  
  - Perubahan data di basis data dibatalkan secara otomatis.
- **Risiko Keamanan & Konsep Modul (Prompt Pack 7.2 D):**  
  Menguji isolasi horizontal antar sesama pengguna dengan hak akses setara (*Horizontal Privilege Escalation*). Policy memastikan dosen hanya memiliki kedaulatan atas mata kuliah yang diampunya sendiri, mencegah sabotase silabus, manipulasi materi, atau pencurian hak akademik antar dosen.
- **Kondisi Setelah Dikembalikan:**  
  Sistem otorisasi `CoursePolicy` tetap aktif dan terverifikasi 100% aman.

---

### BREAK 4 — Uji Tembus IDOR Mahasiswa pada Submission (Kaitan Minggu 5 & Prompt Pack 7.2 D)

- **Prediksi Sebelum Mencoba:**  
  Pada Minggu 5, endpoint `/mahasiswa/submissions/{id}` rentan IDOR karena mahasiswa hanya dicek loginnya tanpa validasi kepemilikan submission. Pada Minggu 7, implementasi `SubmissionPolicy::view` akan menutup celah ini dan merespon dengan status HTTP `403 Forbidden`.
- **Skenario / Baris Kode yang Diuji:**  
  Aturan pencegahan IDOR pada [`app/Policies/SubmissionPolicy.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Policies/SubmissionPolicy.php#L42-L44):
  ```php
  if ($user->role === 'mahasiswa') {
      return $submission->user_id === $user->id;
  }
  ```
  Ditegakkan pada [`app/Http/Controllers/Mahasiswa/SubmissionController.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/Mahasiswa/SubmissionController.php#L69):
  ```php
  public function show(Submission $submission)
  {
      Gate::authorize('view', $submission);
      return view('mahasiswa.submissions.show', compact('submission'));
  }
  ```
- **Langkah Pengujian:**  
  1. Login sebagai Mahasiswa A (`mahasiswa@kampuslms.test`, ID: 3).
  2. Mahasiswa B (ID: 4) memiliki berkas submission tugas dengan ID 15.
  3. Mahasiswa A mencoba mengakses submission milik Mahasiswa B dengan mengganti URL parameter ID secara langsung:
     ```bash
     curl -i -b cookie_mhs_a.txt http://127.0.0.1:8000/mahasiswa/submissions/15
     ```
- **Hasil Aktual & Kode Status HTTP:**  
  - Status HTTP: **403 Forbidden** (Berbeda drastis dengan Minggu 5 yang mengembalikan **200 OK**).  
  - File lampiran tugas dan catatan tugas Mahasiswa B terlindungi sepenuhnya dari akses Mahasiswa A.
- **Risiko Keamanan & Konsep Modul (Prompt Pack 7.2 D):**  
  Celah *Insecure Direct Object Reference* (IDOR) pada entitas submission dapat mengakibatkan kecurangan akademik massal (penjiplakan tugas sebelum tenggat) serta pelanggaran privasi data mahasiswa. `SubmissionPolicy` terbukti menjadi benteng utama dalam mengisolasi kepemilikan berkas akademik per individu.
- **Kondisi Setelah Dikembalikan:**  
  Pemeriksaan `SubmissionPolicy` tetap terpasang kokoh.

---

### BREAK 5 — Kebocoran di Tingkat Daftar / Collection (Kaitan Prompt Pack 7.2 C)

- **Prediksi Sebelum Mencoba:**  
  Jika method `index` controller memanggil `Course::paginate()` secara polos tanpa penyaringan peran di tingkat query database, mahasiswa akan dapat melihat seluruh mata kuliah yang ada di universitas (termasuk mata kuliah prodi lain atau yang belum dibuka).
- **Skenario / Baris Kode yang Diuji:**  
  Mengganti query tersaring dengan query polos:
  ```php
  // ❌ Skenario Rusak: Query membocorkan seluruh data universitas
  $courses = Course::paginate(15);
  ```
  Dibandingkan dengan standar aman berbasis peran yang telah diimplementasikan:
  ```php
  // ✔ Skenario Aman: Scoping ketat di level query database
  $courses = (match ($user->role) {
      'admin'     => Course::query(),
      'dosen'     => $user->taughtCourses(),
      'mahasiswa' => $user->courses(),
      default     => abort(403),
  })->with('lecturer')->paginate(15);
  ```
- **Langkah Pengujian:**  
  1. Login sebagai akun Mahasiswa (`mahasiswa@kampuslms.test`) yang hanya terdaftar di 2 mata kuliah.
  2. Buka rute `/courses` atau rute katalog internal.
  3. Amati isi data tabel yang dirender dan hitung total data pada pagination footer.
- **Hasil Aktual & Kode Status HTTP:**  
  - Status HTTP: **200 OK**, tetapi terjadi **Kebocoran Data Masif**.  
  - Seluruh mata kuliah di kampus (misal 50+ mata kuliah) muncul di layar mahasiswa, lengkap dengan informasi dosen dan status internalnya.
- **Risiko Keamanan & Konsep Modul (Prompt Pack 7.2 C):**  
  Berdasarkan materi Prompt Pack 7.2 C: **Menyaring data di Blade View adalah kesalahan arsitektur fatal (*critical anti-pattern*).**  
  Alasannya:
  1. **Kerusakan Pagination:** Jika filter `@if(...)` ditaruh di view, halaman 1 bisa tampak kosong melompong padahal indikator halaman menunjukkan `Page 1 of 5` (karena 15 data ditarik dari DB tetapi 15-15 nya di-skip di Blade).
  2. **Kebocoran Memori & API:** Data yang tidak berhak tetap ditarik dari database ke memori server dan rawan bocor melalui JSON output atau debugger.
  3. **Beban Query Berat:** Membebani database engine dengan mengambil ribuan data yang akhirnya dibuang di view.  
  Solusinya adalah scoping query di database menggunakan ekspresi `match ($user->role)`.
- **Kondisi Setelah Dikembalikan:**  
  Query scoping berbasis peran dengan relasi `taughtCourses()` dan `courses()` dipulihkan utuh.

---

### BREAK 6 — Mass Assignment Manipulasi Role

- **Prediksi Sebelum Mencoba:**  
  Jika atribut `role` tidak didaftarkan pada `$fillable` model `User`, upaya pengguna menyuntikkan parameter `role=admin` melalui form pembaruan profil akan diabaikan secara diam-diam oleh Eloquent, sehingga role pengguna tidak berubah.
- **Skenario / Baris Kode yang Diuji:**  
  Pemeriksaan whitelist mass assignment pada [`app/Models/User.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Models/User.php#L24-L29):
  ```php
  protected $fillable = [
      'name',
      'email',
      'password',
      'nim_nip',
      // 'role' sengaja TIDAK dimasukkan di sini
  ];
  ```
- **Langkah Pengujian:**  
  1. Login sebagai mahasiswa biasa.
  2. Kirim request pembaruan profil pengguna dengan menyisipkan payload `role=admin`:
     ```bash
     curl -i -b cookie_mhs.txt -X PUT http://127.0.0.1:8000/profile \
       -d "name=Mahasiswa+Biasa" -d "role=admin" -d "_token=<CSRF>"
     ```
  3. Periksa status nilai kolom `role` pada basis data menggunakan Tinker:
     ```php
     App\Models\User::where('email', 'mahasiswa@kampuslms.test')->value('role');
     ```
- **Hasil Aktual & Kode Status HTTP:**  
  - Status HTTP: **200 OK / 302 Found** (Nama berhasil diperbarui).  
  - Nilai kolom `role` di basis data: **TETAP `'mahasiswa'`** (Suntikan `role=admin` ditolak dan diabaikan oleh Eloquent).
- **Risiko Keamanan & Konsep Modul:**  
  Jika kolom `role` ceroboh dimasukkan ke dalam `$fillable`, aplikasi rentan terhadap serangan *Mass Assignment Privilege Escalation* (CWE-915). Pengguna biasa cukup memodifikasi elemen form via Inspect Element atau mengirim request HTTP mentah untuk mengangkat dirinya sendiri menjadi Super Administrator sistem.
- **Kondisi Setelah Dikembalikan:**  
  Properti `$fillable` pada model `User` tetap bersih dan tidak memuat kolom `role`.

---

### BREAK 7 — Kata Sandi Plaintext di Database

- **Prediksi Sebelum Mencoba:**  
  Jika cast `'password' => 'hashed'` dihapus dari model `User`, pembuatan user baru menggunakan Eloquent tanpa enkripsi manual akan menyimpan kata sandi apa adanya (*plaintext*) ke dalam kolom `password` di tabel `users`.
- **Skenario / Baris Kode yang Diuji:**  
  Menghapus baris casting pada [`app/Models/User.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Models/User.php#L50):
  ```php
  protected function casts(): array
  {
      return [
          'email_verified_at' => 'datetime',
          // 'password' => 'hashed', // DIHAPUS SEMENTARA
      ];
  }
  ```
- **Langkah Pengujian:**  
  1. Eksekusi perintah pembuatan user melalui Laravel Tinker:
     ```bash
     php artisan tinker --execute="
     \$u = App\Models\User::create([
         'name' => 'Korban Plaintext',
         'email' => 'korban@kampuslms.test',
         'password' => 'RahasiaSuper123!',
         'nim_nip' => '99999999'
     ]);
     echo DB::table('users')->where('email', 'korban@kampuslms.test')->value('password');
     "
     ```
  2. Periksa format teks yang tercetak.
- **Hasil Aktual & Kode Status HTTP:**  
  - Output string yang tersimpan di kolom database: **`RahasiaSuper123!`** (Teks polos murni!).  
  - Seharusnya string berupa hash Bcrypt seperti: `$2y$12$eX9qWp8...`.
- **Risiko Keamanan & Konsep Modul:**  
  Pelanggaran keamanan tingkat kritis (*Catastrophic Security Vulnerability*). Apabila basis data bocor akibat SQL Injection, kebocoran berkas backup, atau akses tidak sah, seluruh kata sandi akun pengguna dapat langsung dibaca seketika. Karena mayoritas pengguna memiliki kebiasaan menggunakan kata sandi yang sama di berbagai platform (*credential reuse*), kebocoran ini membahayakan akun email, perbankan, dan data pribadi pengguna secara luas.
- **Kondisi Setelah Dikembalikan:**  
  Cast `'password' => 'hashed'` dipulihkan utuh pada method `casts()` di [`app/Models/User.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Models/User.php#L50). Data uji coba dihapus dari basis data.

---

### BREAK 8 — Pembajakan Cookie Session

- **Prediksi Sebelum Mencoba:**  
  Jika nilai cookie `laravel_session` disalin dari browser yang sudah login lalu disuntikkan ke peramban lain yang bersih (*incognito*), peramban kedua akan langsung memperoleh akses ke dashboard tanpa melalui proses login.
- **Skenario / Baris Kode yang Diuji:**  
  Menguji mekanisme *Stateful Session Hijacking* pada arsitektur Laravel Web Guard.
- **Langkah Pengujian:**  
  1. Buka Google Chrome, login sebagai Admin (`admin@kampuslms.test`).
  2. Buka DevTools (F12) → Tab *Application* → *Cookies* → salin nilai string dari cookie `laravel_session`.
  3. Buka jendela *Incognito* baru (atau peramban lain seperti Mozilla Firefox / Microsoft Edge) yang belum memiliki sesi aktif.
  4. Buka alamat `http://127.0.0.1:8000/login`.
  5. Buka DevTools pada peramban kedua, buat cookie baru bernama `laravel_session` dan tempelkan (*paste*) nilai yang telah disalin dari peramban pertama.
  6. Lakukan navigasi langsung ke rute `http://127.0.0.1:8000/dashboard` dan tekan Enter.
- **Hasil Aktual & Kode Status HTTP:**  
  - Status HTTP: **200 OK**.  
  - Peramban kedua langsung menampilkan halaman Dashboard Administrator tanpa meminta input email atau kata sandi sama sekali.
- **Risiko Keamanan & Konsep Modul:**  
  Mendemonstrasikan ancaman *Session Hijacking*. Protokol HTTP bersifat *stateless*, sehingga server sepenuhnya mengandalkan token session cookie sebagai tanda pengenal identitas pengguna.  
  Tiga atribut proteksi bawaan Laravel wajib dikonfigurasi secara ketat pada `config/session.php`:
  1. **`HttpOnly` (`true`):** Mencegah pencurian cookie melalui skrip JavaScript berbahaya (*XSS exploitation* via `document.cookie`).
  2. **`Secure` (`true`):** Memastikan cookie hanya dapat dikirimkan melalui jalur terenkripsi HTTPS, menangkal penyadapan paket jaringan pada Wi-Fi publik (*Man-in-the-Middle / packet sniffing*).
  3. **`SameSite` (`'lax'` / `'strict'`):** Mencegah cookie dikirimkan secara otomatis pada request berbahaya dari situs pihak ketiga (*Cross-Site Request Forgery*).

---

## 3. KESIMPULAN MINGGU 7

Berdasarkan hasil eksplorasi bagian **READ** dan pembuktian empiris delapan skenario **BREAK**, disimpulkan bahwa arsitektur keamanan web modern pada Laravel 12 dibangun di atas tiga fondasi yang saling melengkapi:

1. **Integritas Autentikasi dan State Sesi:**
   - Pemanggilan `session()->regenerate()` tepat setelah verifikasi kredensial berhasil adalah pertahanan mutlak dalam memutus rantai serangan *Session Fixation*.
   - Standar casting `'password' => 'hashed'` pada model `User` menjamin seluruh kata sandi tersimpan dalam format hash kriptografis Bcrypt satu arah secara otomatis, memitigasi dampak katastrofik kebocoran database.
   - Konfigurasi cookie berbendera `HttpOnly`, `Secure`, dan `SameSite` melindungi kredensial sesi dari bahaya pembajakan (*session hijacking*) dan eksploitasi XSS/CSRF.

2. **Otorisasi Terpusat per Objek via Policy (`Gate::authorize()`):**
   - Eksperimen BREAK 2 membuktikan bahwa direktif `@can` pada Blade hanyalah rekayasa visibilitas antarmuka (*UI obfuscation*) untuk kenyamanan pengguna, **bukan sistem pengaman**.
   - Penegakan otorisasi wajib berada di sisi server menggunakan `Gate::authorize()` yang terhubung langsung dengan kelas Policy (`CoursePolicy`, `MaterialPolicy`, `AssignmentPolicy`, `SubmissionPolicy`, `GradePolicy`).
   - Pendekatan ini secara tuntas menutup celah *Horizontal Privilege Escalation* (dosen mengakses mata kuliah dosen lain) dan IDOR antar mahasiswa pada berkas tugas maupun nilai.

3. **Penyaringan di Tingkat Query Database (`match ($user->role)`):**
   - Eksperimen BREAK 5 membuktikan bahwa otorisasi per objek belum cukup jika endpoint kumpulan data (*collection*) mengalami kebocoran di tingkat daftar (*index*).
   - Menyaring data menggunakan kondisi Blade di antarmuka merusak sistem paginasi dan membebani server.
   - Penerapan scoping query berbasis peran menggunakan konstruksi `match ($user->role)` memastikan bahwa basis data hanya mengembalikan rekaman data yang secara sah menjadi hak akses pengguna tersebut.

Dengan mengintegrasikan regenerasi session, proteksi mass assignment, Policy terpusat, dan scoping query database, seluruh **Daftar Titik Rawan IDOR** yang dipetakan pada Milestone Minggu 5 kini telah **tertutup rapat dan terverifikasi secara aman** pada Milestone Minggu 7.
