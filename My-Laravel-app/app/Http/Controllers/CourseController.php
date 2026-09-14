<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * CourseController — Mengelola halaman modul Mata Kuliah.
 *
 * Saat ini menggunakan data statis (array) sebagai pengganti database,
 * agar kita bisa fokus memahami alur Route → Controller → View
 * sebelum masuk ke Eloquent & migrasi.
 */
class CourseController extends Controller
{
    /**
     * Sumber data statis.
     *
     * KENAPA private static method?
     * → Supaya data bisa dipakai oleh index() dan show()
     *   tanpa duplikasi. Nanti saat sudah pakai database,
     *   method ini diganti dengan query Eloquent (Course::all()).
     */
    private static function dummyCourses(): array
    {
        return [
            [
                'id'         => 1,
                'kode'       => 'IF101',
                'nama'       => 'Pemrograman Web',
                'sks'        => 3,
                'semester'   => 5,
                'dosen'      => 'Dr. Budi Santoso, M.Kom.',
                // 'deskripsi'  => 'Mempelajari pengembangan aplikasi web modern menggunakan HTML, CSS, JavaScript, dan framework Laravel. Mahasiswa akan membangun proyek nyata berupa Learning Management System.',
                'deskripsi'  => '<script>alert("Kamu kena XSS!")</script>',
                'jadwal'     => 'Senin, 08:00 – 10:30 WIB',
                'ruangan'    => 'Lab Komputer 3',
                'mahasiswa'  => 42,
            ],
            [
                'id'         => 2,
                'kode'       => 'IF102',
                'nama'       => 'Basis Data Lanjut',
                'sks'        => 3,
                'semester'   => 5,
                'dosen'      => 'Prof. Siti Aminah, Ph.D.',
                'deskripsi'  => 'Membahas konsep lanjutan basis data relasional: normalisasi, indexing, stored procedure, trigger, serta pengenalan basis data NoSQL.',
                'jadwal'     => 'Selasa, 13:00 – 15:30 WIB',
                'ruangan'    => 'Lab Komputer 1',
                'mahasiswa'  => 38,
            ],
            [
                'id'         => 3,
                'kode'       => 'IF103',
                'nama'       => 'Rekayasa Perangkat Lunak',
                'sks'        => 3,
                'semester'   => 5,
                'dosen'      => 'Dr. Ahmad Wijaya, M.T.',
                'deskripsi'  => 'Mendalami siklus hidup pengembangan perangkat lunak, mulai dari requirement engineering, desain UML, implementasi, testing, hingga deployment dan maintenance.',
                'jadwal'     => 'Rabu, 10:00 – 12:30 WIB',
                'ruangan'    => 'Ruang 405',
                'mahasiswa'  => 45,
            ],
            [
                'id'         => 4,
                'kode'       => 'IF104',
                'nama'       => 'Jaringan Komputer',
                'sks'        => 3,
                'semester'   => 5,
                'dosen'      => 'Dr. Rina Kartika, M.Kom.',
                'deskripsi'  => 'Mempelajari arsitektur jaringan, model OSI & TCP/IP, subnetting, routing, dan keamanan jaringan. Praktikum menggunakan Cisco Packet Tracer.',
                'jadwal'     => 'Kamis, 08:00 – 10:30 WIB',
                'ruangan'    => 'Lab Jaringan',
                'mahasiswa'  => 40,
            ],
            [
                'id'         => 5,
                'kode'       => 'IF105',
                'nama'       => 'Kecerdasan Buatan',
                'sks'        => 3,
                'semester'   => 5,
                'dosen'      => 'Prof. Hendra Gunawan, Ph.D.',
                'deskripsi'  => 'Pengantar kecerdasan buatan meliputi search algorithm, knowledge representation, machine learning dasar, dan natural language processing.',
                'jadwal'     => 'Jumat, 10:00 – 12:30 WIB',
                'ruangan'    => 'Ruang 301',
                'mahasiswa'  => 36,
            ],
        ];
    }

    /**
     * index — Menampilkan DAFTAR seluruh mata kuliah.
     *
     * KENAPA index()?
     * → Konvensi RESTful Laravel: method index() bertugas
     *   menampilkan koleksi resource (semua mata kuliah).
     *   Route: GET /courses  →  courses.index
     */
    public function index()
    {
        // Ambil semua data mata kuliah (nanti diganti Course::all())
        $courses = self::dummyCourses();

        // Kirim data ke view 'courses.index'
        // compact('courses') sama dengan ['courses' => $courses]
        return view('courses.index', compact('courses'));
    }

    /**
     * show — Menampilkan DETAIL satu mata kuliah.
     *
     * KENAPA show($id)?
     * → Konvensi RESTful Laravel: method show() bertugas
     *   menampilkan satu resource tertentu berdasarkan identifier.
     *   Route: GET /courses/{course}  →  courses.show
     *
     * KENAPA parameter $id, bukan Route Model Binding?
     * → Karena kita belum pakai database/Eloquent Model.
     *   Nanti saat sudah ada Model Course, parameter ini diganti
     *   menjadi show(Course $course) dan Laravel otomatis
     *   mencari data berdasarkan ID (Route Model Binding).
     */
    public function show(string $id)
    {
        $courses = self::dummyCourses();

        // Cari mata kuliah berdasarkan ID di dalam array statis.
        // collect() mengubah array menjadi Laravel Collection
        // supaya bisa pakai method firstWhere().
        $course = collect($courses)->firstWhere('id', (int) $id);

        // Jika ID tidak ditemukan, tampilkan halaman 404.
        // abort(404) melempar HttpException yang ditangani Laravel.
        if (!$course) {
            abort(404);
        }

        return view('courses.show', compact('course'));
    }
}
