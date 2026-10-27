# Minggu 10

**Mata Kuliah:** Pemograman Web A <br>
**Nama:** Annisa Dwi Lestari Sonny <br>
**Tanggal:** 26/10/2026

---

## Read

### 1. Matikan worker. Buat satu tugas baru. Buka tabel `jobs` — apa isinya?
Jawaban :  Tabel jobs berisi baris data antrean pekerjaan yang belum diproses oleh worker. Kolom-kolomnya berisi:

- id: ID unik job (misal: 1)
- queue: nama antrean (default)
- payload: data serialized job dalam format JSON
- attempts: bernilai 0 (menandakan belum pernah dicoba dieksekusi)
- reserved_at: bernilai null (menandakan belum diambil oleh worker)
- available_at & created_at: waktu job dibuat

```json
// Bukti cuplikan data di tabel `jobs`
{
  "id": 1,
  "queue": "default",
  "attempts": 0,
  "reserved_at": null,
  "available_at": 1792989805,
  "created_at": 1792989805
}
```

### 2. Baca kolom `payload`. Bisakah Anda mengenali nama Listener dan ID tugasnya?
Jawaban :
Iya karena, pada Listener dan ID tugas dapat di kenali melalui dalam kolom `payload`:
- Nama Listener: Ditemukan pada properti `"displayName"`, yaitu `App\Listeners\SendAssignmentPublishedNotification`.
- ID Tugas: Ditemukan pada bagian objek serialisasi model di dalam `"command"`, yaitu `App\Models\Assignment` dengan atribut `"id";i:17` (yang berarti ID tugas yang bersangkutan adalah **17**).

```php
// Potongan isi `payload` :
{
 `"displayName":"App\\Listeners\\SendAssignmentPublishedNotification"`
 
 "assignment";...:"App\\Models\\Assignment";s:2:"id";i:17;`
}
```

### 3. Jalankan `php artisan queue:work`, amati keluarannya. Periksa tabel `jobs` lagi.
Jawaban :
Keluaran `php artisan queue:work`:
```text
 2026-10-26 13:03:04 App\Listeners\SendAssignmentPublishedNotification ................................... RUNNING
  2026-10-26 13:03:05 App\Listeners\SendAssignmentPublishedNotification ............................. 883.43ms DONE
  2026-10-26 13:03:05 App\Listeners\SendAssignmentPublishedNotification ................................... RUNNING
  2026-10-26 13:03:05 App\Listeners\SendAssignmentPublishedNotification ............................. 358.48ms DONE
  2026-10-26 13:03:05 App\Notifications\AssignmentPublishedNotification ................................... RUNNING
  2026-10-26 13:03:05 App\Notifications\AssignmentPublishedNotification ............................. 226.23ms DONE

dst...
 
```

Hasil ketika periksa tabel jobs lagi:
```php
> DB::table('jobs')->get();

= Illuminate\Support\Collection {#8467
    all: [],
  }
