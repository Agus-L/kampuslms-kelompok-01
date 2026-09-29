<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;

/**
 * Dosen\SubmissionController — Melihat submission mahasiswa.
 *
 * Route model binding diterapkan:
 * - Route bersarang (index): menerima Assignment $assignment
 *   sebagai parent → /dosen/assignments/{assignment}/submissions
 * - Route shallow (show): menerima Submission $submission
 *   langsung → /dosen/submissions/{submission}
 *
 * Route hanya mendaftarkan: index, show
 * (lihat ->only(['index', 'show']) di routes/web.php)
 * Dosen tidak membuat submission — itu hak mahasiswa.
 *
 * ⚠️ IDOR TINGGI: Dosen A jangan bisa melihat submission
 * di MK dosen B. Perlu cek kepemilikan.
 * → Akan dipindahkan ke Policy di minggu 7.
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
        //
    }

    /**
     * Detail satu submission (shallow route).
     *
     * Route: GET /dosen/submissions/{submission} → dosen.submissions.show
     * Route model binding: {submission} → Submission $submission
     */
    public function show(Submission $submission)
    {
        //
    }
}
