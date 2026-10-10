<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSubmissionRequest;
use App\Http\Resources\V1\SubmissionCollection;
use App\Http\Resources\V1\SubmissionResource;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * API AssignmentSubmissionController — Pengumpulan & Daftar Tugas Mahasiswa.
 *
 * Endpoint:
 *   GET  /api/v1/assignments/{id}/submissions → index (dosen pemilik)
 *   POST /api/v1/assignments/{id}/submissions → store (mahasiswa terdaftar, multipart)
 */
class AssignmentSubmissionController extends Controller
{
    /**
     * GET /api/v1/assignments/{assignment}/submissions
     *
     * Menampilkan daftar submission untuk suatu tugas.
     * Akses: Dosen pemilik (pengampu mata kuliah tugas ini).
     */
    public function index(Request $request, Assignment $assignment)
    {
        $user = $request->user();

        // Pastikan relasi course termuat
        $assignment->load('course');

        abort_unless(
            $user->role === 'dosen' && $assignment->course->lecturer_id === $user->id,
            403,
            'Anda tidak memiliki akses ke sumber daya ini.'
        );

        $submissions = $assignment->submissions()
            ->with(['student', 'grade.grader'])
            ->latest('submitted_at')
            ->paginate(15);

        return new SubmissionCollection($submissions);
    }

    /**
     * POST /api/v1/assignments/{assignment}/submissions
     *
     * Mahasiswa mengumpulkan tugas (upload multipart file).
     * Akses: Mahasiswa yang terdaftar pada mata kuliah tugas ini.
     */
    public function store(StoreSubmissionRequest $request, Assignment $assignment)
    {
        $user = $request->user();

        // Pastikan relasi course termuat
        $assignment->load('course');

        // Otorisasi sudah dilakukan di StoreSubmissionRequest (403)
        // Jika sampai ke sini, user adalah mahasiswa terdaftar.

        // Cek apakah mahasiswa sudah pernah mengumpulkan tugas ini
        if (Submission::where('assignment_id', $assignment->id)->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'file' => ['Anda sudah mengumpulkan tugas ini.'],
            ]);
        }

        // Cek batas waktu (due_at)
        $isLate = now()->isAfter($assignment->due_at);
        if ($isLate && ! $assignment->allow_late) {
            throw ValidationException::withMessages([
                'file' => ['Tenggat waktu pengumpulan tugas telah lewat.'],
            ]);
        }

        $file = $request->file('file');
        $path = $file->store('submissions', 'public');

        $submission = new Submission();
        $submission->assignment_id = $assignment->id;
        $submission->user_id = $user->id;
        $submission->file_path = $path;
        $submission->original_name = $file->getClientOriginalName();
        $submission->file_size = $file->getSize();
        $submission->note = $request->input('note');
        $submission->submitted_at = now();
        $submission->is_late = $isLate;
        $submission->save();

        return (new SubmissionResource($submission->load('student')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/v1/submissions/{submission}
     *
     * Detail submission (menguji Policy view — pencegahan IDOR).
     */
    public function show(Request $request, Submission $submission)
    {
        $submission->load(['student', 'grade.grader', 'assignment.course']);

        \Illuminate\Support\Facades\Gate::authorize('view', $submission);

        return new SubmissionResource($submission);
    }
}
