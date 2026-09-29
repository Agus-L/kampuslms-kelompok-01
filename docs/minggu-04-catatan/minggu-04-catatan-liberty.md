# Catatan Minggu 4
## Identitas Mahasiswa
Nama    : Agus Liberty Purba  
NIM     : 10241005

---

## READ

1. **Method apa yang menerima request? Di controller mana?**  
   **Jawab:**  
   Request penambahan data mata kuliah (`POST /courses`) diterima oleh method `store()` di dalam controller `App\Http\Controllers\CourseController` (`app/Http/Controllers/CourseController.php`).  
   Untuk pembaruan mata kuliah (`PUT/PATCH /courses/{course}`), request diterima oleh method `update()` di controller yang sama (`CourseController`).  
   Sedangkan pada modul Pengguna (`POST /users`), request diterima oleh method `store()` di `App\Http\Controllers\UserController`.

2. **Di titik mana persisnya validasi terjadi — sebelum atau sesudah baris pertama method controller?**  
   **Jawab:**  
   Validasi terjadi **sebelum baris pertama di dalam method controller dieksekusi**.  
   Hal ini dikarenakan method controller menggunakan type-hint Form Request (`StoreCourseRequest $request` atau `UpdateCourseRequest $request`). Dependency Injection container Laravel akan otomatis memanggil method `validateResolved()` pada instance Form Request tersebut ketika me-resolve parameter method controller. Jika validasi gagal, Laravel langsung melempar `ValidationException` dan mengeksekusi redirect response kembali ke halaman sebelumnya, sehingga baris pertama kode di dalam body controller (`$validated = $request->validated();`) sama sekali belum sempat dijalankan.

3. **Ke mana Laravel me-redirect setelah gagal? Siapa yang menentukan tujuannya?**  
   **Jawab:**  
   Laravel me-redirect kembali ke **halaman form sebelumnya (URL asal pengiriman request)**, misalnya ke `http://127.0.0.1:8000/courses/create`.  
   Tujuannya ditentukan oleh **URL referrer / previous URL** yang dicatat di session oleh middleware Laravel (`url()->previous()`), yang diproses secara otomatis oleh class bawaan Form Request / Exception Handler melalui method `redirector->to($this->getRedirectUrl())`. Jika pengembang ingin mengubah arah redirect bawaan ini, dapat mengoverride properti `$redirect` atau `$redirectRoute` di dalam Form Request class tersebut.

4. **Dari mana `@error('sks')` mengambil pesannya?**  
   **Jawab:**  
   `@error('sks')` mengambil pesan galat dari objek penampung error di session, yaitu **`$errors`** (instance dari `Illuminate\Support\MessageBag`), tepatnya melalui method panggilan internal `$errors->has('sks')` dan `$errors->first('sks')`.  
   Pesan spesifiknya berasal dari return array method `messages()` pada file `App\Http\Requests\StoreCourseRequest` (misalnya: `'sks.required' => 'Bobot SKS wajib ditentukan.'` dan `'sks.min' => 'Bobot SKS minimal :min SKS.'`).

5. **Dari mana `old('sks')` mengambil nilainya? Berapa lama nilai itu bertahan?**  
   **Jawab:**  
   `old('sks')` mengambil nilai input yang sebelumnya dikirim dari **Session Flash Data** Laravel (`session()->getOldInput('sks')`).  
   Nilai tersebut hanya bertahan selama **satu siklus request HTTP berikutnya (flash)**. Begitu halaman form di-refresh kembali (F5) atau pengguna berpindah ke halaman lain, data session `_old_input` otomatis dihapus oleh middleware `Illuminate\Session\Middleware\StartSession`.

