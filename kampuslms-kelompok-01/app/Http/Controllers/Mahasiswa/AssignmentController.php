<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;

/**
 * Mahasiswa\AssignmentController — Melihat tugas.
 *
 * Route model binding diterapkan:
 * - Route bersarang (index): menerima Course $course
 *   sebagai parent → /mahasiswa/courses/{course}/assignments
 * - Route shallow (show): menerima Assignment $assignment
 *   langsung → /mahasiswa/assignments/{assignment}
 *
 * Route hanya mendaftarkan: index, show
 * (lihat ->only(['index', 'show']) di routes/web.php)
 * Mahasiswa hanya melihat tugas, tidak bisa membuat/edit/hapus.
 *
 * ⚠️ IDOR TINGGI: Mahasiswa bisa mengakses tugas MK yang
 * tidak dia ikuti dengan mengganti ID di URL.
 * → Akan dipindahkan ke Policy di minggu 7.
 */
class AssignmentController extends Controller
{
    /**
     * Daftar tugas dalam satu mata kuliah.
     *
     * Route: GET /mahasiswa/courses/{course}/assignments → mahasiswa.courses.assignments.index
     * Route model binding: {course} → Course $course
     */
    public function index(Course $course)
    {
        //
    }

    /**
     * Detail satu tugas (shallow route).
     *
     * Route: GET /mahasiswa/assignments/{assignment} → mahasiswa.assignments.show
     * Route model binding: {assignment} → Assignment $assignment
     */
    public function show(Assignment $assignment)
    {
        //
    }
}
