<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Mahasiswa\GradeController — Melihat nilai sendiri.
 *
 * Route model binding diterapkan:
 * - Route bersarang (index): menerima Submission $submission
 *   sebagai parent → /mahasiswa/submissions/{submission}/grades
 *
 * Route hanya mendaftarkan: index, courseGrades
 * (lihat ->only(['index']) dan route GET courses/{course}/grades di routes/web.php)
 * Mahasiswa hanya bisa melihat nilai, tidak bisa edit.
 *
 * Otorisasi menggunakan SubmissionPolicy via Gate::authorize.
 */
class GradeController extends Controller
{
    /**
     * Lihat nilai untuk sebuah submission.
     *
     * Route: GET /mahasiswa/submissions/{submission}/grades → mahasiswa.submissions.grades.index
     * Route model binding: {submission} → Submission $submission
     */
    public function index(Request $request, Submission $submission)
    {
        Gate::authorize('view', $submission);

        if ($request->wantsJson()) {
            return response()->json([
                'submission_id' => $submission->id,
                'assignment'    => $submission->assignment->title ?? null,
                'grade'         => $submission->grade,
            ]);
        }

        return redirect()->route('mahasiswa.submissions.show', $submission);
    }

    /**
     * Rekap nilai per mata kuliah — Poin 7 Milestone M3.
     *
     * Menampilkan rapor/transkrip nilai mahasiswa pada satu mata kuliah:
     * daftar seluruh tugas, status pengumpulan, nilai yang didapat,
     * dan feedback dosen untuk setiap tugas.
     *
     * Route: GET /mahasiswa/courses/{course}/grades → mahasiswa.courses.grades.index
     * Route model binding: {course} → Course $course
     *
     * Catatan Performa (untuk optimisasi Minggu 11):
     * - Saat ini menggunakan Eager Loading standar.
     * - Bisa dioptimasi dengan subquery atau raw aggregate di Minggu 11.
     */
    public function courseGrades(Course $course)
    {
        Gate::authorize('view', $course);

        $user = auth()->user();

        // Ambil semua tugas published di MK ini, beserta submission milik mahasiswa ini
        $assignments = $course->assignments()
            ->where('status', 'published')
            ->with(['submissions' => function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->with('grade');
            }])
            ->orderBy('due_at', 'asc')
            ->get();

        // Hitung statistik pribadi mahasiswa
        $gradedScores = collect();
        foreach ($assignments as $assignment) {
            $sub = $assignment->submissions->first();
            if ($sub && $sub->grade && $sub->grade->score !== null) {
                $gradedScores->push([
                    'score'     => $sub->grade->score,
                    'max_score' => $assignment->max_score,
                ]);
            }
        }

        $stats = (object) [
            'avg'              => $gradedScores->isNotEmpty() ? round($gradedScores->avg('score'), 2) : null,
            'total_assignments' => $assignments->count(),
            'graded_count'     => $gradedScores->count(),
            'total_score'      => $gradedScores->sum('score'),
            'total_max_score'  => $gradedScores->sum('max_score'),
        ];

        return view('mahasiswa.grades.index', compact('course', 'assignments', 'stats'));
    }
}