6. **Buka DevTools → Application → Cookies. Temukan cookie session Laravel. Catat namanya.**  
   **Jawab:**  
   1. XSRF-Token dengan value:
   `eyJpdiI6IjNFaU41R3FQN3dEYThBUk4zSkNmZ2c9PSIsInZhbHVlIjoiT0NBVUZOck02L0Zoc3g3QjRtdXhka1M2YW5XOXp4ZHR1cEpsQW9Rc3hGR1hxUW0zeDVBZUxwekdrQ0xDY0FjSGh3djBjZS9reS8rMVdKTG4yWk50MERuNFJ4V2xNTVFlNG55Z2xaVjVpTUNSUUdmTENKZkR4dE5hbjFvTHdEN0EiLCJtYWMiOiJlM2JhNTUyZGExYTlmODc0MmRjOTIxN2JkZjE0ZmVlNGEyNWRhNDhkZDJiOTI2MTdhMTQ4N2ViNjllN2Q0ZThlIiwidGFnIjoiIn0%3D`
   2. laravel-session dengan value:
   `eyJpdiI6Ik1FdEMwSnh1ekM3aVhxa0hiandqQ1E9PSIsInZhbHVlIjoiS2g2Z01MY3JiZURUWWZjSXNpdmViS3pncTdvMndDbmJFbmpHSnh5NHViWnZxNDZybzN0MVcxU1VHMTlubldrUU1kT1FIQVZNRy9kUjUyN3lXYUNKckFpUlRDNW9naTgxYnFXRHNWTnN4RGRNUVg2VjZyQVpJQkZrMFI0OWNLZXkiLCJtYWMiOiIzNzk0YjRhYTgxM2E5NDQ2ZGM0ZGQ5NWI5NjE5YzY2MjZlNjc2NjliZDE4YzUyM2RjOWM4ZTMxMzUzYmYyZGZlIiwidGFnIjoiIn0%3D`

---

## BREAK

| # | Yang Dirusak | Prediksi | Hasil Sebenarnya |
| :-: | :--- | :--- | :--- |
| **1** | Hapus `@csrf` dari form, lalu kirim | Request form POST akan ditolak oleh middleware penjamin keamanan Laravel karena token CSRF tidak disertakan. | Muncul halaman error **HTTP 419 Page Expired** (`CSRF token mismatch`). Hal ini mencegah serangan *Cross-Site Request Forgery*, di mana pihak ketiga mengecoh pengguna untuk mengirim request berbahaya tanpa izin. |
| **2** | Ganti `$request->validated()` menjadi `$request->all()`, lalu kirim field liar lewat `curl` | Field liar di luar aturan validasi akan ikut terbaca dan berpotensi masuk ke query Eloquent jika kolom tersebut terdaftar di `$fillable`. | **Mass assignment kembali terbuka**. Jika request disusupi field tak terduga, `$request->all()` meloloskan seluruh input tanpa filter ketat aturan rules Form Request. |
| **3** | Hapus validasi `exists:users,id` pada `lecturer_id`, kirim `lecturer_id=99999` | Database akan menolak karena ada relasi Foreign Key constraint, atau terjadi inkonsistensi data jika DB tidak memvalidasi. | Terjadi error database **`Illuminate\Database\QueryException: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails`** (data yatim/orphan dicegah di tingkat database namun membocorkan error SQL 500 karena tidak dicegat di validasi). |
| **4** | Hapus validasi `in:...` pada `status`, kirim `status=superadmin` | Nilai status ilegal berhasil lolos validasi request, lalu aplikasi membentur batasan enum database. | Muncul error level database / SQL **`Illuminate\Database\QueryException`** (*Data truncated for column 'status'* atau *CHECK constraint failed: status in ('draft','active','archived')*). Validasi enum di Form Request jebol. |
| **5** | Hapus `->withQueryString()`, lakukan pencarian lalu klik halaman 2 | Saat mengklik navigasi ke halaman kedua, parameter pencarian/filter di query string URL akan hilang. | **Filter hilang (bug klasik)**. URL halaman 2 berubah menjadi `/courses?page=2` tanpa membawa parameter `q` dan `status`, sehingga daftar kembali menampilkan seluruh mata kuliah tanpa filter. |
| **6** | Ganti `return redirect()` menjadi `return view()` pada `store`, lalu tekan F5 setelah simpan | Saat menekan F5/Refresh, browser akan memunculkan dialog konfirmasi pengiriman ulang form POST, dan data baru yang sama terinput kembali. | **Data ganda (*duplicate data entry*)**. Browser menampilkan *Confirm Form Resubmission*, dan jika dilanjutkan data tersimpan berulang kali. Inilah alasan arsitektur **Post/Redirect/Get (PRG)** wajib digunakan. |
| **7** | Hapus `old(...)` dari semua input, lalu kirim form dengan satu kesalahan | Seluruh inputan yang sudah diketik pengguna di form akan hilang/kosong kembali saat validasi menemukan satu kolom salah. | **Seluruh form ter-reset kosong**. Pengguna dipaksa mengetik ulang semua data dari awal hanya karena satu input salah, membuat User Experience (UX) sangat buruk dan melelahkan. |