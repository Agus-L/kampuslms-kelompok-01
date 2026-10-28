# Catatan Praktikum Minggu 10: Realtime, Event, Queue, dan Notifikasi
**Nama:** Aliya Labibah  
**NIM:** 10241007  
**Mata Kuliah:** Pemrograman Web (KampusLMS | Laravel 12)  

---

## READ — Ikuti Satu Job (30 Menit)

### 1. Matikan worker. Buat satu tugas baru. Buka tabel `jobs` — apa isinya?
Saat saya memastikan worker (`php artisan queue:work`) tidak berjalan di terminal lalu membuat tugas baru melalui akun dosen di mata kuliah **Struktur Data**, tugas tersebut langsung tersimpan di database. 

Ketika saya memeriksa tabel `jobs` di database, di dalamnya muncul **1 baris pekerjaan antrean baru** yang sedang tertahan karena belum ada worker yang memprosesnya. Detail kolomnya:
* `id`: Nomor urut antrean tugas (angka unik).
* `queue`: Bernama `default` (jalur antrean standar bawaan Laravel).
* `payload`: Teks JSON panjang yang menyimpan informasi detail mengenai tugas apa yang harus dikerjakan di latar belakang.
* `attempts`: Bernilai `0` (menandakan pekerjaan ini masih baru dan belum pernah dicoba dieksekusi sama sekali oleh worker).
* `reserved_at`: Bernilai `null` (artinya belum ada proses worker yang mengunci atau mengambil pekerjaan ini).
* `available_at` & `created_at`: Waktu saat pekerjaan ini pertama kali masuk ke antrean database.

---

### 2. Baca kolom `payload`. Bisakah Anda mengenali nama Listener dan ID tugasnya?
**Ya, saya bisa mengenalinya dengan jelas.** 

Meskipun isi kolom `payload` berupa teks JSON yang cukup panjang karena diserialisasi oleh Laravel, informasi pentingnya tetap terbaca:
* **Nama Listener:** Terlihat jelas pada bagian:
  ```json
  "displayName": "App\\Listeners\\SendAssignmentPublishedNotification"
  ```
  Ini menunjukkan kelas listener yang bertugas membuat notifikasi untuk para mahasiswa.
* **ID Tugas (*Assignment ID*):** Terlihat di dalam properti `"data"` -> `"command"`, di mana model `Assignment` disimpan lengkap beserta ID tugas yang baru saja saya buat di mata kuliah Struktur Data (misalnya tugas ID `32`).

Artinya, Laravel membungkus nama listener dan data tugas ke dalam format JSON ini agar nantinya saat worker aktif, worker tahu persis pekerjaan apa yang harus dijalankan dan data mana yang harus diproses.

---

### 3. Jalankan `php artisan queue:work`, amati keluarannya. Periksa tabel `jobs` lagi.
Ketika saya menyalakan worker dengan menjalankan perintah `php artisan queue:work` di terminal, worker langsung mendeteksi antrean yang tadi menggantung.

* **Keluaran di Terminal:**
  ```text
  App\Listeners\SendAssignmentPublishedNotification ... RUNNING
  App\Listeners\SendAssignmentPublishedNotification ... DONE
  ```
  Worker mengambil pekerjaan tersebut (`RUNNING`), memproses pembuatan notifikasi untuk seluruh mahasiswa di kelas, lalu menandainya selesai (`DONE`).
* **Kondisi Tabel `jobs` Setelah Selesai:**
  Saat saya periksa kembali tabel `jobs` di database, baris antrean tersebut sudah **hilang atau kosong kembali (0 baris)**. Hal ini terjadi karena Laravel secara otomatis menghapus job yang sudah berhasil diproses agar tabel antrean tetap bersih dan pekerjaan tidak dijalankan berulang kali.

---

### 4. Periksa tabel `notifications`. Berapa baris yang muncul? Cocok dengan jumlah mahasiswa?
Saat saya membuka tabel `notifications` di database setelah worker selesai bekerja, muncul baris-baris notifikasi baru dengan tipe `App\Notifications\AssignmentPublishedNotification`.

