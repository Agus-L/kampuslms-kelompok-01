# Minggu 6

**Mata Kuliah:** Pemograman Web A <br>
**Nama:** Annisa Dwi Lestari Sonny <br>
**Tanggal:** 05/10/2026

---

## Read

1. Jalankan `php artisan install:api`. Baca perubahan yang terjadi di `bootstrap/app.php`.

**Jawaban:** 
```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php', // <-- baris baru ini
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```
Berati seluruh route di `routes/api.php` otomatis diberi prefix `/api` dan middleware grup `api`. 

2. Buat satu endpoint `GET /api/v1/courses` sederhana.

**Jawaban:** 
```php
// routes/api.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CourseController;

Route::prefix('v1')->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/courses', [CourseController::class, 'index']);
    });
});
```

3. Bandingkan dengan `CourseController` versi web yang sudah ada. Tulis di catatan: apa yang sama dan apa yang berbeda di antara keduanya?

**Jawaban:** 

**1. Persamaan:**

| No | Aspek Persamaan | Penjelasan |
| :---: | :--- | :--- |
| 1 | **Model Database** | Keduanya sama-sama berinteraksi dengan Eloquent Model `Course` (dan relasinya seperti `lecturer`, `materials`, `assignments`). |
| 2 | **Logika Query** | Sama-sama memanfaatkan fitur query Eloquent, pagination (`paginate(15)`), serta *eager loading* (`with('lecturer')`) untuk menghindari *N+1 problem*. |
| 3 | **Otorisasi Data** | Keduanya menerapkan logika pemeriksaan hak akses (memastikan dosen hanya mengakses kelasnya atau mahasiswa hanya kelas yang diikutinya). |

**2. Perbedaan:**

| No | Aspek | `CourseController` (Versi Web) | `CourseController` (Versi API) |
| :---: | :--- | :--- | :--- |
| 1 | **Format Respons** | Mengembalikan tampilan HTML/Blade view (`return view('courses.index', ...)`) | Mengembalikan data terstruktur JSON melalui API Resource (`CourseResource`) |
| 2 | **Mekanisme Autentikasi** | **Stateful**: Menggunakan Session & Cookie browser, serta validasi CSRF token | **Stateless**: Menggunakan Token Bearer (Laravel Sanctum) tanpa session |
| 3 | **Kebutuhan Form (Actions)** | Memiliki method `create()` dan `edit()` untuk menampilkan halaman form | Tidak memiliki method `create()` / `edit()`, langsung menangani request data |
| 4 | **Penanganan Error / Validasi** | Melakukan redirect kembali (`back()->withErrors()`) dengan *flash message* | Mengembalikan response JSON dengan HTTP status code yang sesuai (401, 403, 422) |
| 5 | **Penyajian Data** | Seluruh atribut model dapat terkirim ke view Blade | Data disaring secara ketat melalui daftar putih (*whitelist*) pada Resource |

4. Panggil endpoint API tanpa header `Accept: application/json`. Lalu dengan header itu. Catat bedanya.

**Jawaban:** 
* **Tanpa Header `Accept: application/json`:**
  Secara umum Laravel akan menganggap request dari browser dan me-redirect (`302 Found`) ke halaman login web (`/login`).
* **Dengan Header `Accept: application/json`:**
  Laravel mengembalikan respons API murni berstatus **`401 Unauthorized`** dalam format JSON (`{"message": "Unauthenticated."}`).


5. Jalankan `php artisan route:list --path=api`. Cocokkan dengan kontrak di spesifikasi.

**Jawaban:**

```text

\\Berikut ini daftar route yang di hasilkan:

POST            api/v1/assignments ..................................... Api\V1\AssignmentController@store
  PUT|PATCH       api/v1/assignments/{assignment} ....................... Api\V1\AssignmentController@update
  DELETE          api/v1/assignments/{assignment} ...................... Api\V1\AssignmentController@destroy
  GET|HEAD        api/v1/assignments/{assignment}/submissions .. Api\V1\AssignmentSubmissionController@index
  POST            api/v1/assignments/{assignment}/submissions .. Api\V1\AssignmentSubmissionController@store
  POST            api/v1/auth/login ............................................ Api\V1\AuthController@login
  POST            api/v1/auth/logout .......................................... Api\V1\AuthController@logout
  GET|HEAD        api/v1/courses ............................................. Api\V1\CourseController@index
  GET|HEAD        api/v1/courses/{course} ..................................... Api\V1\CourseController@show
  GET|HEAD        api/v1/courses/{course}/assignments .............. Api\V1\CourseAssignmentController@index
  GET|HEAD        api/v1/courses/{course}/materials .................. Api\V1\CourseMaterialController@index  
  GET|HEAD        api/v1/me ....................................................... Api\V1\AuthController@me  
  GET|HEAD        api/v1/notifications ................................. Api\V1\NotificationController@index  
  POST            api/v1/notifications/{id}/read .................. Api\V1\NotificationController@markAsRead  
  PUT             api/v1/submissions/{submission}/grade ............ Api\V1\SubmissionGradeController@update  
```

---

### Break
| Yang Dirusak | Prediksi | Hasil Sebenarnya |
| :--- | :--- | :--- |
| Kembalikan `response()->json(User::all())` di satu endpoint uji | Data user akan keluar mentah dalam format JSON tanpa penyaringan, berpotensi membocorkan data sensitif. | Seluruh record user terekspos mentah ke response JSON beserta hash password bcrypt dan metadata internal karena tidak diproteksi oleh API Resource. |
| Hapus `auth:sanctum` dari grup route, panggil tanpa token | Endpoint privat dapat diakses secara bebas tanpa autentikasi token (menjadi publik). | Berhasil diakses dengan status `200 OK` tanpa token; seluruh data mata kuliah langsung terbuka untuk publik. |
| Panggil endpoint terlindungi dengan token yang sudah dihapus | Request ditolak dengan kode status 401 Unauthorized karena token tidak lagi valid. | Muncul respon `401 Unauthorized` (`{"message": "Unauthenticated."}`) karena record token sudah dihapus dari tabel `personal_access_tokens` saat logout. |
| Login sebagai mahasiswa, panggil `POST /api/v1/assignments` | Request ditolak dengan error 403 Forbidden karena mahasiswa tidak memiliki hak akses untuk membuat tugas. | Menghasilkan status `403 Forbidden` (`{"message": "Anda tidak memiliki akses ke sumber daya ini."}`). Autentikasi token berhasil (bukan 401), namun otorisasi peran ditolak. |
| Hapus eager loading, panggil daftar mata kuliah, lihat Telescope/Debugbar | Data JSON tetap tampil normal status 200 OK, tetapi memicu masalah performa N+1 query. | Respon tetap 200 OK, namun terjadi lonjakan query SQL secara drastis (1 query course + N query lecturer) akibat lazy loading saat serialisasi data. |
| Hapus `throttle` dari login, jalankan 50 percobaan berturut-turut | Semua percobaan login terus dilayani tanpa batas waktu atau pemblokiran, rentan terhadap brute force. | Semua 50 request terus diproses (status 422) tanpa pernah dibatasi oleh status `429 Too Many Requests`, membuka celah serangan tebak kata sandi. |
| Buat pesan login berbeda untuk email salah vs password salah | Memberikan petunjuk apakah email terdaftar di sistem atau tidak (*user enumeration*). | Sistem secara gamblang mengonfirmasi keberadaan akun email yang valid, memudahkan penyerang memetakan target akun untuk serangan lanjutan. Pesan kesalahan seharusnya generik. |

---
