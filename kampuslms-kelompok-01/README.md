# KampusLMS — Kelompok 01

* **URL Publik Resmi (Live HTTPS):** [https://kampuslms-kelompok-01-production.up.railway.app](https://kampuslms-kelompok-01-production.up.railway.app)
* **Dokumentasi Deployment (Milestone 4):** [docs/deployment.md](../docs/deployment.md)

### Akun Demo:
* Admin: `admin@kampuslms.test` / `password`
* Dosen: `dosen@kampuslms.test` / `password`
* Mahasiswa: `mahasiswa@kampuslms.test` / `password`

---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## Sistem Notifikasi & Queue Worker

Proyek ini menggunakan **Event-Driven Notification** untuk memberi tahu mahasiswa ketika:
1. **Tugas baru dipublikasikan** oleh dosen → seluruh mahasiswa di mata kuliah tersebut.
2. **Submission dinilai** oleh dosen → mahasiswa pemilik submission.

### Prasyarat Tabel Database

Proyek ini sudah menyertakan migration untuk tabel-tabel yang dibutuhkan queue dan notifikasi. Jalankan migrasi untuk membuat seluruh tabel yang diperlukan:

```bash
php artisan migrate
```

Tabel yang dibuat oleh migration di atas terkait sistem ini:

| Tabel | Migration | Fungsi |
|-------|-----------|--------|
| `jobs` | `0001_01_01_000002_create_jobs_table.php` | Menyimpan antrean job yang menunggu dieksekusi worker |
| `failed_jobs` | `0001_01_01_000002_create_jobs_table.php` | Mencatat job yang gagal setelah dieksekusi |
| `job_batches` | `0001_01_01_000002_create_jobs_table.php` | Mendukung batch processing job |
| `notifications` | `2025_01_02_000007_create_notifications_table.php` | Menyimpan notifikasi in-app (lonceng) untuk setiap user |

### Konfigurasi Queue

Pastikan file `.env` sudah mengatur koneksi queue ke database:

```env
QUEUE_CONNECTION=database
```

Nilai ini sudah diatur secara default di `.env.example`.

### Menjalankan Queue Worker

Setelah `php artisan migrate` selesai, jalankan queue worker di terminal **terpisah** agar job notifikasi dapat diproses:

```bash
# Mode development — worker berjalan dan memproses job secara real-time
php artisan queue:work

# Dengan opsi retry (mencoba ulang job yang gagal setelah 90 detik)
php artisan queue:work --tries=3 --backoff=90
```

> **⚠️ PENTING:** Queue worker **harus tetap berjalan** selama aplikasi digunakan.
> Jika worker mati, notifikasi **tidak akan terkirim** (baik database maupun email),
> tetapi **tidak ada error** yang muncul di layar dosen — ini disebut *silent failure*.
> Job hanya menumpuk di tabel `jobs` hingga worker dihidupkan kembali.

### Perintah Berguna

```bash
# Melihat antrean job yang tertunda
php artisan queue:monitor database:default

# Menjalankan 1 job saja (berguna untuk debugging)
php artisan queue:work --once

# Melihat job yang gagal
php artisan queue:failed

# Menjalankan ulang semua job yang gagal
php artisan queue:retry all

# Membersihkan semua job yang gagal
php artisan queue:flush
```

### Arsitektur Event → Listener → Notification

```
Dosen membuat tugas (AssignmentController@store)
  └─► event(new AssignmentPublished($assignment))
        └─► SendAssignmentPublishedNotification (ShouldQueue)
              └─► AssignmentPublishedNotification → [database, mail]
                    └─► Penerima: $assignment->course->students

Dosen memberi nilai (GradeController@store / update)
  └─► event(new SubmissionGraded($grade))
        └─► SendSubmissionGradedNotification (ShouldQueue)
              └─► SubmissionGradedNotification → [database, mail]
                    └─► Penerima: $grade->submission->student
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

