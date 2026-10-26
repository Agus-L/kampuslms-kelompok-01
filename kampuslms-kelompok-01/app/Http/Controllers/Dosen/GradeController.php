<?php

namespace App\Http\Controllers\Dosen;

use App\Events\SubmissionGraded;
use App\Http\Controllers\Controller;
use App\Http\Requests\GradeRequest;
use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Support\Facades\Gate;

/**
 * Dosen\GradeController — Memberi dan memperbarui nilai.
 *
 * Route model binding diterapkan:
 * - Route bersarang (store): menerima Submission $submission
 *   sebagai parent → /dosen/submissions/{submission}/grades
 * - Route shallow (update): menerima Grade $grade
 *   langsung → /dosen/grades/{grade}
 *
 * Route hanya mendaftarkan: store, update
 * (lihat ->only(['store', 'update']) di routes/web.php)
 *
 * Prinsip Controller Tipis:
 * - Validasi input didelegasikan ke Form Request (GradeRequest).
 * - Otorisasi menggunakan GradePolicy via Gate::authorize.
 * - Dispatching event SubmissionGraded mendelegasikan pengiriman notifikasi
 *   ke antrean asynchronous di background.
 */
class GradeController extends Controller
{
    /**
     * Simpan nilai baru untuk sebuah submission.
     *
     * Route: POST /dosen/submissions/{submission}/grades → dosen.submissions.grades.store
     * Route model binding: {submission} → Submission $submission
     */
    public function store(GradeRequest $request, Submission $submission)
    {
        Gate::authorize('create', [Grade::class, $submission]);

        $validated = $request->validated();

        $grade = Grade::firstOrNew(['submission_id' => $submission->id]);
        $grade->graded_by = auth()->id();
        $grade->score     = $validated['score'];
        $grade->feedback  = $validated['feedback'] ?? null;
        $grade->graded_at = now();
        $grade->save();

        // Dispatch event → notifikasi ke mahasiswa pemilik submission via Queue
        event(new SubmissionGraded($grade));

        return back()->with('success', 'Nilai dan umpan balik berhasil disimpan.');
    }

    /**
     * Perbarui nilai yang sudah ada (shallow route).
     *
     * Route: PUT /dosen/grades/{grade} → dosen.grades.update
     * Route model binding: {grade} → Grade $grade
     */
    public function update(GradeRequest $request, Grade $grade)
    {
        Gate::authorize('update', $grade);

        $validated = $request->validated();

        $grade->graded_by = auth()->id();
        $grade->score     = $validated['score'];
        $grade->feedback  = $validated['feedback'] ?? null;
        $grade->graded_at = now();
        $grade->save();

        // Dispatch event → notifikasi ke mahasiswa pemilik submission via Queue
        event(new SubmissionGraded($grade));

        return back()->with('success', 'Nilai berhasil diperbarui.');
    }
}
