# Identitas Mahasiswa

## Nama: Agus Liberty Purba
## NIM: 10241005


---
### READ
1. Matikan worker. Buat satu tugas baru. Buka tabel `jobs` — apa isinya?
Jawab: Ketika proses background worker tidak aktif dan pengajar mempublikasikan tugas baru, Laravel menambahkan satu baris entri antrean baru ke dalam tabel `jobs`. Kolom-kolom di dalamnya merepresentasikan metadata antrean asinkron:
- `id`: Pengenal unik bertipe integer untuk baris pekerjaan tersebut (contoh: `1`).
- `queue`: Bernilai `'default'`, yang menunjukkan nama antrean standar sistem.
- `payload`: Teks JSON panjang yang menyimpan detail instruksi eksekusi, mencakup namespace listener target (`App\Listeners\SendAssignmentPublishedNotification`) serta objek event `AssignmentPublished` yang telah diserialisasi.
- `attempts`: Angka `0`, mengindikasikan bahwa antrean ini belum pernah dicoba dieksekusi oleh worker.
- `reserved_at`: Bernilai `NULL` karena belum ada worker aktif yang mengunci atau memproses job ini.
- `available_at` & `created_at`: Stempel waktu (UNIX timestamp) yang mencatat waktu pertama kali tugas dimasukkan ke antrean.

Selama worker belum dijalankan di terminal, tugas ini akan terus mengendap (*pending*) di tabel `jobs`, sehingga notifikasi tidak kunjung sampai ke akun mahasiswa.

2. Baca kolom `payload`. Bisakah Anda mengenali nama Listener dan ID tugasnya?
Jawab: Ya, kedua informasi tersebut dapat diidentifikasi secara jelas di dalam struktur JSON kolom `payload`:
- Nama Listener: Terlihat langsung pada kunci `"displayName"`, yaitu `"App\\Listeners\\SendAssignmentPublishedNotification"`.
- ID Tugas: Ditemukan pada bagian `"data" -> "command"` yang memuat string serialisasi objek PHP dari event `AssignmentPublished`. Di dalamnya tercatat referensi model Eloquent `App\Models\Assignment` beserta atribut ID-nya (misalnya `"id";i:15;`). Hal ini membuktikan bahwa Laravel menyimpan penanda model ke dalam antrean database agar nantinya worker dapat memuat ulang model tersebut dari database saat antrean dijalankan.

3. Jalankan `php artisan queue:work`, amati keluarannya. Periksa tabel `jobs` lagi.
Jawab: Saat perintah `php artisan queue:work` dieksekusi di terminal, worker langsung mendeteksi adanya antrean yang tertunda dan mencetak log proses:
```text
[2026-10-...] Processing: App\Listeners\SendAssignmentPublishedNotification
[2026-10-...] Processed:  App\Listeners\SendAssignmentPublishedNotification
```
Setelah statusnya berubah menjadi `Processed`, pemeriksaan ulang pada tabel `jobs` melalui Tinker (`DB::table('jobs')->count();`) menunjukkan angka 0 (bersih). Hal ini terjadi karena mekanisme internal Laravel secara otomatis menghapus rekaman job dari tabel `jobs` segera setelah tugas berhasil diselesaikan (*completed successfully*) guna mencegah penumpukan data di database.

4. Periksa tabel `notifications`. Berapa baris yang muncul? Cocok dengan jumlah mahasiswa?
Jawab: Setelah antrean tuntas diproses oleh worker, tabel `notifications` bertambah sejumlah baris yang tepat sama dengan total mahasiswa yang terdaftar pada mata kuliah tersebut. Sebagai contoh, jika terdapat 5 mahasiswa di kelas tersebut, maka akan muncul 5 entri notifikasi baru. Tiap entri memiliki atribut `notifiable_type = 'App\Models\User'` dan `notifiable_id` yang merepresentasikan ID pengguna masing-masing mahasiswa penerima.

5. Baca kolom `data` pada satu notifikasi. Adakah yang tidak seharusnya tersimpan di situ?
Jawab: Pada notifikasi pembuatan tugas (`AssignmentPublishedNotification`), kolom `data` menyimpan payload JSON berupa ringkasan tugas: `assignment_id`, `course_id`, `course_name`, `title`, `due_at`, `message`, dan `action_url`. Seluruh data ini aman disimpan karena bersifat informasi umum untuk seluruh peserta kelas.

Namun, hal yang TIDAK BOLEH disimpan di kolom `data` adalah informasi sensitif atau privat, contohnya pada notifikasi penilaian tugas (`SubmissionGradedNotification`). Skor angka mahasiswa (`score`) maupun catatan evaluasi dosen (`feedback`) tidak boleh disertakan di payload ini. Kolom `data` pada tabel `notifications` tersimpan dalam bentuk teks biasa (tanpa enkripsi) dan bisa dibaca langsung oleh siapa pun yang memiliki akses ke database atau peran admin. Menyimpan nilai di payload notifikasi rentan membocorkan privasi akademik mahasiswa. Praktik yang tepat adalah kolom `data` hanya menyimpan ID referensi dan teks pemberitahuan umum, sedangkan rincian nilai dan feedback hanya boleh diakses melalui halaman detail submission yang dilindungi oleh otorisasi Policy.



### BREAK

