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
    public function index(Request $request)
    {
        $courses = $request->user()->courses()
            ->with('lecturer')
            ->withCount(['materials', 'assignments' => fn ($q) => $q->where('status', 'published')])
            ->when($request->filled('q'), fn ($query) =>
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->q . '%')
                      ->orWhere('code', 'like', '%' . $request->q . '%');
                }))
            ->when($request->filled('status'), fn ($query) =>
                $query->where('status', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('courses.index', compact('courses'));
    }

    /**
     * Detail satu mata kuliah yang diikuti.
     *
     * Route: GET /mahasiswa/courses/{course} → mahasiswa.courses.show
     * Route model binding: {course} → Course $course
     */
    public function show(Request $request, Course $course)
    {
        Gate::authorize('view', $course);

        $course->load([
            'lecturer',
            'students',
            'materials' => fn ($q) => $q->latest(),
            'assignments' => fn ($q) => $q->where('status', 'published')->latest(),
        ]);

        return view('courses.show', compact('course'));
    }
}