* **Jumlah Baris:** Tepat muncul **18 baris baru**.
* **Kecocokan dengan Jumlah Mahasiswa:** Jumlah 18 baris ini **cocok 100% dengan jumlah mahasiswa yang terdaftar di kelas Struktur Data kami (total 18 mahasiswa)**, termasuk akun Andi Pratama dan Siti Nurhaliza. Setiap baris memiliki `notifiable_id` yang sesuai dengan ID masing-masing mahasiswa di kelas tersebut.

---

### 5. Baca kolom `data` pada satu notifikasi. Adakah yang tidak seharusnya tersimpan di situ?
Ketika saya membuka salah satu kolom `data` pada baris notifikasi yang terkirim, isinya berupa teks JSON seperti ini:
```json
{
  "assignment_id": 32,
  "course_id": 2,
  "course_name": "Struktur Data",
  "title": "tugasa ya",
  "due_at": "2026-10-28T20:05:00+08:00",
  "message": "Tugas baru 'tugasa ya' telah dipublikasikan di mata kuliah Struktur Data.",
  "action_url": "http://localhost/mahasiswa/assignments/32"
}
```

* **Analisis Keamanan Data:**
  Menurut saya, isi kolom `data` ini sudah aman dan **tidak ada data terlarang atau data sensitif yang tersimpan**. Isinya hanya memuat informasi umum yang memang perlu dilihat mahasiswa pada lonceng notifikasi (seperti nama mata kuliah, judul tugas, batas waktu, dan URL rute). Sistem sama sekali **tidak menyimpan data rahasia** seperti password, token sesi, maupun nilai tugas di tabel ini. Tautan `action_url` pun aman karena tetap mewajibkan mahasiswa untuk login sebelum bisa membuka tugasnya.

---
---

## BREAK — Delapan Kerusakan (50 Menit)

