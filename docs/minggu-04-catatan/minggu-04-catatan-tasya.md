# Catatan MINGGU 4

## Identitas Mahasiswa

Nama: Anastasya Salsabila Khoirunnisa  
NIM: 10241009

---

## READ — Telusuri Satu Siklus Form Gagal

Pengujian siklus form gagal dilakukan pada fitur tambah Mata Kuliah dengan memasukkan nilai tidak valid: **SKS = 99** (melebihi batas maksimal validasi `max:6`).

### 1. Method yang Menerima Request
- **Method HTTP:** `POST`
- **Route yang Menerima Request:** Route bernama `courses.store` dengan URL path `/courses`. Route ini didaftarkan di file [routes/web.php](file:///C:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/routes/web.php) melalui resource controller:
  ```php
  Route::resource('courses', CourseController::class);
  ```
- **Controller:** `App\Http\Controllers\CourseController`
- **Method Controller:** `store(StoreCourseRequest $request)`

### 2. Titik Validasi
- **File:** [app/Http/Requests/StoreCourseRequest.php](file:///C:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Requests/StoreCourseRequest.php)
- **Class / Method:** `App\Http\Requests\StoreCourseRequest::rules()`
- **Posisi Eksekusi Validasi:** Validasi terjadi **SEBELUM baris pertama** method controller `store()` dieksekusi.
- **Mekanisme Laravel:**
  Laravel memanfaatkan fitur *Form Request Validation*. Ketika method `CourseController::store(StoreCourseRequest $request)` dipanggil, Laravel Service Container melakukan *dependency injection* dan me-resolve class `StoreCourseRequest`.
  Class tersebut mewarisi `Illuminate\Foundation\Http\FormRequest` yang mengimplementasikan `Illuminate\Contracts\Validation\ValidatesWhenResolved`. Melalui trait `ValidatesWhenResolvedTrait`, method `validateResolved()` dieksekusi secara otomatis saat request selesai di-resolve. Method ini memicu validator dengan aturan yang ada di method `rules()`. Jika validasi gagal, Laravel langsung melempar `Illuminate\Validation\ValidationException`, sehingga alur eksekusi terhenti dan baris-baris kode di dalam method `CourseController::store()` sama sekali tidak pernah dijalankan.

### 3. Redirect Setelah Validasi Gagal
- **Tujuan Redirect:** Kembali ke URL halaman form sebelumnya, yaitu `http://127.0.0.1:8000/courses/create` (route `courses.create`).
- **Mekanisme / Kode Penentu Tujuan:**
  Pada class `FormRequest`, ketika validasi gagal method `failedValidation(Validator $validator)` dipanggil dan mengeksekusi:
  ```php
  throw (new ValidationException($validator))->redirectTo($this->getRedirectUrl());
  ```
  Method `$this->getRedirectUrl()` secara default memanggil `url()->previous()` (mengambil URL dari header HTTP `Referer` atau session riwayat request sebelumnya).
  `ValidationException` kemudian ditangkap oleh Exception Handler bawaan Laravel (`Illuminate\Foundation\Exceptions\Handler`), yang otomatis menyusun respons HTTP 302 (Redirect) kembali ke URL asal (`courses/create`) dengan membawa flash data session berupa `errors` (`withErrors()`) dan input lama pengguna (`withInput()`).
- **Yang Benar-benar Terjadi:**
  Browser menerima HTTP status code `302 Found` dengan header `Location: http://127.0.0.1:8000/courses/create`. Browser secara otomatis melakukan request `GET` ulang ke URL tersebut, lalu merender kembali halaman form create yang sudah dilengkapi dengan pesan kesalahan dan nilai input lama.

### 4. Sumber Pesan @error('sks')
- **Hubungan Validation Error, Error Bag, dan `@error('sks')`:**
  1. Ketika validasi SKS gagal (nilai 99 > 6), validator memasukkan pesan kesalahan ke dalam objek `Illuminate\Support\MessageBag`.
  2. Saat respons redirect 302 dibuat, MessageBag ini disimpan ke dalam flash session dengan key `errors`.
  3. Middleware `Illuminate\View\Middleware\ShareErrorsFromSession` yang terdaftar pada grup middleware `web` membaca flash data tersebut dan membagikannya ke seluruh view Blade melalui variabel global `$errors` (berupa instance `Illuminate\Support\ViewErrorBag`).
  4. Direktif Blade `@error('sks')` pada [resources/views/courses/create.blade.php](file:///C:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/resources/views/courses/create.blade.php) oleh Laravel Blade Compiler diterjemahkan menjadi:
     ```php
     <?php $__errorArgs = ['sks'];
     if ($errors->has($__errorArgs[0])) :
     if (isset($message)) { $__messageOriginal = $message; }
     $message = $errors->first($__errorArgs[0]); ?>
         <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
     <?php unset($message);
     if (isset($__messageOriginal)) { $message = $__messageOriginal; }
     endif;
     unset($__errorArgs, $__bag); ?>
     ```
  5. Pesan spesifik pada project diambil dari method `messages()` pada [StoreCourseRequest.php](file:///C:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Requests/StoreCourseRequest.php):
     ```php
     'sks.max' => 'Bobot SKS maksimal :max SKS.',
     ```
     Sehingga teks yang dirender ke layar pengguna adalah: **"Bobot SKS maksimal 6 SKS."**.

### 5. Sumber Nilai old('sks')
- **Sumber Nilai:** Berasal dari data payload HTTP POST yang dikirimkan oleh pengguna (`$request->input('sks')` / `$_POST['sks']`).
- **Cara Laravel Menyimpannya:**
  Saat `ValidationException` dilempar, exception handler memanggil redirect dengan method `withInput()`. Method ini memanggil `$session->flashInput($request->input())`, yang menyimpan seluruh array data form ke dalam session Laravel di bawah kunci `_old_input`.
- **Masa Berlaku Nilai:**
  Nilai tersebut adalah data *flash session* yang bersifat sementara. Nilai hanya tersedia selama **1 siklus request berikutnya**. Setelah halaman form `GET /courses/create` selesai dirender dan siklus request berakhir, Laravel menandai flash session tersebut untuk dihapus sehingga data `_old_input` dibersihkan pada request selanjutnya.
- **Penggunaan Kembali oleh Form:**
  Helper function `old('sks')` di dalam template Blade memanggil `app('request')->old('sks')` yang mengambil data dari session key `_old_input.sks`. Nilai tersebut dicetak ke dalam atribut input:
  ```html
  <input ... value="{{ old('sks') }}">
  ```
  Pada pengujian ini, atribut `value` pada input SKS terisi kembali dengan angka `99`, sehingga pengguna tidak perlu mengetik ulang dari nol.

### 6. Cookie Session Laravel
- **Nama Cookie Session Aktual:**
  Berdasarkan konfigurasi pada [config/session.php](file:///C:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/config/session.php) baris 156:
  ```php
  'cookie' => env(
      'SESSION_COOKIE',
      Str::slug(env('APP_NAME', 'laravel'), '_').'_session'
  ),
  ```
  dan hasil inspeksi langsung header respons HTTP (`Set-Cookie`) dari server lokal yang berjalan, cookie yang benar-benar digunakan adalah:
  1. **`laravel_session`** — Cookie session utama Laravel yang berisi ID sesi terenkripsi untuk mengaitkan request browser dengan data session di server.
  2. **`XSRF-TOKEN`** — Cookie tambahan berisi token enkripsi CSRF yang digunakan untuk proteksi request JavaScript/AJAX.

---

# BREAK — Tujuh Kerusakan

## 1. Hapus @csrf

### Prediksi
Jika direktif `@csrf` dihapus dari form create, request POST tidak akan menyertakan field `_token`. Middleware proteksi CSRF Laravel (`ValidateCsrfToken`) akan menolak request dan menghasilkan error HTTP `419 Page Expired`.

### Perubahan yang Dilakukan
Menghapus baris `@csrf` pada file [resources/views/courses/create.blade.php](file:///C:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/resources/views/courses/create.blade.php):
```blade
<!-- Sebelum -->
<form action="{{ route('courses.store') }}" method="POST">
    @csrf

<!-- Sesudah (sementara) -->
<form action="{{ route('courses.store') }}" method="POST">
```

### Pengujian
Membuka halaman `http://127.0.0.1:8000/courses/create` di browser, mengisi form lengkap, lalu menekan tombol "Simpan Mata Kuliah".

### Hasil Aktual
- Browser menampilkan halaman error Laravel dengan judul:
  **"419 | Page Expired"**
- Status HTTP yang diterima: `419 Unknown Status / Page Expired`.
- Pada log aplikasi tercatat exception: `Illuminate\Session\TokenMismatchException`.
- Data form sama sekali tidak diproses oleh controller dan tidak masuk ke database.

### Kesimpulan
CSRF Protection pada Laravel berfungsi mencegah eksploitasi Cross-Site Request Forgery di mana situs eksternal berbahaya memalsukan request atas nama pengguna yang sedang terotentikasi. Tanpa validasi token rahasia per-sesi ini, aplikasi menolak seluruh request berstatus modifikasi (POST/PUT/PATCH/DELETE).

---

## 2. Ganti validated() menjadi all()

### Prediksi
Jika `$request->validated()` diganti dengan `$request->all()`, controller akan mengambil seluruh data yang dikirim oleh client tanpa difilter oleh Form Request. Jika ada attacker yang mengirim field tambahan liar, field tersebut akan diteruskan ke `Course::create()`. Namun, apakah field liar masuk ke database atau tidak tergantung pada proteksi `$fillable` di model `Course`.

### Perubahan yang Dilakukan
Mengubah method `store()` di [app/Http/Controllers/CourseController.php](file:///C:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/CourseController.php):
```php
// Sebelum
Course::create($request->validated());

// Sesudah (sementara)
Course::create($request->all());
```

### Pengujian
Pengujian dilakukan menggunakan `curl.exe` dengan mengirimkan field form yang valid ditambah tiga field liar yang tidak ada di form: `is_admin=1`, `role=superadmin`, dan `injected_field=hacked`:
```bash
curl.exe -i -s -b cookies.txt -c cookies.txt -X POST http://127.0.0.1:8000/courses \
  -d "_token=..." \
  -d "code=IF991" \
  -d "name=Uji Break 2" \
  -d "sks=3" \
  -d "lecturer_id=2" \
  -d "status=draft" \
  -d "is_admin=1" \
  -d "role=superadmin" \
  -d "injected_field=hacked"
```

### Hasil Aktual
- Request berhasil diproses dan server mengembalikan status `302 Found` (redirect ke `/courses` dengan pesan sukses).
- Pemeriksaan langsung ke database SQLite via Tinker:
  ```php
  Course::where('code', 'IF991')->first()->toArray();
  ```
  Menghasilkan:
  ```
  id: 6
  code: "IF991"
  name: "Uji Break 2"
  sks: 3
  lecturer_id: 2
  status: "draft"
  description: null
  ```
- **Field liar `is_admin`, `role`, dan `injected_field` TIDAK TERSIMPAN ke database.**
- Model `Course` memiliki proteksi:
  ```php
  protected $fillable = ['code', 'name', 'sks', 'lecturer_id', 'status', 'description'];
  ```
  Eloquent secara otomatis mengabaikan field yang tidak terdaftar dalam whitelist `$fillable`.

### Kesimpulan
Mengganti `validated()` menjadi `all()` menghilangkan perlindungan di level HTTP / Controller, sehingga mass assignment berpotensi terjadi jika model menggunakan `protected $guarded = []`. Namun pada project ini, mass assignment tetap dicegah oleh lapisan Model Eloquent melalui whitelist `$fillable`. Ini membuktikan implementasi prinsip *Defense in Depth* (keamanan berlapis) di Laravel.

---

## 3. Hapus exists:users,id pada lecturer_id

### Prediksi
Jika aturan validasi `exists:users,id` pada `lecturer_id` dihapus, form request akan meloloskan nilai ID dosen sembarang (misal `99999`). Jika database memiliki relasi foreign key constraint aktif, database akan menolak penyimpanan dan menghasilkan error `QueryException` (HTTP 500). Jika tidak ada foreign key, maka data yatim (orphan record) akan masuk ke database.

### Perubahan yang Dilakukan
Menghapus aturan `'exists:users,id'` pada [app/Http/Requests/StoreCourseRequest.php](file:///C:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Requests/StoreCourseRequest.php):
```php
// Sebelum
'lecturer_id' => ['required', 'integer', 'exists:users,id'],

// Sesudah (sementara)
'lecturer_id' => ['required', 'integer'],
```

### Pengujian
Pengujian dilakukan menggunakan `curl.exe` dengan mengirimkan `lecturer_id=99999` (ID user yang tidak ada di database):
```bash
curl.exe -i -s -b cookies.txt -c cookies.txt -X POST http://127.0.0.1:8000/courses \
  -d "_token=..." \
  -d "code=IF992" \
  -d "name=Uji Break 3" \
  -d "sks=3" \
  -d "lecturer_id=99999" \
  -d "status=draft"
```

### Hasil Aktual
- Form Request **meloloskan validasi** (tidak ada redirect 302 kegagalan validasi).
- Namun eksekusi query insert ke database gagal dengan status **HTTP 500 Internal Server Error**.
- Pesan error sebenarnya:
  ```
  Illuminate\Database\QueryException
  SQLSTATE[23000]: Integrity constraint violation: 19 FOREIGN KEY constraint failed
  (Connection: sqlite, SQL: insert into "courses" ("code", "name", "sks", "lecturer_id", "status", "updated_at", "created_at") values (IF992, Uji Break 3, 3, 99999, draft, 2026-09-24 01:21:42, 2026-09-24 01:21:42))
  ```
- **Data yatim TIDAK MASUK ke database.**
- Database SQLite project memiliki foreign key constraint aktif dari migration:
  ```php
  $table->foreignId('lecturer_id')->constrained('users')->cascadeOnDelete();
  ```

### Kesimpulan
Menghapus `exists:users,id` tidak sampai meloloskan data yatim karena integritas relasional dijaga ketat oleh Foreign Key constraint di level database. Namun, ketiadaan validasi di level form request menyebabkan kegagalan database tidak tertangani (*unhandled exception*) dan merusak *user experience* dengan menampilkan layar crash HTTP 500, bukannya pesan validasi ramah pengguna.

---

## 4. Hapus Validasi in:... pada status

### Prediksi
Jika validasi `in:draft,active,archived` dihapus, Form Request akan meloloskan status apa pun (seperti `status=superadmin`). Tetapi jika database menggunakan tipe enum dengan constraint CHECK, SQLite akan menolak data tersebut.

### Perubahan yang Dilakukan
Menghapus aturan `Rule::in(...)` pada [app/Http/Requests/StoreCourseRequest.php](file:///C:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Requests/StoreCourseRequest.php):
```php
// Sebelum
'status' => ['required', Rule::in(['draft', 'active', 'archived'])],

// Sesudah (sementara)
'status' => ['required'],
```

### Pengujian
Pengujian dilakukan menggunakan `curl.exe` mengirimkan status di luar enum yang diperbolehkan (`status=superadmin`):
```bash
curl.exe -i -s -b cookies.txt -c cookies.txt -X POST http://127.0.0.1:8000/courses \
  -d "_token=..." \
  -d "code=IF993" \
  -d "name=Uji Break 4" \
  -d "sks=3" \
  -d "lecturer_id=2" \
  -d "status=superadmin"
```

### Hasil Aktual
- Form Request meloloskan data tersebut karena hanya memeriksa aturan `required`.
- Namun eksekusi query INSERT ke database gagal dan memicu status **HTTP 500 Internal Server Error**.
- Pesan error sebenarnya:
  ```
  Illuminate\Database\QueryException
  SQLSTATE[23000]: Integrity constraint violation: 19 CHECK constraint failed: status
  (Connection: sqlite, SQL: insert into "courses" ("code", "name", "sks", "lecturer_id", "status", "updated_at", "created_at") values (IF993, Uji Break 4, 3, 2, superadmin, 2026-09-24 01:23:01, 2026-09-24 01:23:01))
  ```
- **Nilai `superadmin` TIDAK MASUK ke database.**
- Migration database mendefinisikan kolom status sebagai enum:
  ```php
  $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
  ```
  Di SQLite, Laravel membuat tabel dengan constraint DDL: `CHECK ("status" in ('draft', 'active', 'archived'))`.

### Kesimpulan
Database SQLite secara otomatis menerapkan CHECK constraint untuk tipe enum. Walaupun integritas nilai kolom tetap terlindungi dari data korup di level database, hilangnya validasi `Rule::in()` di Form Request mengakibatkan aplikasi mengalami crash fatal (HTTP 500) saat menerima input yang tidak valid.

---

## 5. Hapus withQueryString()

### Prediksi
Jika method `withQueryString()` dihapus dari pagination di controller, query parameter pencarian atau filter (seperti `?q=...&status=...`) akan hilang dari link pagination halaman 2, 3, dan seterusnya. Akibatnya saat pengguna berpindah halaman, filter akan ter-reset dan menampilkan seluruh data tanpa filter.

### Perubahan yang Dilakukan
Menghapus chaining method `withQueryString()` pada method `index()` di [app/Http/Controllers/CourseController.php](file:///C:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/CourseController.php):
```php
// Sebelum
$courses = $query->paginate(10)->withQueryString();

// Sesudah (sementara)
$courses = $query->paginate(10);
```

### Pengujian
1. Membuka URL daftar mata kuliah dengan parameter pencarian dan filter aktif:
   `http://127.0.0.1:8000/courses?q=basis&status=active`
2. Menginspeksi elemen HTML link pagination tombol "2" dan tombol navigasi "Next".

### Hasil Aktual
- Sebelum diubah (kondisi normal dengan `withQueryString()`):
  URL pagination mengarah ke: `http://127.0.0.1:8000/courses?q=basis&status=active&page=2` (parameter `q` dan `status` tetap dipertahankan).
- Setelah `withQueryString()` dihapus:
  URL pagination yang dihasilkan pada tombol pagination hanya: `http://127.0.0.1:8000/courses?page=2`.
- Parameter `q=basis` dan `status=active` hilang total dari tautan halaman berikutnya. Jika tautan tersebut diklik, controller mengeksekusi query tanpa filter pencarian, sehingga daftar mata kuliah kembali menampilkan seluruh mata kuliah secara default.

### Kesimpulan
Method `withQueryString()` bertugas menyalin seluruh query string dari request saat ini ke dalam URL link pagination yang dihasilkan oleh Laravel Paginator. Tanpa method ini, fitur pencarian dan penyaringan data akan rusak begitu pengguna berpindah ke halaman berikutnya.

---

## 6. Ganti redirect() menjadi view()

### Prediksi
Mengganti `return redirect()` menjadi `return view()` pada method `store()` akan melanggar pola arsitektur Post/Redirect/Get (PRG). Jika browser di-refresh setelah submit data berhasil, browser akan meminta konfirmasi pengiriman ulang form (re-submission). Pengiriman ulang ini berpotensi menduplikasi data atau memicu error validasi unik.

### Perubahan yang Dilakukan
Mengubah akhir method `store()` pada [app/Http/Controllers/CourseController.php](file:///C:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/CourseController.php):
```php
// Sebelum
return redirect()->route('courses.index')->with('success', 'Mata kuliah berhasil ditambahkan.');

// Sesudah (sementara)
$courses = Course::with('lecturer')->paginate(10);
return view('courses.index', compact('courses'))->with('success', 'Mata kuliah berhasil ditambahkan.');
```

### Pengujian
1. Melakukan submit form tambah mata kuliah via POST dengan data kode `IF994`.
2. Mengamati respons HTTP pertama.
3. Melakukan refresh halaman (F5) pada browser atau mengirim ulang request POST yang sama.

### Hasil Aktual
1. **Pada submit POST pertama:** Server merespons langsung dengan status **HTTP 200 OK** dan merender view `courses.index` tanpa melakukan redirect 302. URL di address bar browser tetap berada di `http://127.0.0.1:8000/courses` (URL endpoint POST). Data `IF994` berhasil tersimpan di database.
2. **Saat menekan F5 (Refresh):** Browser memunculkan dialog peringatan:
   *"Confirm Form Resubmission — The page that you're looking for used information that you entered. Returning to that page might cause any action you took to be repeated."*
3. **Saat pengiriman ulang dilanjutkan:** Browser mengirimkan kembali paket POST yang sama persis. Namun kali ini sistem merespons dengan **HTTP 302 Found** kembali ke form create karena aturan validasi `'code' => ['unique:courses,code']` menolak kode `IF994` yang sudah ada dari submit pertama.
4. Jika pada skenario form tanpa aturan `unique`, data duplikat yang sama persis akan tersimpan berulang kali setiap kali pengguna me-refresh halaman.

### Kesimpulan
Pola PRG (Post/Redirect/Get) adalah standar penting dalam rekayasa web:
- Request `POST` memproses mutasi data di server,
- Diikuti oleh respon `Redirect` (HTTP 302/303) ke endpoint GET,
- Browser melakukan `GET` untuk menampilkan hasil.
Dengan PRG, address bar browser berada pada method GET yang aman di-refresh (idempotent) tanpa resiko memicu duplikasi data atau resubmission dialog.

---

## 7. Hapus old(...)

### Prediksi
Jika helper `old(...)` dihapus dari atribut `value` elemen input form, maka ketika validasi gagal dan form diredirect kembali, seluruh field input yang dihapus `old()`-nya akan menjadi kosong. Pengguna akan kehilangan data yang sebelumnya sudah diketik dan harus mengetik ulang seluruhnya.

### Perubahan yang Dilakukan
Menghapus penggunaan helper `old('code')` pada input Kode Mata Kuliah di [resources/views/courses/create.blade.php](file:///C:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/resources/views/courses/create.blade.php):
```blade
<!-- Sebelum -->
<input ... name="code" value="{{ old('code') }}" ...>

<!-- Sesudah (sementara) -->
<input ... name="code" value="" ...>
```

### Pengujian
Mengisi form create mata kuliah dengan data:
- Kode Mata Kuliah: `IF999`
- Nama Mata Kuliah: `Pemrograman Web Lanjut`
- SKS: `99` (sengaja tidak valid)
- Dosen: dipilih salah satu
- Status: `draft`
Kemudian menekan tombol "Simpan Mata Kuliah".

### Hasil Aktual
- Validasi gagal pada field SKS dan browser diredirect kembali ke halaman `courses/create`.
- Pesan error "Bobot SKS maksimal 6 SKS." muncul di bawah input SKS.
- Field Nama Mata Kuliah tetap terisi `"Pemrograman Web Lanjut"` karena masih menggunakan `old('name')`.
- Namun field **Kode Mata Kuliah menjadi KOSONG BERSIH (`value=""`)**, meskipun sebelumnya pengguna sudah mengetik `"IF999"`.

### Kesimpulan
Helper `old(...)` memanfaatkan flash session untuk mengembalikan nilai input sebelum validasi gagal ke dalam atribut form HTML. Ketiadaan `old(...)` merusak pengalaman pengguna (UX) secara signifikan karena pengguna dipaksa mengetik ulang data yang sebenarnya sudah benar hanya karena terdapat kesalahan pada field lainnya.

---

## Kesimpulan MINGGU 4

Berdasarkan seluruh hasil penelusuran siklus READ 1–6 dan pengujian eksperimen BREAK 1–7 pada project Laravel KampusLMS, diperoleh kesimpulan komprehensif sebagai berikut:

1. **Siklus Form Gagal dan Validasi:**
   Laravel memproses validasi melalui Form Request (`StoreCourseRequest`) sebelum controller method dijalankan. Kegagalan validasi secara otomatis memicu `ValidationException` yang menghasilkan redirect HTTP 302 ke halaman form sebelumnya, membawa serta error bag (`ViewErrorBag`) dan flash input lama (`_old_input`) melalui session (cookie `laravel_session`). Hal ini memungkinkan direktif Blade `@error` dan helper `old()` menampilkan kembali konteks kesalahan serta data isian pengguna secara mulus.

2. **Keamanan Berlapis (Defense in Depth):**
   - Di lapisan HTTP, Form Request (`$request->validated()`) dan token CSRF (`@csrf`) bertindak sebagai gerbang pertahanan pertama dalam menyaring input dan mencegah pemalsuan request.
   - Di lapisan Model, whitelist `$fillable` bertindak sebagai dinding pertahanan kedua untuk mencegah *Mass Assignment Vulnerability* meskipun controller secara ceroboh memanggil `$request->all()`.
   - Di lapisan Database, foreign key constraint dan check constraint enum menjadi benteng terakhir yang menjaga integritas data relasional dari nilai yatim atau status tidak valid.

3. **Peran Validasi Aplikasi vs Database Constraint:**
   Meskipun database memiliki constraint yang mampu menahan data korup (seperti pada BREAK 3 dan BREAK 4), mengandalkan database saja tanpa validasi aplikasi adalah kesalahan fatal. Ketiadaan validasi aplikasi akan meloloskan request hingga menabrak database dan menghasilkan crash HTTP 500 (`QueryException`). Validasi di Form Request wajib hadir untuk memberikan pesan error yang manusiawi dan mengembalikan pengguna ke form tanpa merusak aplikasi.

4. **Kepatuhan Pola Arsitektur dan UX:**
   - Pola **Post/Redirect/Get (PRG)** mutlak diterapkan pada seluruh operasi manipulasi data untuk mencegah pengiriman ulang form ganda saat refresh halaman.
   - Penerapan helper `old()` dan `withQueryString()` pada pagination menjaga kontinuitas state aplikasi dan menjamin pengalaman pengguna (UX) yang nyaman dan konsisten.
