<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Http\Request;

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
 * ⚠️ IDOR TINGGI: Dosen A jangan bisa memberi nilai
 * pada submission di MK dosen B.
 * → Akan dipindahkan ke Policy di minggu 7.
 */
class GradeController extends Controller
{
    /**
     * Simpan nilai baru untuk sebuah submission.
     *
     * Route: POST /dosen/submissions/{submission}/grades → dosen.submissions.grades.store
     * Route model binding: {submission} → Submission $submission
     */
    public function store(Request $request, Submission $submission)
    {
        abort_unless($submission->assignment->course->lecturer_id === auth()->id(), 403);
    }

    /**
     * Perbarui nilai yang sudah ada (shallow route).
     *
     * Route: PUT /dosen/grades/{grade} → dosen.grades.update
     * Route model binding: {grade} → Grade $grade
     */
    public function update(Request $request, Grade $grade)
    {
        abort_unless($grade->submission->assignment->course->lecturer_id === auth()->id(), 403);
    }
}
