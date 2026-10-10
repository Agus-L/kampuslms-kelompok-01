<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
 * Otorisasi menggunakan CoursePolicy via Gate::authorize.
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
        Gate::authorize('view', $course);
    }
}
