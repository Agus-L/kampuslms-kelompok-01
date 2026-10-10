<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
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
 * Route hanya mendaftarkan: index
 * (lihat ->only(['index']) di routes/web.php)
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
    public function index(Submission $submission)
    {
        Gate::authorize('view', $submission);

        $grade = $submission->grade;

        return response()->json([
            'submission_id' => $submission->id,
            'assignment'    => $submission->assignment->title ?? null,
            'grade'         => $grade,
        ]);
    }
}
