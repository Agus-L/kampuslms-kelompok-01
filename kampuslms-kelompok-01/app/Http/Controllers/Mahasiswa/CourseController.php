<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

/**
 * Mahasiswa\CourseController — Melihat MK yang diikuti.
 *
 * Route model binding diterapkan: parameter {course} di URL
 * otomatis di-resolve menjadi instance Course oleh Laravel.
 *
 * Route hanya mendaftarkan: index, show
 * (lihat ->only(['index', 'show']) di routes/web.php)
 * Mahasiswa hanya melihat MK yang dia ikuti, tidak bisa CRUD.
 *
 * ⚠️ IDOR TINGGI: Controller harus memfilter hanya MK yang
 * di-enroll oleh mahasiswa yang sedang login.
 * → Akan dipindahkan ke Policy di minggu 7.
 */
class CourseController extends Controller
{
    /**
     * Daftar mata kuliah yang diikuti mahasiswa ini.
     *
     * Route: GET /mahasiswa/courses → mahasiswa.courses.index
     */
    public function index()
    {
        //
    }

    /**
     * Detail satu mata kuliah yang diikuti.
     *
     * Route: GET /mahasiswa/courses/{course} → mahasiswa.courses.show
     * Route model binding: {course} → Course $course
     */
    public function show(Course $course)
    {
        //
    }
}
