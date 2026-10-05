<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpsertGradeRequest;
use App\Http\Resources\V1\GradeResource;
use App\Models\Grade;
use App\Models\Submission;

/**
 * API SubmissionGradeController — Penilaian Tugas Mahasiswa.
 *
 * Endpoint:
 *   PUT /api/v1/submissions/{id}/grade
 *
 * Akses: Dosen pemilik (pengampu course tempat tugas berada).
 * Format: score, feedback — upsert, aman dipanggil berulang.
 * Status code: 201 saat pertama kali dibuat, 200 saat diperbarui.
 */
class SubmissionGradeController extends Controller
{
    /**
     * PUT /api/v1/submissions/{submission}/grade
     *
     * Memberikan atau memperbarui nilai submission.
     */
    public function update(UpsertGradeRequest $request, Submission $submission)
    {
        $user = $request->user();

        // Eager load relasi assignment dan course untuk otorisasi
        $submission->load('assignment.course');

        abort_unless(
            $user->role === 'dosen' && $submission->assignment->course->lecturer_id === $user->id,
            403,
            'Anda tidak memiliki akses ke sumber daya ini.'
        );

        // Cek apakah nilai sudah pernah ada (untuk membedakan status code 201 vs 200)
        $isNew = ! Grade::where('submission_id', $submission->id)->exists();

        $grade = Grade::updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'score'     => $request->validated('score'),
                'feedback'  => $request->validated('feedback'),
                'graded_by' => $user->id,
                'graded_at' => now(),
            ]
        );

        $grade->load('grader');

        return (new GradeResource($grade))
            ->response()
            ->setStatusCode($isNew ? 201 : 200);
    }
}