| # | Yang dicoba | Prediksi | Hasil Sebenarnya |
| --- | --- | --- | --- |
| 1 | Set `QUEUE_CONNECTION=database`, jangan jalankan worker, buat tugas baru | Tugas berhasil disimpan di web, tetapi notifikasi tidak pernah sampai ke mahasiswa tanpa memunculkan pesan error apa pun (*silent failure*). | Terbukti terjadi kegagalan senyap (*silent failure*). Dosen menerima respon sukses berwarna hijau di browser tanpa peringatan kendala. Namun di sisi mahasiswa, indikator notifikasi tetap nol. Pemeriksaan database membuktikan antrean hanya tertahan di tabel `jobs` karena tidak ada proses worker yang menjalankannya. Tidak ada catatan exception baik di layar browser maupun berkas `laravel.log`. |
| 2 | Hapus `implements ShouldQueue` dari Listener, buat tugas untuk 40 mahasiswa | Tombol simpan tugas akan mengalami *loading* yang lama karena pengiriman notifikasi ke puluhan mahasiswa dieksekusi secara sinkron di thread HTTP web yang sama. | Terbukti terjadi perlambatan (*freeze*) beberapa detik sebelum halaman dialihkan. Tanpa `implements ShouldQueue`, Laravel dipaksa melakukan iterasi dan menyisipkan puluhan baris notifikasi ke database sebelum mengembalikan respon ke dosen, sehingga menyandera kenyamanan pengguna. |
| 3 | Ganti `QUEUE_CONNECTION=sync`, ulangi nomor 2 | Respon tetap lambat seperti percobaan nomor 2 karena koneksi `sync` langsung menjalankan antrean saat itu juga tanpa proses latar belakang. | Terbukti performa tetap lambat dan meniadakan manfaat antrean. Kendati Listener menyertakan `implements ShouldQueue`, driver `sync` memaksa Laravel menjalankan method `handle()` secara instan dalam thread HTTP yang sama. Tabel `jobs` selalu kosong dan kegagalan pada pengiriman notifikasi akan langsung menggagalkan request dosen. |
| 4 | Buat Notification melempar exception, jalankan worker | Worker akan mencoba menjalankan ulang tugas sesuai nilai `$tries`, dan jika tetap gagal sampai batas maksimal, data akan dipindahkan ke tabel `failed_jobs`. | Terbukti worker menampilkan log status `FAILING` sebanyak 3 kali (sesuai properti `$tries = 3` dan jeda `$backoff`), sebelum akhirnya dinyatakan `FAILED`. Record otomatis dipindahkan dari tabel `jobs` ke tabel `failed_jobs` lengkap beserta pesan error dan *stack trace*. Method `failed()` juga mencatat log ke `storage/logs/laravel.log`. |
| 5 | Hentikan worker di tengah pemrosesan job | Tugas yang belum tuntas tidak akan hilang dan status reservasinya akan direset otomatis agar bisa dieksekusi ulang (*at-least-once delivery*). | Terbukti data job tetap aman di database. Saat worker dihentikan mendadak via `Ctrl+C` ketika memproses job, baris data tidak terhapus dari tabel `jobs`. Kolom `reserved_at` akan kedaluwarsa setelah batas `retry_after`. Begitu `queue:work` dijalankan kembali, worker langsung mengambil ulang pekerjaan tersebut dan memprosesnya hingga tuntas. |
| 6 | Masukkan nilai mahasiswa (`score` dan `feedback`) ke payload notifikasi, lalu buka tabel `notifications` sebagai admin | Nilai dan ulasan dosen tersimpan dalam format teks terbuka di database, memicu celah kebocoran privasi data akademik. | Terbukti terjadi pelanggaran privasi. Ketika `score` dan komentar evaluasi dimasukkan ke `toArray()` notifikasi, data tersebut tersimpan tanpa enkripsi di kolom `data`. Siapa pun yang memiliki hak akses basis data atau level admin dapat membaca nilai tugas mahasiswa lain tanpa tersaring oleh otorisasi `GradePolicy`. |
| 7 | Panggil endpoint "tandai dibaca" (`PATCH /notifications/{id}`) dengan ID notifikasi milik orang lain | Berpotensi memunculkan celah keamanan IDOR jika server tidak memeriksa kepemilikan notifikasi yang ditandai dibaca. | Sistem berhasil menangkal serangan IDOR karena controller menerapkan proteksi `abort_unless($notification->notifiable_id === $request->user()->id, 403)`. Saat atribut `action` form dimanipulasi untuk menarget notifikasi milik mahasiswa lain, server langsung memblokir dengan kode `403 Forbidden` dan menampilkan halaman penolakan akses. Namun jika validasi kepemilikan tersebut ditiadakan, pengguna bisa memanipulasi status notifikasi milik akun lain. |
| 8 | Kalau memakai Reverb: ganti `PrivateChannel` menjadi `Channel`, langganan channel orang lain | Menggunakan channel publik memungkinkan pengguna lain menyadap data siaran realtime pengguna lain (*IDOR WebSocket*). | Pada implementasi proyek ini, kami menggunakan Livewire Polling (`wire:poll.30s`) yang sudah mencukupi target kompetensi. Namun dalam arsitektur WebSocket (Reverb), penggantian ke `Channel` biasa akan memungkinkan siapa pun memantau notifikasi pengguna lain lewat konsol browser. Oleh karena itu, WebSocket wajib memakai `PrivateChannel` dengan otorisasi ketat di `routes/channels.php` (`$user->id === (int) $userId`). |
