<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

/**
 * Dosen\SubmissionController — Melihat submission mahasiswa.
 *
 * Route model binding diterapkan:
 * - Route bersarang (index): menerima Assignment $assignment
 *   sebagai parent → /dosen/assignments/{assignment}/submissions
 * - Route shallow (show, download): menerima Submission $submission
 *   langsung → /dosen/submissions/{submission}
 *
 * Route hanya mendaftarkan: index, show + custom download
 * (lihat ->only(['index', 'show']) di routes/web.php)
 * Dosen tidak membuat submission — itu hak mahasiswa.
 *
 * Otorisasi menggunakan SubmissionPolicy & AssignmentPolicy via Gate::authorize.
 */
class SubmissionController extends Controller
{
    /**
     * Daftar submission untuk satu tugas.
     *
     * Route: GET /dosen/assignments/{assignment}/submissions → dosen.assignments.submissions.index
     * Route model binding: {assignment} → Assignment $assignment
     */
    public function index(Assignment $assignment)
    {
        Gate::authorize('view', $assignment);

        $submissions = $assignment->submissions()
            ->with(['student', 'grade'])
            ->latest('submitted_at')
            ->paginate(20);

        return view('submissions.index', compact('assignment', 'submissions'));
    }

    /**
     * Detail satu submission (shallow route).
     *
     * Route: GET /dosen/submissions/{submission} → dosen.submissions.show
     * Route model binding: {submission} → Submission $submission
     */
    public function show(Submission $submission)
    {
        Gate::authorize('view', $submission);

        $submission->load(['student', 'assignment.course', 'grade.grader']);

        return view('submissions.show', compact('submission'));
    }

    /**
     * Unduh berkas submission (endpoint terpisah dengan otorisasi Policy).
     *
     * Route: GET /dosen/submissions/{submission}/download → dosen.submissions.download
     * Route model binding: {submission} → Submission $submission
     *
     * Akses dikendalikan oleh SubmissionPolicy@download:
     * - Dosen hanya bisa mengunduh berkas submission dari mata kuliah yang diampunya
     * - Berkas dikirim dari disk privat (storage/app/private) melalui PHP,
     *   sehingga web server tidak bisa melayani berkas secara langsung
     */
    public function download(Submission $submission)
    {
        Gate::authorize('download', $submission);

        abort_unless(Storage::disk('local')->exists($submission->file_path), 404);

        return Storage::disk('local')->download(
            $submission->file_path,
            $submission->original_name
        );
    }
}