| # | Yang dicoba | Prediksi | Hasil Sebenarnya |
|---|-------------|----------|------------------|
| 1 | Set `QUEUE_CONNECTION=database`, jangan jalankan worker, buat tugas baru | Tugas berhasil tersimpan di sistem, namun tidak ada notifikasi yang terkirim ke mahasiswa karena job tertahan di antrean database tanpa worker. | **Saat saya membuat tugas baru tanpa menjalankan worker, tugas berhasil dibuat secara normal di sisi dosen dan muncul di daftar tugas mahasiswa, tetapi notifikasinya tidak pernah terkirim (*silent failure*).** Ketika saya periksa, dosen tidak menerima pesan error apa pun dan mahasiswa bisa melihat tugas baru di halamannya karena data tugas sudah masuk ke database. Namun, lonceng notifikasi mahasiswa tetap bernilai 0. Saat saya mengecek tabel `jobs` di database, data pengiriman notifikasi ternyata tertahan dan tidak pernah diproses karena tidak ada worker (`php artisan queue:work`) yang aktif. Eksperimen ini menunjukkan bahaya *silent failure*, di mana sistem tampak berhasil dari luar padahal pengiriman notifikasi mahasiswa macet total. |
| 2 | Hapus `implements ShouldQueue` dari Listener, buat tugas untuk 40 mahasiswa | Waktu respon server saat membuat tugas menjadi sangat lambat (loading lama) karena server dipaksa memproses 40 notifikasi secara sinkron dalam satu siklus request HTTP. | **Saat saya menghapus `implements ShouldQueue` dari Listener, tugas tetap berhasil dibuat tanpa worker, tetapi proses pengiriman notifikasinya dipaksa berjalan langsung di tempat (sinkron).** Ketika saya klik simpan, web server harus menyelesaikan pembuatan notifikasi ke seluruh mahasiswa terlebih dahulu sebelum halaman dosen berhasil berpindah (*redirect*). Pada pengujian lokal di laptop, jeda waktu belum terlalu terasa lambat karena kelas contoh kami hanya berisi ~18 mahasiswa dan email hanya dicatat ke file teks log (`MAIL_MAILER=log`). Namun pada sistem kampus sungguhan (dengan 40+ mahasiswa dan pengiriman email SMTP nyata), halaman dosen akan tertahan (*loading/freeze*) puluhan detik atau bahkan terkena *timeout*. Hal ini membuktikan bahwa `ShouldQueue` sangat penting agar proses simpan di sisi dosen tetap cepat tanpa harus menunggu proses kirim notifikasi selesai. |
| 3 | Ganti `QUEUE_CONNECTION=sync`, ulangi nomor 2 | Meskipun Listener memakai `ShouldQueue`, aplikasi tetap terasa lambat karena driver `sync` mengeksekusi seluruh job secara langsung saat itu juga tanpa memasukkannya ke tabel antrean. | **Saat saya mengubah konfigurasi `.env` menjadi `QUEUE_CONNECTION=sync` lalu mencoba membuat tugas baru, proses penyimpanan di halaman dosen mengalami jeda *loading* yang lama (bahkan sempat memicu error batas waktu 30 detik `Maximum execution time exceeded`), dan tabel `jobs` di database tetap kosong.** Notifikasi ke mahasiswa memang langsung terkirim tanpa perlu menjalankan worker di terminal, namun hal itu terjadi karena driver `sync` memaksa seluruh pembuatan notifikasi dan format email dieksekusi langsung di tempat saat tombol simpan diklik. Akibatnya, tulisan `implements ShouldQueue` di file Listener menjadi tidak berpengaruh karena sistem mengabaikan antrean latar belakang. Eksperimen ini membuktikan bahwa tanpa antrean nyata (seperti database), proses web dosen akan terbebani dan rawan mengalami keterlambatan respon atau *crash*. |
| 4 | Buat Notification melempar exception, jalankan worker | Worker akan mencoba mengeksekusi job berulang kali sesuai batas `tries`. Setelah jatah percobaan habis, job dipindahkan ke tabel `failed_jobs`. | **Saat saya menambahkan `throw new \Exception(...)` pada fungsi `toArray()` di Notification lalu menjalankan worker, halaman dosen tetap berhasil menyimpan tugas tanpa error, sementara terminal worker mencatat status `FAIL` dan mencoba mengulanginya sebanyak 3 kali.** Di terminal `queue:work`, tugas pendataan awal selesai sukses (`DONE`), namun pengiriman notifikasi mahasiswa menabrak error sehingga statusnya berganti dari `RUNNING` menjadi `FAIL`. Worker menerapkan jeda 10 detik (`$backoff = 10`) dan otomatis mencoba ulang hingga batas 3 kali percobaan (`$tries = 3`). Setelah gagal permanen, job tersebut dipindahkan ke tabel `failed_jobs` (terbukti muncul saat diperiksa lewat perintah `php artisan queue:failed`). Eksperimen ini membuktikan bahwa kegagalan di latar belakang berhasil diisolasi dengan baik, sehingga halaman web dosen tetap aman dan tidak mengalami error sama sekali. |
| 5 | Hentikan worker di tengah pemrosesan job | Job tidak hilang; data transaksi akan di-rollback atau dikembalikan ke antrean tabel `jobs` untuk diproses ulang saat worker hidup kembali. | **Saat saya menambahkan jeda buatan `sleep(10)` pada Listener lalu mematikan worker secara paksa menggunakan `Ctrl + C` ketika statusnya masih `RUNNING`, tugas tersebut terbukti tidak hilang dan tetap tersimpan di tabel `jobs`.** Penambahan jeda `sleep(10)` sengaja dilakukan untuk memperlambat proses eksekusi agar saya sempat menghentikan terminal worker di tengah jalan. Karena proses diputus sebelum tuntas, worker belum sempat mengirimkan laporan selesai ke database, sehingga sistem tidak menghapus tugas tersebut dan mempertahankannya di dalam tabel antrean `jobs`. Ketika worker dijalankan kembali via `php artisan queue:work`, tugas yang menggantung tersebut otomatis diambil ulang dan diproses sampai tuntas (`DONE`). Eksperimen ini membuktikan keandalan antrean berbasis database (*fault-tolerant* / *at-least-once delivery*), di mana tugas antrean tetap aman dan tidak akan lenyap meskipun server mati mendadak di tengah pemrosesan. |
| 6 | Masukkan nilai mahasiswa ke payload notifikasi, lalu buka tabel `notifications` sebagai admin | Nilai mahasiswa akan terekspos dalam bentuk teks biasa tanpa enkripsi di kolom `data` tabel `notifications`, sehingga privasi nilai mahasiswa bocor permanen. | **Saat saya menambahkan data `'score'` dan `'feedback'` ke dalam method `toArray()` pada [SubmissionGradedNotification.php](file:///d:/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Notifications/SubmissionGradedNotification.php#L79-L80) lalu menjalankan worker saat dosen memberi nilai, nilai mahasiswa tersimpan secara terbuka di tabel `notifications`.** Ketika saya memeriksa kolom `data` pada tabel `notifications` di database, angka nilai dan pesan evaluasi mahasiswa terlihat jelas dalam bentuk teks mentah (*plaintext JSON*). Hal ini membuktikan terjadinya celah kebocoran data akademik (*Information Disclosure*). Karena notifikasi tersimpan permanen di database, siapa pun yang memiliki akses database dapat membaca nilai rahasia mahasiswa tanpa melewati pemeriksaan otorisasi Policy. Eksperimen ini menegaskan bahwa notifikasi sebaiknya tidak langsung menampilkan angka nilai, melainkan hanya memberikan link agar mahasiswa membuka halaman tugasnya sendiri yang sudah aman dan terkunci login. |
| 7 | Panggil endpoint "tandai dibaca" dengan ID notifikasi orang lain | Bila tidak ada pemeriksaan kepemilikan, seorang mahasiswa dapat menandai dibaca notifikasi milik mahasiswa lain hanya dengan mengganti UUID pada URL (kerentanan IDOR). | **Saat saya menonaktifkan baris validasi kepemilikan di [NotificationController.php](file:///d:/kampuslms-kelompok-01/kampuslms-kelompok-01/app/Http/Controllers/NotificationController.php#L34), saya login sebagai Siti Nurhaliza dan berhasil menandai notifikasi milik Andi Pratama (UUID: `c729a5b5-0eec-4fd5-8f3d-5737ba8aa10f`) menjadi sudah dibaca.** Hal ini saya lakukan dengan mengganti target UUID pada form aksi tombol di browser melalui Inspect Element. Karena baris pengaman dimatikan, server memproses perubahan tersebut tanpa memeriksa identitas pemilik asli notifikasi, sehingga terjadi kerentanan IDOR (*Insecure Direct Object Reference*). Namun setelah baris `abort_unless($notification->notifiable_id === $request->user()->id, 403)` diaktifkan kembali, server langsung memblokir aksi tersebut dengan respon **HTTP 403 Forbidden** dan pesan *"Akses ditolak: notifikasi bukan milik Anda"*. Eksperimen ini membuktikan pentingnya validasi kepemilikan agar pengguna lain tidak dapat memanipulasi data yang bukan haknya. |
| 8 | Kalau memakai Reverb: ganti `PrivateChannel` menjadi `Channel`, langganan channel orang lain | Menggunakan channel publik menyebabkan aliran siaran WebSocket terbuka untuk umum, sehingga mahasiswa lain dapat menyadap notifikasi realtime rekan sekelasnya tanpa perlu login atau otorisasi. | **Proyek KampusLMS Kelompok 01 tidak menggunakan Laravel Reverb maupun siaran WebSocket, melainkan hanya memakai notifikasi database biasa yang dicek berkala lewat Livewire (`wire:poll.30s`), sehingga skenario perubahan channel ini tidak ada di sistem kami.** Namun secara konsep sesuai pengandaian pada soal: jika suatu sistem memakai Reverb lalu siaran diubah dari `PrivateChannel` menjadi `Channel` publik, perlindungan otorisasi akan hilang. Akibatnya, mahasiswa lain bisa menyadap siaran notifikasi tugas atau nilai rekan sekelasnya secara langsung lewat console browser tanpa perlu izin login (*realtime eavesdropping*). Oleh karena itu, siaran data pribadi wajib selalu menggunakan `PrivateChannel`. |