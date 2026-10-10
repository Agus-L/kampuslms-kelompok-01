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
    public function index(Request $request)
    {
        $courses = $request->user()->taughtCourses()
            ->with('lecturer')
            ->withCount(['students', 'materials', 'assignments'])
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

        if ($request->wantsJson()) {
            return response()->json($courses);
        }

        return view('courses.index', compact('courses'));
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