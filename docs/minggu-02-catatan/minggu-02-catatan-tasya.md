# Catatan Minggu 2

## Identitas Mahasiswa

Nama: Anastasya Salsabila Khoirunnisa  
NIM: 10241009  

---

## READ — Telusuri Satu Request Penuh (Siklus Rute `/tentang`)

Berdasarkan penelusuran arsitektur Laravel 12 pada proyek KampusLMS, berikut analisis mendalam terhadap 5 pertanyaan telaah siklus hidup (*lifecycle*) request URL `/tentang`:

---

### 1. Pencocokan Rute di `routes/web.php`
- **Berkas Rute:**  
  [`routes/web.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/routes/web.php#L71-L73) (Baris 71–73).
- **Kode:**
  ```php
  Route::get('/tentang', function () {
      return view('tentang');
  })->name('tentang');
  ```
- **Rincian:**  
  Method `GET|HEAD`, URI `/tentang`, dan nama alias rute `tentang`.

---

### 2. Penangan Request (Closure vs Controller)
- **Kondisi Saat Ini:**  
  Ditangani langsung oleh **Closure** (fungsi anonim) di dalam `routes/web.php`.
- **Perbedaan Arsitektur MVC:**  
  - **Closure:** Langsung mengembalikan view dari file route. Ringkas untuk prototyping halaman statis, tetapi membebani file rute jika logika bertambah.
  - **Controller:** Memisahkan alur logika ke class terpisah (`app/Http/Controllers/`). Menjaga prinsip *Separation of Concerns* agar file rute hanya berfungsi sebagai daftar navigasi.
- **Jika Dialihkan ke Controller:**  
  - Berkas Controller & Method: [`app/Http/Controllers/TentangController.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/) pada method `index()`.
  - Definisi Rute:
    ```php
    Route::get('/tentang', [TentangController::class, 'index'])->name('tentang');
    ```

---

### 3. Penyajian View
- **View yang Dikembalikan:**  
  `view('tentang')`.
