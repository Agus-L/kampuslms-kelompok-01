# Identitas Mahasiswa

## Nama: Agus Liberty Purba
## NIM: 10241005

---

## Read

### 1. Matikan worker. Buat satu tugas baru. Buka tabel `jobs` — apa isinya?
Jawaban :
Ketika worker dinonaktifkan lalu ada tugas baru yang dipublikasikan, tabel `jobs` memuat baris data antrean pekerjaan yang statusnya masih tertunda (*pending*) dan menunggu untuk dieksekusi. Kolom-kolom di dalamnya berisi:

- `id`: Pengenal unik untuk tiap entri job (misalnya: 1)
- `queue`: Kanal antrean penampung (secara bawaan bernilai `default`)
- `payload`: Data serialized job dalam format JSON
- `attempts`: Bernilai `0`, menandakan bahwa pekerjaan ini sama sekali belum pernah dieksekusi oleh worker
- `reserved_at`: Bernilai `null`, mengindikasikan antrean ini belum diambil atau di-*lock* oleh worker aktif mana pun
- `available_at` & `created_at`: Timestamp UNIX yang mencatat kapan tugas ini dijadwalkan tersedia dan waktu pembuatannya

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
Bisa. Informasi mengenai nama Listener maupun ID tugas dapat diidentifikasi secara jelas melalui struktur data di dalam kolom `payload`:
- **Nama Listener**: Tertera pada properti `"displayName"`, yaitu `App\Listeners\SendAssignmentPublishedNotification`.
- **ID Tugas**: Ditemukan pada bagian serialisasi model objek di dalam properti `"command"`, yaitu entitas `App\Models\Assignment` dengan atribut identifier `"id";i:17` (yang menunjukkan ID tugas yang bersangkutan adalah **17**).

```php
// Potongan isi `payload` :
{
 `"displayName":"App\\Listeners\\SendAssignmentPublishedNotification"`
 
 "assignment";...:"App\\Models\\Assignment";s:2:"id";i:17;`
}
```

### 3. Jalankan `php artisan queue:work`, amati keluarannya. Periksa tabel `jobs` lagi.
Jawaban :
Log keluaran terminal saat menjalankan `php artisan queue:work`:
```text
  2026-10-26 13:03:04 App\Listeners\SendAssignmentPublishedNotification ................................... RUNNING
  2026-10-26 13:03:05 App\Listeners\SendAssignmentPublishedNotification ............................. 883.43ms DONE
  2026-10-26 13:03:05 App\Listeners\SendAssignmentPublishedNotification ................................... RUNNING
  2026-10-26 13:03:05 App\Listeners\SendAssignmentPublishedNotification ............................. 358.48ms DONE
  2026-10-26 13:03:05 App\Notifications\AssignmentPublishedNotification ................................... RUNNING
  2026-10-26 13:03:05 App\Notifications\AssignmentPublishedNotification ............................. 226.23ms DONE

dst...
 
```

Hasil pengecekan ulang pada tabel `jobs`:
```php
> DB::table('jobs')->get();

= Illuminate\Support\Collection {#8467
    all: [],
  }
