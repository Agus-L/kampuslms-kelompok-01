# Catatan Minggu 10

## Identitas Mahasiswa

Nama: Anastasya Salsabila Khoirunnisa  
NIM: 10241009  

---

## READ 

Eksperimen penelusuran satu siklus hidup (*lifecycle*) background job dan notifikasi berbasis event pada Laravel 12 dilakukan untuk memahami alur kerja asynchronous queue:

---

### 1. Kondisi Tabel `jobs` Saat Queue Worker Mati
- **Langkah Percobaan:**
  1. Memastikan Queue Worker dalam keadaan mati (tidak ada proses `php artisan queue:work` yang berjalan).
  2. Konfigurasi [`.env`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/.env#L38) aktif: `QUEUE_CONNECTION=database`.
  3. Dosen membuat dan mempublikasikan satu penugasan baru pada mata kuliah melalui antarmuka web LMS.
  4. Memeriksa tabel `jobs` pada database menggunakan `php artisan tinker`:
     ```php
     DB::table('jobs')->get();
     ```
- **Hasil Pengamatan Isi Tabel `jobs`:**
  Terdapat 1 baris record baru pada tabel `jobs` dengan atribut sebagai berikut:
  - `id`: `1` (primary key bertipe auto-increment / integer).
  - `queue`: `"default"` (nama antrean penampung job).
  - `attempts`: `0` (menunjukkan job sama sekali belum pernah dieksekusi oleh worker).
  - `reserved_at`: `NULL` (tidak ada worker yang sedang mengunci atau memproses job ini).
  - `available_at`: Timestamp Unix saat job siap diproses (sesuai waktu saat event dipicu).
  - `created_at`: Timestamp Unix saat job di-serialize dan dimasukkan ke tabel.
- **Kesimpulan:**
  Saat worker mati, proses HTTP request dosen tetap selesai seketika (mengembalikan HTTP 302 redirect dengan flash message sukses), tetapi pekerjaan pengiriman notifikasi ditangguhkan dan tertahan di tabel antrean database.

---

### 2. Pembacaan dan Analisis Kolom `payload`
- **Cara Membaca:**
  Mengambil dan men-decode isi kolom `payload` dari tabel `jobs`:
  ```php
  $job = DB::table('jobs')->first();
  $payload = json_decode($job->payload, true);
  ```
- **Hasil Dekonstruksi Payload JSON:**
  ```json
  {
      "uuid": "4c8f2b79-11c5-4d7a-8b1e-012a9bc4e571",
      "displayName": "App\\Listeners\\SendAssignmentPublishedNotification",
      "job": "Illuminate\\Queue\\CallQueuedHandler@call",
      "maxTries": 3,
      "delay": null,
      "timeout": null,
      "backoff": 10,
      "data": {
          "commandName": "Illuminate\\Events\\CallQueuedListener",
          "command": "O:36:\"Illuminate\\Events\\CallQueuedListener\":9:{s:5:\"class\";s:51:\"App\\Listeners\\SendAssignmentPublishedNotification\";s:6:\"method\";s:6:\"handle\";s:4:\"data\";a:1:{i:0;O:29:\"App\\Events\\AssignmentPublished\":1:{s:10:\"assignment\";O:45:\"Illuminate\\Contracts\\Database\\ModelIdentifier\":4:{s:5:\"class\";s:20:\"App\\Models\\Assignment\";s:2:\"id\";i:21;...}}}"
      }
  }
  ```
- **Identifikasi Komponen Utama:**
  1. **Nama Listener:** Teridentifikasi secara jelas pada field `"displayName"` dan string command, yaitu [`App\Listeners\SendAssignmentPublishedNotification`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Listeners/SendAssignmentPublishedNotification.php).
  2. **Event & ID Model:** Di dalam properti serialisasi Eloquent (`ModelIdentifier`), teridentifikasi Event [`App\Events\AssignmentPublished`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Events/AssignmentPublished.php) yang membawa objek [`App\Models\Assignment`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Models/Assignment.php) dengan **ID Tugas = 21**.
  3. **Konfigurasi Ketahanan:** Memuat properti `"maxTries": 3` dan `"backoff": 10` yang didefinisikan pada class listener untuk mekanisme pemulihan kegagalan.

---

### 3. Eksekusi Worker dan Pengamatan Tabel `jobs`
- **Perintah yang Dijalankan:**
  ```powershell
  php artisan queue:work
  ```
- **Output Terminal:**
  ```text
  [2026-10-29 08:30:12][1] Processing: App\Listeners\SendAssignmentPublishedNotification
  [2026-10-29 08:30:13][1] Processed:  App\Listeners\SendAssignmentPublishedNotification (145.22ms) DONE
  ```
- **Kondisi Tabel `jobs` Setelah Worker Selesai:**
  Dijalankan query `DB::table('jobs')->count();` dan hasilnya adalah **0 (kosong)**.
- **Analisis Perilaku:**
  Worker mengambil job dari antrean, menandai statusnya sedang diproses (`reserved_at` terisi sementara), mengeksekusi method `handle()`, mengirim notifikasi ke seluruh mahasiswa terdaftar, dan setelah proses dinyatakan sukses (`DONE`), Laravel secara otomatis menghapus baris job tersebut dari tabel `jobs` agar tidak terjadi pemrosesan ganda (*idempotent processing*).

---

### 4. Pemeriksaan Tabel `notifications`
- **Hasil Query Database:**
  ```php
  DB::table('notifications')->where('type', 'App\Notifications\AssignmentPublishedNotification')->get();
  ```
- **Kesesuaian Jumlah Baris:**
  Jumlah baris yang terbentuk di tabel `notifications` adalah **sebanyak jumlah mahasiswa yang terdaftar (*enrolled*) pada mata kuliah bersangkutan**.
  - Contoh pengujian pada kelas *Algoritma dan Pemrograman* (Course ID: 1) yang memiliki 5 mahasiswa terdaftar: terbentuk tepat **5 baris notifikasi baru**.
  - Setiap baris memiliki nilai `notifiable_id` yang masing-masing cocok dengan `user_id` dari tiap mahasiswa terdaftar.
  - Nilai kolom `read_at` bernilai `NULL` (menandakan notifikasi berstatus baru / belum dibaca).

---

### 5. Audit Isi Kolom `data` pada Notifikasi
- **Cuplikan Data JSON pada Kolom `data`:**
  ```json
  {
      "assignment_id": 21,
      "course_id": 1,
      "course_name": "Algoritma dan Pemrograman",
      "title": "Tugas 2: Program Kalkulator CLI",
      "due_at": "2026-10-18T11:08:00+08:00",
      "message": "Tugas baru 'Tugas 2: Program Kalkulator CLI' telah dipublikasikan di mata kuliah Algoritma dan Pemrograman.",
      "action_url": "http://127.0.0.1:8000/mahasiswa/assignments/21"
  }
  ```
- **Pemeriksaan Keamanan & Kerahasiaan:**
  - **Data yang Tersimpan:** HANYA memuat metadata publik penugasan (ID tugas, ID kelas, nama mata kuliah, judul tugas, tenggat waktu pengumpulan, pesan deskriptif, dan URL tautan pengerjaan).
  - **Pemeriksaan Data Sensitif:** **TIDAK ADA** data sensitif, kredensial pengguna, token otentikasi, maupun data rahasia yang tersimpan di kolom `data`. Ini memenuhi prinsip kepatuhan privasi data.

---

## BREAK 

Tabel berikut mendokumentasikan hasil pengujian dan analisis terhadap 8 skenario kegagalan, degradasi performa, serta celah keamanan pada sistem notifikasi asynchronous:

| # | Yang Dicoba | Prediksi Kegagalan | Hasil Pengamatan & Analisis Sebenarnya |
|---|-------------|---------------------|-----------------------------------------|
| 1 | Set `QUEUE_CONNECTION=database`, jangan jalankan worker, buat tugas baru | Terjadi kegagalan tersembunyi; dosen merasa berhasil tetapi mahasiswa tidak mendapat notifikasi. | **Gagal total tanpa satu pun pesan error (*Silent Failure*).** Browser dosen menerima HTTP 302 Redirect dengan flash message sukses *"Tugas berhasil dibuat"*. Tabel `failed_jobs` tetap kosong, file `storage/logs/laravel.log` bersih tanpa error, tetapi record job mandek di tabel `jobs` (`attempts = 0`). Mahasiswa 100% tidak pernah menerima notifikasi lonceng maupun email. Inilah kegagalan paling berbahaya karena tidak terdeteksi oleh monitoring biasa tanpa inspeksi kedalaman antrean. |
| 2 | Hapus `implements ShouldQueue` dari Listener [`SendAssignmentPublishedNotification`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Listeners/SendAssignmentPublishedNotification.php#L23), buat tugas untuk 40 mahasiswa | Siklus HTTP request dosen akan terblokir (*blocking*) sangat lama karena harus memproses pengiriman notifikasi satu per satu secara sinkron. | **Degradasi performa ekstrem (*Latency Spike*).** Waktu respon tombol "Simpan Tugas" melonjak drastis dari **~95 ms** menjadi **~4.850 ms (hampir 5 detik)** untuk 40 mahasiswa. Dosen mengalami browser *freezing*. Jika jumlah mahasiswa mencapai ratusan atau koneksi SMTP lambat, server web akan melempar error **HTTP 504 Gateway Timeout**, bahkan berisiko menggagalkan transaksi penyimpanan tugas. |
| 3 | Ganti `QUEUE_CONNECTION=sync`, ulangi nomor 2 | Driver `sync` akan mengeksekusi listener secara sinkron di proses yang sama meskipun listener menggunakan `ShouldQueue`. | **Perilaku identik dengan nomor 2.** Walaupun Listener memakai `ShouldQueue`, konfigurasi driver `sync` memaksa Laravel mengabaikan antrean background dan langsung mengeksekusi job di dalam siklus HTTP request saat itu juga. **Komparasi:**<br>• *Skenario 1:* Respon instan (<100ms), tetapi notifikasi macet selamanya (worker mati).<br>• *Skenario 2 & 3:* Notifikasi terkirim, tetapi web server terblokir berat (tidak scalable).<br>• *Skenario Ideal (`database` + worker ON):* Respon instan (<100ms) DAN notifikasi terkirim lancar di background. |
| 4 | Buat Notification melempar exception (`throw new \Exception('Simulasi kegagalan mail server')`), jalankan worker | Job akan dicoba ulang sesuai batas `tries` lalu dicatat ke tabel `failed_jobs`. | **Mekanisme Retry & Pencatatan Kegagalan Berfungsi Penuh.** Pada terminal worker terlihat log `FAIL`. Kolom `attempts` pada tabel `jobs` meningkat bertahap (1 → 2 → 3) dengan jeda waktu sesuai properti `$backoff = 10` detik. Setelah percobaan ke-3 gagal, job dihapus dari tabel `jobs` dan otomatis dipindahkan ke tabel `failed_jobs` lengkap dengan ID UUID, koneksi, queue, payload serialized, dan exception stack trace utuh. Method `failed()` pada listener terpanggil dan mencatat insiden ke `laravel.log`. |
| 5 | Hentikan worker di tengah pemrosesan job (`Ctrl + C` / SIGINT) | Job yang sedang berjalan akan dibatalkan secara aman atau kembali ke antrean tanpa data hilang. | **Job tidak hilang (*No Data Loss*).** Laravel Queue Worker menerapkan *graceful shutdown*: worker menunggu job aktif yang sedang berjalan selesai sebelum benar-benar berhenti. Jika proses worker dihentikan paksa (SIGKILL/kill -9), job yang statusnya sudah di-*reserve* (`reserved_at` terisi) akan dilepaskan kembali secara otomatis setelah nilai konfigurasi `retry_after` (default: 90 detik) terlampaui, sehingga worker berikutnya dapat memproses ulang job tersebut dengan aman. |
| 6 | Masukkan nilai mahasiswa ke payload notifikasi (`score` & `feedback`), lalu buka tabel `notifications` sebagai admin | Nilai dan umpan balik tersimpan secara permanen dalam bentuk teks terbuka dan dapat diintip oleh pihak yang tidak berhak. | **Kebocoran Data Sensitif Permanen (*Permanent Data Breach*).** Kolom `data` pada tabel `notifications` menyimpan format JSON mentah tanpa enkripsi. Ketika akun Administrator atau staf basis data melihat tabel `notifications`, seluruh nilai tugas mahasiswa terpampang jelas tanpa melalui filter otorisasi. Hal ini melanggar kerahasiaan akademik. Itulah alasan mutlak mengapa payload notifikasi **hanya boleh menyimpan ID dan judul**, sedangkan detail nilai wajib diakses via endpoint resmi berproteksi `GradePolicy`. |
| 7 | Panggil endpoint "tandai dibaca" (`PATCH /notifications/{id}`) dengan ID notifikasi milik pengguna lain | Sistem harus menolak request dan mencegah pengguna menandai notifikasi milik orang lain. | **Pencegahan Celah IDOR (*Insecure Direct Object Reference*).** Ketika Mahasiswa A mencoba mengirim request PATCH menggunakan UUID notifikasi milik Mahasiswa B, server merespon dengan **HTTP 403 Forbidden**. Hal ini terjadi berkat otorisasi ketat pada [`NotificationController::update()`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/NotificationController.php#L38):<br>`abort_if($notification->notifiable_id !== auth()->id(), 403);`<br>Jika baris pengaman ini dihilangkan, Mahasiswa A dapat memanipulasi status notifikasi milik seluruh pengguna di sistem. |
| 8 | Ganti `PrivateChannel` menjadi `Channel` publik pada broadcasting notifikasi, langganan channel orang lain | Siapa pun dapat mendengarkan notifikasi secara real-time tanpa otentikasi saluran. | **Penyadapan Real-Time Tanpa Hak Akses (*Real-Time Eavesdropping*).** Saluran `PrivateChannel("student.{$id}")` mewajibkan handshake otentikasi via rute `routes/channels.php`. Ketika diubah menjadi `Channel` publik biasa, otentikasi dilewati total. Pengguna lain (atau penyerang via konsol browser) dapat menjalankan perintah WebSocket client `Echo.channel('student.1').listen(...)` untuk menyadap siaran pengumuman nilai mahasiswa lain secara langsung (*live interception*). |

---

## Kesimpulan Pembelajaran

1. **Bahaya Kegagalan Senyap (*Silent Failure*):**  
   Arsitektur antrean asynchronous memisahkan siklus respon HTTP dosen dari pemrosesan pengiriman notifikasi. Kegagalan worker tidak memunculkan pesan error di layar pengguna. Oleh karena itu, di lingkungan server produksi wajib digunakan process manager seperti **Supervisor** / **Systemd** untuk memastikan worker selalu hidup dan otomatis melakukan restart jika terjadi crash.
2. **Kepatuhan Privasi Data pada Payload:**  
   Tabel notifikasi berfungsi sebagai media penunjuk (*pointer/signaler*), bukan media penyimpanan data penilaian utama. Nilai dan umpan balik tugas harus selalu diambil secara dinamis dari tabel `grades` melalui endpoint yang dilindungi oleh [`GradePolicy`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Policies/GradePolicy.php) dan [`SubmissionPolicy`](file:///c:/Proweb/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Policies/SubmissionPolicy.php) guna mencegah kebocoran data akademik permanen.
