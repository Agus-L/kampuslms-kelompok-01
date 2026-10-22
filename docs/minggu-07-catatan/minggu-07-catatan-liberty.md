# Identitas Mahasiswa 

## Nama : Agus Liberty Purba
## NIM : 10241007



## Read

### 1. Berkas dan method manakah yang bertugas menangani request POST saat proses login?

**Jawaban:**
- **Berkas:** `app/Http/Controllers/Auth/LoginController.php`
- **Method:** `public function login(Request $request)` (baris 26–60)
- **Route:** `Route::post('/login', [LoginController::class, 'login'])` pada `routes/web.php`.
- **Penjelasan:** Method ini memvalidasi input pengguna (`login` berupa Email atau NIM/NIP, serta `password`), mengecek kredensial lewat `Auth::attempt()`, meregenerasi session, dan mengarahkan pengguna ke dashboard sesuai perannya.

### 2. Di baris dan berkas manakah pemanggilan verifikasi kredensial `Auth::attempt()` dieksekusi?

**Jawaban:**
- **Berkas & Baris:** `app/Http/Controllers/Auth/LoginController.php` baris 47.
- **Potongan Kode:**
  ```php
  if (Auth::attempt($credentials, $remember)) {
  ```
- **Penjelasan:** Parameter `$credentials` memuat identifier dinamis (`email` atau `nim_nip`) dan teks mentah `password`. Laravel kemudian memverifikasi password tersebut dengan hash Bcrypt di tabel `users` menggunakan `Hash::check()`.

### 3. Di mana letak pemanggilan `session()->regenerate()` dan apa alasan keamanannya?

**Jawaban:**
- **Berkas & Baris:** `app/Http/Controllers/Auth/LoginController.php` baris 48 (langsung setelah `Auth::attempt()` sukses).
- **Potongan Kode:**
  ```php
  $request->session()->regenerate();
  ```
- **Penjelasan:** Berfungsi mencegah serangan **Session Fixation**. Dengan memperbarui ID session ke nilai acak baru setelah autentikasi berhasil, penyerang yang memegang atau menyisipkan ID session lama saat berstatus guest tidak dapat membajak sesi pengguna.

### 4. Di mana letak konfigurasi hashing kata sandi pada model `User`?

**Jawaban:**
- **Berkas & Baris:** `app/Models/User.php` baris 46–53 pada method `casts()`.
- **Potongan Kode:**
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
- **Penjelasan:** Cast `'password' => 'hashed'` memastikan atribut kata sandi otomatis di-hash satu arah (Bcrypt) oleh Eloquent sebelum disimpan ke database, tanpa perlu memanggil `Hash::make()` manual.

### 5. Apa perbedaan isi cookie session di DevTools Application sebelum dan sesudah proses login?

**Jawaban:**
- **Sebelum Login (Guest):** Cookie `laravel_session` berisi token sesi pengunjung biasa yang belum terikat dengan identitas pengguna di server (`user_id` bernilai `null`).
- **Sesudah Login:** Nilai string `laravel_session` **berubah total** menjadi token baru karena dipicu oleh `session()->regenerate()`, dan sesi di server kini terhubung ke akun pengguna yang berhasil login.
- **Karakteristik:** Memiliki flag `HttpOnly` (kebal dari pencurian skrip JS/XSS) dan `SameSite=Lax` (pencegahan CSRF).

### 6. Apa yang terjadi ketika menekan tombol Back di browser setelah melakukan logout, dan mengapa demikian?

**Jawaban:**
- **Kejadian:** Tampilan halaman sebelumnya (misal dashboard) mungkin masih tampak sekilas. Namun, ketika halaman di-refresh atau pengguna berinteraksi/mengklik tautan, sistem langsung mengalihkan kembali ke halaman `/login`.
- **Penyebab:** Halaman yang muncul sesaat berasal dari **cache lokal peramban (browser cache)**, bukan response baru dari server. Di server, sesi pengguna telah dihapus secara tuntas melalui `Auth::logout()` dan `$request->session()->invalidate()`.

---

### Break

| Yang Dirusak | Prediksi | Hasil Sebenarnya |
| :--- | :--- | :--- |
| Hapus `session()->regenerate()` dari login | Pengguna berhasil masuk, namun ID session tidak disegarkan sehingga rentan serangan *session fixation*. | Sesi login berhasil (302 Redirect), tetapi nilai cookie `laravel_session` tetap sama persis seperti sebelum login. |
| Hapus `Gate::authorize()` dari `update`, tapi biarkan `@can` di Blade | Tombol di antarmuka tidak tampak, tetapi endpoint backend tetap bisa ditembus jika diakses langsung lewat URL / cURL. | Tombol aksi tersembunyi di UI, namun request langsung ke endpoint tetap lolos (200 OK) tanpa pencegahan otorisasi. |
| Sebagai dosen A, kirim PUT ke mata kuliah dosen B lewat `curl` | Permintaan ditolak sistem karena Dosen A bukan pemilik mata kuliah yang bersangkutan. | Server mengembalikan respon `403 Forbidden` (`This action is unauthorized.`) berkat proteksi `CoursePolicy@update`. |
| Sebagai mahasiswa, buka submission mahasiswa lain (IDOR minggu 5) | Akses ditolak dan sistem mengembalikan respon 403 Forbidden. | Tampil halaman error `403 Forbidden`; mahasiswa tidak dapat melihat tugas mahasiswa lain karena dicegat `SubmissionPolicy`. |
| Ubah `index` menjadi `Course::paginate()` polos, login sebagai mahasiswa | Terjadi kebocoran tingkat daftar di mana seluruh data mata kuliah dapat dilihat oleh semua peran. | Akun mahasiswa dapat melihat seluruh daftar mata kuliah yang ada di database (200 OK), termasuk kelas yang tidak diikutinya. |
| Kirim `role=admin` pada form edit profil | Upaya eskalasi peran gagal karena field `role` dibatasi oleh validasi / mass assignment protection. | Nilai kolom `role` di database tidak berubah karena disaring ketat pada Form Request dan model. |
| Ganti cast `hashed` menjadi tidak ada, buat user baru, lihat kolom password di database | Kata sandi pengguna baru tersimpan dalam bentuk teks mentah tanpa enkripsi. | Pada database tabel `users`, kolom `password` tersimpan sebagai teks polos (*plaintext*) tanpa melalui hashing Bcrypt. |
| Login, salin cookie session, tempel di browser lain | Sesi akun terbawa ke browser kedua, membuktikan pentingnya pengamanan atribut cookie. | Browser kedua langsung berada dalam kondisi login tanpa memasukkan kredensial, menegaskan pentingnya flag `HttpOnly` dan `Secure`. |

---
