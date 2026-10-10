<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Mahasiswa\SubmissionController — Mengumpulkan dan melihat tugas.
 *
 * Route model binding diterapkan:
 * - Route bersarang (index, create, store): menerima Assignment $assignment
 *   sebagai parent → /mahasiswa/assignments/{assignment}/submissions
 * - Route shallow (show, edit, update): menerima Submission $submission
 *   langsung → /mahasiswa/submissions/{submission}
 *
 * Route mendaftarkan semua kecuali destroy
 * (lihat ->except(['destroy']) di routes/web.php)
 * Submission yang sudah dikumpulkan tidak boleh dihapus.
 *
 * Otorisasi menggunakan SubmissionPolicy & AssignmentPolicy via Gate::authorize.
 */
class SubmissionController extends Controller
{
    /**
     * Daftar submission untuk satu tugas.
     *
     * Route: GET /mahasiswa/assignments/{assignment}/submissions → mahasiswa.assignments.submissions.index
     * Route model binding: {assignment} → Assignment $assignment
     */
    public function index(Assignment $assignment)
    {
        Gate::authorize('view', $assignment);

        $submissions = $assignment->submissions()
            ->where('user_id', auth()->id())
            ->with('grade')
            ->get();

        return response()->json($submissions);
    }

    /**
     * Form untuk mengumpulkan tugas baru.
     *
     * Route: GET /mahasiswa/assignments/{assignment}/submissions/create → mahasiswa.assignments.submissions.create
     * Route model binding: {assignment} → Assignment $assignment
     */
    public function create(Assignment $assignment)
    {
        Gate::authorize('create', [Submission::class, $assignment]);
    }

    /**
     * Simpan submission baru.
     *
     * Route: POST /mahasiswa/assignments/{assignment}/submissions → mahasiswa.assignments.submissions.store
     * Route model binding: {assignment} → Assignment $assignment
     */
    public function store(Request $request, Assignment $assignment)
    {
        Gate::authorize('create', [Submission::class, $assignment]);
    }

    /**
     * Detail satu submission (shallow route).
     *
     * Route: GET /mahasiswa/submissions/{submission} → mahasiswa.submissions.show
     * Route model binding: {submission} → Submission $submission
     */
    public function show(Submission $submission)
    {
        Gate::authorize('view', $submission);
    }

    /**
     * Form edit submission (shallow route).
     *
     * Route: GET /mahasiswa/submissions/{submission}/edit → mahasiswa.submissions.edit
     * Route model binding: {submission} → Submission $submission
     */
    public function edit(Submission $submission)
    {
        Gate::authorize('update', $submission);
    }

    /**
     * Simpan perubahan submission (shallow route).
     *
     * Route: PUT /mahasiswa/submissions/{submission} → mahasiswa.submissions.update
     * Route model binding: {submission} → Submission $submission
     */
    public function update(Request $request, Submission $submission)
    {
        Gate::authorize('update', $submission);
    }
}
