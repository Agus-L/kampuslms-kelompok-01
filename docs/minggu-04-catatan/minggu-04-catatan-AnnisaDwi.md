# Minggu 4

**Mata Kuliah:** Pemograman Web A <br>
**Nama:** Annisa Dwi Lestari Sonny <br>
**Tanggal:** 24/09/2026

---

## Read

1. Method apa yang menerima request? Di controller mana?

**Jawaban:** 
Request formulir penambahan mata kuliah diterima oleh method **`store`** yang berada di dalam **`CourseController`** (`App\Http\Controllers\CourseController`). Jadi alur ini terbentuk karena pengiriman data memakai method HTTP `POST` menuju endpoint `/courses`, yang ditangani oleh deklarasi resource route `Route::resource('courses', CourseController::class)`.

2. Di titik mana persisnya validasi terjadi — sebelum atau sesudah baris pertama method controller?

**Jawaban:** 
Validasi berlangsung **sebelum** baris pertama kode di dalam method controller dijalankan. 
Alasannya, method `store` memanfaatkan Form Request (`StoreCourseRequest $request`). Ketika permintaan tiba, service container Laravel langsung menginstansiasi Form Request dan mengeksekusi validasi terlebih dahulu (`validateResolved()`). Apabila input terbukti tidak valid (seperti SKS bernilai 99), sistem akan seketika melempar `ValidationException` dan memicu redirect kembali. Akibatnya, baris pertama pada method `store()` sama sekali belum sempat dieksekusi.

3. Ke mana Laravel me-redirect setelah gagal? Siapa yang menentukan tujuannya?

**Jawaban:** 
- **Tujuan Redirect:** User diarahkan kembali ke halaman formulir sebelumnya (*previous URL*), yaitu halaman input penambahan mata kuliah (`/courses/create`).
- **Pihak yang Menentukan:** Framework Laravel menentukannya secara otomatis melalui mekanisme exception handler bawaan yang membaca URL asal dari header HTTP `Referer` atau session `url.previous`. Kendati demikian, pengembang juga memiliki fleksibilitas untuk menentukan tujuannya secara spesifik menggunakan properti `$redirect` / `$redirectTo` atau method `redirectRoute()` di dalam Form Request.

4. Dari mana @error('sks') mengambil pesannya?

**Jawaban:** 
Direktif `@error('sks')` memperoleh pesannya dari flash session yang disuntikkan ke objek **`$errors`** (*View Error Bag*) oleh middleware `ShareErrorsFromSession`. 
Ketika validasi gagal, seluruh pesan kesalahan disimpan sementara di session. Di Blade view, `@error('sks')` memeriksa keberadaan pesan galat pada field `'sks'`. Jika ditemukan, direktif ini menyediakan variabel `$message` yang berisi pesan kesalahan pertama (sesuai kustomisasi pada method `messages()` di `StoreCourseRequest`, seperti peringatan batas nilai SKS).

5. Dari mana old('sks') mengambil nilainya? Berapa lama nilai itu bertahan?

**Jawaban:** 
- **Asal Nilai:** Fungsi helper `old('sks')` mengambil data dari flash session dengan key `_old_input`. Nilai ini secara otomatis disimpan oleh Laravel saat validasi gagal melalui instruksi redirect `withInput()`.
- **Masa Bertahan:** Datanya hanya bertahan selama **satu request berikutnya** (*flash session*). Begitu halaman formulir selesai dimuat kembali dan ditampilkan ke pengguna, data input lama tersebut akan langsung dibersihkan dan dihapus dari session pada request berikutnya.

6. Buka DevTools → Application → Cookies. Temukan cookie session Laravel. Catat namanya.

**Jawaban:** 
Cookie session yang tercatat pada peramban bernama **`laravel_session`**.
Penamaan ini mengikuti pengaturan default pada `config/session.php` yang menggabungkan slug `APP_NAME` dengan akhiran `_session` (pada file `.env`, nilai `APP_NAME=Laravel`). Di samping cookie utama tersebut, terdapat pula cookie pendukung **`XSRF-TOKEN`** yang bertugas untuk proteksi keamanan CSRF. 

