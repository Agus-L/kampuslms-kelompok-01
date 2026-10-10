<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
        Gate::authorize('view', $course);

        return response()->json([
            'dosen_login' => auth()->user()->name,
            'mata_kuliah' => $course->name,
            'pengampu'    => $course->lecturer->name,
        ]);
    }
}