```
Penjelasan:
Setelah worker dijalankan, antrean pekerjaan langsung diproses hingga tuntas (berstatus `DONE`). Karena seluruh tugas selesai dieksekusi tanpa error, Laravel secara otomatis membersihkan baris antrean tersebut dari tabel `jobs`. Dengan demikian, tabel `jobs` kembali kosong (`[]`).

### 4. Periksa tabel `notifications`. Berapa baris yang muncul? Cocok dengan jumlah mahasiswa?
Jawaban :
Secara kumulatif, tabel `notifications` menampung sebanyak 107 baris (akumulasi dari tugas-tugas yang telah dibuat sebelumnya).

Sementara khusus untuk penugasan baru yang baru saja di-*generate* (ID 19 berjudul "tes tes"):
- Jumlah mahasiswa aktif pada kelas tersebut: 20 mahasiswa
- Jumlah record notifikasi baru yang terbentuk: tepat 20 baris
Hal ini membuktikan bahwa setiap mahasiswa yang terdaftar di kelas menerima satu notifikasi secara presisi.

### 5. Baca kolom `data` pada satu notifikasi. Adakah yang tidak seharusnya tersimpan di situ?
Jawaban :
Cuplikan struktur JSON pada kolom `data` notifikasi tugas baru:

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
Hasil analisis:
Tidak ditemukan data sensitif yang bocor. Kolom `data` hanya menyimpan informasi publik/operasional terkait tugas (seperti ID tugas, ID mata kuliah, nama mata kuliah, judul, batas waktu pengumpulan, pesan deskriptif, dan URL tautan). Informasi sensitif atau personal—seperti nilai mahasiswa maupun catatan evaluasi dosen—tidak boleh disimpan di dalam payload notifikasi guna mencegah risiko pelanggaran privasi data.

---

### Break

| Yang Dirusak | Prediksi | Hasil Sebenarnya |
| :--- | :--- | :--- |
| Set `QUEUE_CONNECTION=database`, jangan jalankan worker, buat tugas baru | Tugas berhasil disimpan di UI tanpa kendala, namun pemrosesan notifikasi tertahan di antrean basis data dan mahasiswa belum menerima pemberitahuan apa pun. | Baris pekerjaan baru menumpuk di tabel `jobs` dengan nilai `attempts = 0`; UI berjalan normal tanpa memunculkan error, tetapi tabel `notifications` tidak bertambah karena worker sedang nonaktif. |
| Hapus `implements ShouldQueue` dari Listener, buat tugas untuk 40 mahasiswa | Waktu respons web melambat signifikan dan peramban memuat lama karena pengiriman seluruh 40 notifikasi dieksekusi secara sinkron pada siklus request yang sama. | Terjadi *blocking* pada request HTTP selama beberapa detik ketika menyimpan tugas; waktu respon melonjak tajam sebelum halaman berhasil dialihkan. |
| Ganti `QUEUE_CONNECTION=sync`, ulangi nomor 2 | Proses penyimpanan tugas tetap lambat karena driver `sync` langsung menjalankan antrean saat itu juga di dalam alur request utama tanpa asinkronitas. | Tidak ada rekaman data yang masuk ke tabel `jobs`; seluruh tugas langsung dieksekusi seketika pada siklus request HTTP, menyebabkan waktu respons tetap terhambat. |
| Buat Notification melempar exception, jalankan worker | Worker akan mencoba memproses job beberapa kali sesuai konfigurasi batas percobaan (*tries*). Jika tetap gagal, job akan dipindahkan ke tabel `failed_jobs`. | Konsol worker menampilkan pesan status `FAIL`, mengulang proses sesuai batas *tries*, lalu memindahkan job ke tabel `failed_jobs` lengkap dengan riwayat *exception trace*. |
| Hentikan worker di tengah pemrosesan job | Job yang sedang diproses tidak akan hilang dari tabel `jobs`; status kunciannya akan kedaluwarsa sehingga dapat diambil ulang pada siklus berikutnya. | Kolom `reserved_at` sempat terisi penanda waktu, namun job tidak terhapus dari tabel `jobs` dan otomatis diproses kembali (*available*) setelah worker dijalankan lagi. |
| Masukkan nilai mahasiswa ke payload notifikasi, lalu buka tabel `notifications` sebagai admin | Nilai mahasiswa tersimpan permanen dalam format teks polos (*plaintext*) di database, sehingga rentan terlihat oleh siapa saja yang memiliki akses ke tabel. | Kolom JSON `data` pada tabel `notifications` memuat skor mahasiswa secara terbuka tanpa enkripsi, memicu celah kebocoran data pribadi di tingkat basis data. |
| Panggil endpoint "tandai dibaca" dengan ID notifikasi orang lain | Status notifikasi milik akun lain dapat terubah menjadi telah dibaca akibat endpoint tidak memvalidasi otorisasi kepemilikan data (IDOR). | Request berhasil dengan status HTTP 200 OK dan kolom `read_at` notifikasi milik pengguna lain terisi; terjadi celah keamanan Insecure Direct Object Reference (IDOR). |
| Kalau memakai Reverb: ganti `PrivateChannel` menjadi `Channel`, langganan channel orang lain | Notifikasi realtime dapat disadap secara bebas oleh siapa saja karena kanal siaran telah berubah menjadi publik tanpa proteksi autentikasi. | Pengguna lain (maupun pihak tanpa login) dapat langsung mendengarkan dan membaca muatan siaran notifikasi di browser tanpa autentikasi, menyebabkan kebocoran siaran realtime. |

---