```
ketika di periksa kembali, tidak ada baris data di tabel `jobs` karena sudah di proses oleh worker. Sehingga laravel otomatis menghapusnya dari tabel jobs.

### 4. Periksa tabel `notifications`. Berapa baris yang muncul? Cocok dengan jumlah mahasiswa?
Jawaban :
Seluruh notifkasi tercatat sebanyak 107 baris (dari tugas-tugas sebelumnya)

Sedangkan khusus untuk tugas yang baru dibuat (ID 19 "tes tes"):
- Jumlah mahasiswa terdaftar di kelas: 20 mahasiswa
- Jumlah baris notifikas yang terkirim: 20 baris

### 5. Baca kolom `data` pada satu notifikasi. Adakah yang tidak seharusnya tersimpan di situ?
Jawaban :
Isi kolom `data` pada notifikasi tugas baru:

```json
{
  "assignment_id": 19,
  "course_id": 1,
  "course_name": "Algoritma dan Pemrograman",
  "title": "tes tes",
  "due_at": "2026-10-26T14:52:00+08:00",
  "message": "Tugas baru 'tes tes' telah dipublikasikan di mata kuliah Algoritma dan Pemrograman.",
  "action_url": "http://localhost/mahasiswa/assignments/19"
}
```
Tidak ada data sensitif yang bocor. Kolom `data` hanya menyimpan metadata umum (ID, judul tugas, deadline, dan link rute). Data sensitif seperti nilai (*grade*) atau feedback pribadi dosen memang tidak boleh disimpan di payload notifikasi untuk mencegah risiko kebocoran data.

---

### Break

| Yang Dirusak | Prediksi | Hasil Sebenarnya |
| :--- | :--- | :--- |
| Set `QUEUE_CONNECTION=database`, jangan jalankan worker, buat tugas baru | Tugas berhasil dibuat di UI tanpa error, tapi job tertahan di antrean dan notifikasi tidak pernah sampai ke mahasiswa. | Job menumpuk di tabel `jobs` dengan `attempts = 0`; UI sukses tanpa ada pesan error, namun tabel `notifications` tetap kosong karena worker tidak aktif. |
| Hapus `implements ShouldQueue` dari Listener, buat tugas untuk 40 mahasiswa | Request pembuatan tugas terasa lambat dan browser loading lama karena pengiriman 40 notifikasi diproses secara sinkron. | Terjadi blocking request HTTP beberapa detik saat simpan tugas; waktu respon melonjak drastis sebelum halaman selesai memuat. |
| Ganti `QUEUE_CONNECTION=sync`, ulangi nomor 2 | Respon pembuatan tugas tetap lambat karena driver `sync` langsung mengeksekusi antrean pada request HTTP yang sama. | Tidak ada data masuk ke tabel `jobs`; job langsung dieksekusi secara sinkron di siklus request utama sehingga waktu respons tetap lambat. |
| Buat Notification melempar exception, jalankan worker | Worker mencoba memproses job beberapa kali sesuai limit *tries*, lalu mencatat error dan memasukkannya ke tabel `failed_jobs`. | Worker menampilkan log error status `FAIL`, mencoba ulang sesuai konfigurasi *tries*, lalu memindahkan job ke tabel `failed_jobs` lengkap dengan exception trace. |
| Hentikan worker di tengah pemrosesan job | Job yang sedang diproses tidak hilang dari tabel `jobs`, melainkan kembali ke antrean setelah batas waktu reservasi habis. | Status job sempat `reserved_at`, namun tidak hilang dari tabel `jobs` dan kembali diproses (*available*) saat worker dinyalakan ulang. |
| Masukkan nilai mahasiswa ke payload notifikasi, lalu buka tabel `notifications` sebagai admin | Nilai mahasiswa tersimpan permanen dalam format teks biasa di database dan rentan dilihat oleh pihak yang tidak berhak. | Kolom JSON `data` di tabel `notifications` memuat nilai secara *plaintext*, memicu kebocoran data sensitif mahasiswa di level database. |
| Panggil endpoint "tandai dibaca" dengan ID notifikasi orang lain | Status notifikasi milik pengguna lain dapat ditandai sudah dibaca karena endpoint tidak memverifikasi kepemilikan notifikasi (IDOR). | Request berhasil (200 OK) dan kolom `read_at` notifikasi orang lain terisi; terjadi celah keamanan IDOR akibat absennya otorisasi user. |
| Kalau memakai Reverb: ganti `PrivateChannel` menjadi `Channel`, langganan channel orang lain | Notifikasi realtime dapat disadap oleh siapa saja karena channel siaran bersifat publik tanpa mekanisme otentikasi. | Pengguna lain (atau siapa pun tanpa login) bisa langsung menerima dan membaca isi notifikasi di browser tanpa otentikasi, memicu kebocoran siaran realtime. |

---