**Laravel_session**
eyJpdiI6IlpLdkkxT3NmdGJ4UURqV0FIb1lSalE9PSIsInZhbHVlIjoiRDVaVy9NdUIxc0MvZFd5YjA1SGE0aHJ6QW8wTFRvYUhHSDhVc0VJekFEUndvQU9zMWhYUWdCbndFMDlrcllwMysyeVhMRnZWYkRwS1E5djF6bSs3RStSNnpKM2VIWlorNURNNFVYNTZhRXh4RnMwVzhkbFFsTjFLbTloVVRJUEIiLCJtYWMiOiIyNWRjY2EzNjU2ZDE3YzE1ZGRmOGFkOGY2NWFhYzJkMDcwN2JmMjRkY2UzM2NhYzE1ZGU2Nzg4NTZjNDFlZWEzIiwidGFnIjoiIn0%3D

**XSRF-TOKEN**
eyJpdiI6ImY2VXlWV0tNZW1od1ZPcEkzdEx2aWc9PSIsInZhbHVlIjoiQ3RtVWRjTVdWMDFNeXNvbFZJRkg4Vlp5UjMxM0lTUnpmRjdSaTRKUUtZMGtvZ3o0M0lFQ3R1K0IxalhINUdyRTF2NWRDK3hiNUJTbkJkcHZERTB6SzlFTDJvaDlHcTRGMzZ4bE9jWHNXZ01JbnF2c3BaT0t0SW5IbzVOcWFRUlgiLCJtYWMiOiIzMDJhMDJlMDdiNDBlMzE3YTEyNDkxYTE0YmIxNTU2NTNiYjliNjAyZjJmNjRmNzBjYTUyYWFiNTFiNTRkZDQwIiwidGFnIjoiIn0%3D

---

### Break
| Yang Dirusak | Prediksi | Hasil Sebenarnya |
| :--- | :--- | :--- |
| Hapus `@csrf` dari form, lalu kirim | Request ditolak server karena ketiadaan token CSRF. | Muncul error 419 Page Expired; server menolak request POST yang tidak memiliki token CSRF valid. |
| Ganti `$request->validated()` menjadi `$request->all()`, lalu kirim field liar lewat `curl` | Field tak terduga akan ikut diproses ke model. | Celah *mass assignment* terbuka; field liar yang tidak divalidasi ikut diteruskan ke pembuatan record. |
| Hapus validasi `exists:users,id` pada `lecturer_id`, kirim `lecturer_id=99999` | Data berhasil lolos dari validasi server. | Terjadi kegagalan integritas basis data atau error database *foreign key constraint fails* (data yatim). |
| Hapus validasi `in:...` pada `status`, kirim `status=superadmin` | Nilai status tidak valid dapat terkirim. | Validasi enum jebol; data tersimpan dengan nilai liar di luar opsi (*draft*, *active*, *archived*). |
| Hapus `->withQueryString()`, lakukan pencarian lalu klik halaman 2 | Parameter filter pencarian akan hilang saat ganti halaman. | Tautan pagination halaman 2 hanya memuat `?page=2` tanpa parameter pencarian, sehingga filter pencarian hilang. |
| Ganti `return redirect()` menjadi `return view()` pada `store`, lalu tekan F5 setelah simpan | Browser meminta konfirmasi resubmit dan data tersimpan ganda. | Melanggar pola PRG; muncul peringatan *Confirm Form Resubmission* dan data tersimpan ulang (duplikasi). |
| Hapus `old(...)` dari semua input, lalu kirim form dengan satu kesalahan | Isian form akan kosong kembali saat terjadi error validasi. | Seluruh isian form yang sudah diketik pengguna hilang, mengharuskan pengguna mengetik ulang semuanya dari awal. |
---