- **Lokasi Path Berkas:**  
  [`resources/views/tentang.blade.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/resources/views/tentang.blade.php).

---

### 4. Struktur Layout & Mekanisme Komponen Blade
- **Pembungkus Layout:**  
  Menggunakan Blade Component `<x-layout title="Tentang Proyek - Kampus LMS Kelompok 01">`.
- **Berkas Layout:**  
  [`resources/views/components/layout.blade.php`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/resources/views/components/layout.blade.php).
- **Mekanisme Slot (`{{ $slot }}`):**  
  - Seluruh elemen HTML di dalam tag `<x-layout>...</x-layout>` otomatis ditangkap ke variabel `$slot`.
  - Komponen layout merender `$slot` di dalam tag `<main class="main-content">`, sehingga header, navigasi, CSS/JS `@vite`, dan footer otomatis membungkus konten tanpa duplikasi kode.
  - Atribut `title` dikirim sebagai prop dan ditampilkan dengan fallback: `{{ $title ?? 'Kampus LMS' }}`.

---

### 5. Verifikasi Terminal (`php artisan route:list`)
- **Perintah:**  
  ```bash
  php artisan route:list --path=tentang
  ```
- **Keluaran Terminal:**  
  ```text
  GET|HEAD       tentang ................................................................. tentang › routes/web.php:71
  ```
- **Kesimpulan:**  
  Cocok. Rute merespons `GET|HEAD`, URI `tentang`, nama `tentang`, dan diproses via Closure di `routes/web.php:71`.

---

## BREAK — Delapan Kerusakan

Eksperimen delapan skenario kerusakan dilakukan untuk menguji ketahanan rute, controller, view, dan mekanisme bundling aset pada KampusLMS:

### Tabel Eksperimen 8 Kerusakan

| # | Yang Dirusak | Prediksi Sebelum Mencoba | Pesan Error Sebenarnya & Nama Exception |
|---|---|---|---|
| **1** | Ubah `Route::get` menjadi `Route::post` pada rute daftar mata kuliah (`/courses`). | Error penolakan metode HTTP karena browser mengakses URL via address bar dengan metode GET. | **405 Method Not Allowed**<br>`MethodNotAllowedHttpException`<br>*"The GET method is not supported for route courses. Supported methods: POST."* |
| **2** | Ubah nama view di `return view(...)` menjadi view fiktif (misal: `courses.tidak_ada`). | Laravel gagal menemukan file Blade pada direktori views. | `InvalidArgumentException`<br>*"View [courses.tidak_ada] not found."* |
| **3** | Hapus `->name('courses.show')`, lalu muat halaman yang memanggil `route('courses.show')`. | Terjadi error karena helper `route()` memanggil nama rute yang sudah tidak terdaftar. | `RouteNotFoundException`<br>*"Route [courses.show] not defined."* |
| **4** | Pindahkan rute `/courses/{course}` ke ATAS rute `/courses/create`, lalu buka `/courses/create`. | Halaman formulir create gagal dibuka karena string `"create"` dianggap sebagai parameter ID `{course}`. | **404 Not Found** (`ModelNotFoundException`) atau `TypeError`. Laravel mencocokkan ke `show('create')` dan gagal menemukan data dengan ID `"create"`. |
| **5** | Ganti `{{ $nama }}` jadi `{!! $nama !!}`, lalu isi `$nama` dengan `<script>alert('XSS')</script>`. | Script JavaScript dieksekusi langsung oleh browser karena tidak di-escape. | **XSS Aktif:** Muncul pop-up alert browser bertuliskan `"XSS"`. Tag `<script>` dieksekusi tanpa filter sanitasi HTML. |
| **6** | Hapus `@vite(...)` dari komponen layout (`layout.blade.php`). | Tampilan CSS dan fungsi JS hilang, web menjadi HTML polos. | Tampilan menjadi HTML mentah tanpa styling (*Flash of Unstyled Content*). Seluruh layout, tema dark-mode, dan font tidak berfungsi. |
| **7** | Hentikan server Vite (`npm run dev`), lalu refresh halaman. | Browser gagal terhubung ke server aset lokal Vite. | Gagal memuat skrip HMR (`ERR_CONNECTION_REFUSED` ke `localhost:5173`) atau memicu `ViteManifestNotFoundException` jika manifest build belum ada. |
| **8** | Panggil `route('courses.show')` tanpa menyertakan argumen parameter model/ID. | Laravel error saat menyusun URL karena parameter rute wajib `{course}` tidak memiliki nilai. | `UrlGenerationException`<br>*"Missing required parameter for [Route: courses.show] [URI: courses/{course}]"* |

---

### 1. Ubah `Route::get` Menjadi `Route::post` pada Rute Daftar Mata Kuliah
- **Prediksi Sebelum Mencoba:**  
  Error penolakan metode HTTP karena peramban secara default selalu mengirim request `GET` saat mengakses URL langsung.
- **Hasil Aktual & Kode Status:**  
  HTTP `405 Method Not Allowed` (`MethodNotAllowedHttpException`).
- **Analisis & Risiko:**  
  Protokol HTTP membedakan semantik pengambilan data (`GET`) dan pengiriman payload (`POST`). Rute penyajian halaman wajib merespons `GET`.

---

### 2. Ubah Nama View Menjadi Tidak Ada
- **Prediksi Sebelum Mencoba:**  
  Laravel gagal menemukan berkas template Blade pada sistem berkas.
- **Hasil Aktual & Kode Status:**  
  `InvalidArgumentException: View [courses.tidak_ada] not found.` pada `FileViewFinder`.
- **Analisis & Risiko:**  
  Engine view Laravel mencari berkas secara fisik di bawah folder `resources/views/`. Nama view di controller harus presisi sesuai lokasi berkas.

---

### 3. Hapus `->name('courses.show')` pada Rute
- **Prediksi Sebelum Mencoba:**  
  Helper `route('courses.show')` gagal mencari rute karena nama alias telah dihapus.
- **Hasil Aktual & Kode Status:**  
  `RouteNotFoundException: Route [courses.show] not defined.`
- **Analisis & Risiko:**  
  *Named routes* memisahkan URL fisik dari kode view, memungkinkan refactoring URL tanpa merusak tautan di aplikasi.

---

### 4. Pindahkan Rute `/courses/{course}` ke ATAS `/courses/create`
- **Prediksi Sebelum Mencoba:**  
  Formulir pembuatan mata kuliah tidak dapat diakses karena kata `"create"` tertangkap oleh parameter wildcard `{course}`.
- **Hasil Aktual & Kode Status:**  
  HTTP `404 Not Found` atau `TypeError` saat controller menjalankan `show('create')`.
- **Analisis & Risiko:**  
  Laravel mengevaluasi rute dari atas ke bawah (*First-Match-Wins*). Rute spesifik (`/create`) harus selalu didaftarkan sebelum rute wildcard dinamis (`/{course}`).

---

### 5. Ganti `{{ $nama }}` Menjadi `{!! $nama !!}` dan Isi Skrip XSS
- **Prediksi Sebelum Mencoba:**  
  Skrip berbahaya JavaScript dieksekusi langsung oleh peramban tanpa escaping.
- **Hasil Aktual & Kode Status:**  
  Dialog popup `alert('XSS')` aktif tereksekusi di browser.
- **Analisis & Risiko:**  
  Sintaks `{{ }}` secara otomatis membungkus data dengan `htmlspecialchars()`, sedangkan `{!! !!}` mencetak data mentah (*raw*) yang membuka celah fatal Cross-Site Scripting (XSS).

---

### 6. Hapus `@vite(...)` dari Komponen Layout
- **Prediksi Sebelum Mencoba:**  
  Aset CSS dan JS tidak dimuat sehingga tampilan web menjadi HTML polos.
- **Hasil Aktual & Kode Status:**  
  Halaman web kehilangan seluruh styling (*Flash of Unstyled Content*).
- **Analisis & Risiko:**  
  Direktif `@vite` menyuntikkan tag `<link>` stylesheet dan modul skrip secara otomatis ke header dokumen.

---

### 7. Hentikan Dev Server Vite (`npm run dev`)
- **Prediksi Sebelum Mencoba:**  
  Browser gagal terhubung ke server bundler Vite lokal.
- **Hasil Aktual & Kode Status:**  
  Koneksi aset ke `localhost:5173` ditolak (`ERR_CONNECTION_REFUSED`) atau melempar `ViteManifestNotFoundException`.
- **Analisis & Risiko:**  
  Mode development membutuhkan dev server Vite yang aktif untuk HMR, sedangkan mode produksi mengandalkan file manifest statis hasil `npm run build`.

---

### 8. Panggil `route('courses.show')` Tanpa Mengirim Parameter
- **Prediksi Sebelum Mencoba:**  
  Laravel gagal merakit URL karena parameter dinamis wajib tidak disediakan.
- **Hasil Aktual & Kode Status:**  
  `UrlGenerationException: Missing required parameter for [Route: courses.show]`.
- **Analisis & Risiko:**  
  Parameter segmen kurung kurawal tunggal `{course}` bersifat wajib dan harus disuplai saat pemanggilan fungsi `route()`.

