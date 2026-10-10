<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Admin\EnrollmentController — Kelola enrollment (pendaftaran mahasiswa ke MK).
 *
 * Route model binding diterapkan:
 * - index, create, store: menerima Course $course (parent) karena URL
 *   mengandung {course} → /admin/courses/{course}/enrollments
 * - destroy: menerima string $enrollment (pivot ID) karena shallow()
 *   → /admin/enrollments/{enrollment}
 *
 * Catatan: Enrollment adalah pivot table (course_user), bukan model Eloquent
 * standar. Untuk destroy, kita akan mencari record pivot berdasarkan ID-nya
 * secara manual karena pivot table tidak memiliki model tersendiri.
 *
 * Route hanya mendaftarkan: index, create, store, destroy
 * (lihat ->only([...]) di routes/web.php)
 */
class EnrollmentController extends Controller
{
    /**
     * Daftar mahasiswa yang terdaftar di mata kuliah ini.
     *
     * Route: GET /admin/courses/{course}/enrollments → admin.courses.enrollments.index
     * Route model binding: {course} → Course $course
     */
    public function index(Course $course)
    {
        Gate::authorize('manageEnrollment', $course);
    }

    /**
     * Form untuk mendaftarkan mahasiswa baru ke mata kuliah.
     *
     * Route: GET /admin/courses/{course}/enrollments/create → admin.courses.enrollments.create
     * Route model binding: {course} → Course $course
     */
    public function create(Course $course)
    {
        Gate::authorize('manageEnrollment', $course);
    }

    /**
     * Simpan pendaftaran mahasiswa ke mata kuliah.
     *
     * Route: POST /admin/courses/{course}/enrollments → admin.courses.enrollments.store
     * Route model binding: {course} → Course $course
     */
    public function store(Request $request, Course $course)
    {
        Gate::authorize('manageEnrollment', $course);
    }

    /**
     * Hapus pendaftaran mahasiswa dari mata kuliah.
     *
     * Route: DELETE /admin/enrollments/{enrollment} → admin.enrollments.destroy
     *
     * Catatan: {enrollment} adalah ID dari record di pivot table course_user.
     * Karena tidak ada model Enrollment, parameter tetap string dan dicari manual.
     * Ini adalah pengecualian — pivot table tanpa model tersendiri tidak bisa
     * menggunakan route model binding otomatis.
     */
    public function destroy(string $enrollment)
    {
        //
    }
}
