<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
 * Otorisasi menggunakan AssignmentPolicy & CoursePolicy via Gate::authorize.
 */
class AssignmentController extends Controller
{
    /**
     * Daftar tugas dalam satu mata kuliah yang diikuti.
     *
     * Route: GET /mahasiswa/courses/{course}/assignments → mahasiswa.courses.assignments.index
     * Route model binding: {course} → Course $course
     */
    public function index(Request $request, Course $course)
    {
        Gate::authorize('view', $course);

        $course->loadMissing('lecturer');

        $assignments = $course->assignments()
            ->with('course')
            ->where('status', 'published')
            ->latest()
            ->paginate(15);

        return view('assignments.index', compact('course', 'assignments'));
    }

    /**
     * Detail satu tugas (shallow route).
     *
     * Route: GET /mahasiswa/assignments/{assignment} → mahasiswa.assignments.show
     * Route model binding: {assignment} → Assignment $assignment
     */
    public function show(Request $request, Assignment $assignment)
    {
        Gate::authorize('view', $assignment);

        $assignment->load([
            'course',
            'submissions' => function ($query) {
                $query->where('user_id', auth()->id())->with('grade');
            },
        ]);

        return view('assignments.show', compact('assignment'));
    }
}
