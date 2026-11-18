# KampusLMS — Kelompok 01 (Semester 5 Pemrograman Web)

Aplikasi Portal Pembelajaran Terintegrasi (*Learning Management System*) berbasis **Laravel 12**, **PHP 8.4**, **Livewire**, dan **Tailwind CSS**.

---

## 🌐 Tautan Aplikasi Produksi (Live Deployment)

* **URL Publik Resmi:** [https://kampuslms-kelompok-01-production.up.railway.app](https://kampuslms-kelompok-01-production.up.railway.app)
* **Status:** Aktif (*Live* di Railway Cloud Platform) dengan sertifikat **HTTPS / SSL valid**.

---

## 👥 Pembagian Peran dan Kontribusi Tim

Tabel ini disusun secara jujur dan transparan mencerminkan kontribusi repositori (`git shortlog`) serta pembagian tanggung jawab modul:

| Nama Anggota | NIM | Username GitHub | Tanggung Jawab Utama / Modul |
|---|---|---|---|
| **Agus Liberty Purba** | 10241005 | `Agus-L` | Infrastruktur Docker & Cloud Railway, Skrip Deploy (`deploy.sh`), Sinkronisasi CI/CD, Integrasi PR |
| **Anastasya Salsabila K.** | 10241009 | `kh0oi` | Controller Transkrip Nilai & Rekap Presensi Tugas (W12), Supervisor Queue Worker, Optimasi Pagination |
| **Aan Mardiah** | 10241001 | `aanmardiah2306j` | Audit Keamanan IDOR & Otorisasi Scoping, Pengujian Keamanan Produksi (Curl), Penanganan Job Gagal (Queue) |
| **Aliya Labibah** | 10241007 | `10241007-ops` | Desain Transkrip PDF Landscape ITK, Laporan Terpadu Dosen Pengampu, Template PR, Indeks Database |
| **Annisa Dwi Lestari S.** | 10241011 | `Dwi-ctrl` | Kebijakan Otorisasi (`TranscriptPolicy`), Notifikasi Navbar (Livewire Polling), Caching Dashboard Admin |

---

## 🔑 Akun Demo Pengujian

Gunakan akun pra-konfigurasi berikut untuk menguji berbagai peran pengguna:

| Peran (Role) | Email Akun | Kata Sandi | Deskripsi Hak Akses |
|---|---|---|---|
| **Admin** | `admin@kampuslms.test` | `password` | Pengelolaan data master pengguna, program studi, dan audit sistem |
| **Dosen** | `dosen@kampuslms.test` | `password` | Pembuatan materi, penilaian tugas, dan rekap transkrip kelas |
| **Mahasiswa** | `mahasiswa@kampuslms.test` | `password` | Akses perkuliahan, pengumpulan tugas, dan unduh transkrip nilai |

---

## 📑 Dokumentasi Milestone

* **Panduan & Laporan Deployment (Milestone 4):** [docs/deployment.md](docs/deployment.md)
* **Audit Keamanan IDOR (Milestone 2):** [docs/keamanan.md](docs/keamanan.md)
* **Laporan Optimasi Performa (Milestone 3 / Minggu 11):** [docs/performa.md](docs/performa.md)
* **Spesifikasi Transkrip Akademik (Minggu 12):** [spek-minggu-12.md](spek-minggu-12.md)
