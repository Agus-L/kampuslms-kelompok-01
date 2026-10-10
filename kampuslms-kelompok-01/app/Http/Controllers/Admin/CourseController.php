<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

/**
 * Admin\CourseController — CRUD mata kuliah oleh Admin.
 *
 * Route model binding diterapkan: parameter {course} di URL
 * otomatis di-resolve menjadi instance Course oleh Laravel.
 * Jika course tidak ditemukan, Laravel melempar 404 otomatis.
 */
class CourseController extends Controller
{
    /**
     * Daftar seluruh mata kuliah.
     *
     * Route: GET /admin/courses → admin.courses.index
     */
    public function index()
    {
        $courses = Course::with('lecturer')->latest()->paginate(15);

        if (request()->wantsJson()) {
            return response()->json($courses);
        }

        return view('courses.index', compact('courses'));
    }

    /**
     * Form tambah mata kuliah baru.
     *
     * Route: GET /admin/courses/create → admin.courses.create
     */
    public function create()
    {
        //
    }

    /**
     * Simpan mata kuliah baru ke database.
     *
     * Route: POST /admin/courses → admin.courses.store
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Detail satu mata kuliah.
     *
     * Route: GET /admin/courses/{course} → admin.courses.show
     * Route model binding: {course} → Course $course
     */
    public function show(Course $course)
    {
        //
    }

    /**
     * Form edit mata kuliah.
     *
     * Route: GET /admin/courses/{course}/edit → admin.courses.edit
     * Route model binding: {course} → Course $course
     */
    public function edit(Course $course)
    {
        //
    }

    /**
     * Simpan perubahan mata kuliah.
     *
     * Route: PUT /admin/courses/{course} → admin.courses.update
     * Route model binding: {course} → Course $course
     */
    public function update(Request $request, Course $course)
    {
        //
    }

    /**
     * Hapus mata kuliah.
     *
     * Route: DELETE /admin/courses/{course} → admin.courses.destroy
     * Route model binding: {course} → Course $course
     */
    public function destroy(Course $course)
    {
        //
    }
}
