<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

/**
 * Dosen\CourseController — Mata kuliah yang diampu oleh dosen.
 *
 * Route model binding diterapkan: parameter {course} di URL
 * otomatis di-resolve menjadi instance Course oleh Laravel.
 *
 * Route hanya mendaftarkan: index, show
 * (lihat ->only(['index', 'show']) di routes/web.php)
 * Dosen tidak membuat/edit MK — itu hak admin.
 *
 * ⚠️ IDOR TINGGI: Controller harus memfilter
 * where('lecturer_id', auth()->id()) agar dosen A
 * tidak bisa melihat MK dosen B.
 * → Akan dipindahkan ke Policy di minggu 7.
 */
class CourseController extends Controller
{
    /**
     * Daftar mata kuliah yang diampu dosen ini.
     *
     * Route: GET /dosen/courses → dosen.courses.index
     */
    public function index()
    {
        //
    }

    /**
     * Detail satu mata kuliah yang diampu.
     *
     * Route: GET /dosen/courses/{course} → dosen.courses.show
     * Route model binding: {course} → Course $course
     */
    public function show(Course $course)
    {
        abort_unless($course->lecturer_id === auth()->id(), 403);
    }
}
