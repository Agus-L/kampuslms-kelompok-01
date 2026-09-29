<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;

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
 * ⚠️ IDOR SANGAT KRITIS: Mahasiswa Budi bisa ganti ID untuk
 * melihat submission mahasiswa Ani beserta nilainya.
 * → Akan dipindahkan ke Policy di minggu 7.
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
        //
    }

    /**
     * Form untuk mengumpulkan tugas baru.
     *
     * Route: GET /mahasiswa/assignments/{assignment}/submissions/create → mahasiswa.assignments.submissions.create
     * Route model binding: {assignment} → Assignment $assignment
     */
    public function create(Assignment $assignment)
    {
        //
    }

    /**
     * Simpan submission baru.
     *
     * Route: POST /mahasiswa/assignments/{assignment}/submissions → mahasiswa.assignments.submissions.store
     * Route model binding: {assignment} → Assignment $assignment
     */
    public function store(Request $request, Assignment $assignment)
    {
        //
    }

    /**
     * Detail satu submission (shallow route).
     *
     * Route: GET /mahasiswa/submissions/{submission} → mahasiswa.submissions.show
     * Route model binding: {submission} → Submission $submission
     */
    public function show(Submission $submission)
    {
        //
    }

    /**
     * Form edit submission (shallow route).
     *
     * Route: GET /mahasiswa/submissions/{submission}/edit → mahasiswa.submissions.edit
     * Route model binding: {submission} → Submission $submission
     */
    public function edit(Submission $submission)
    {
        //
    }

    /**
     * Simpan perubahan submission (shallow route).
     *
     * Route: PUT /mahasiswa/submissions/{submission} → mahasiswa.submissions.update
     * Route model binding: {submission} → Submission $submission
     */
    public function update(Request $request, Submission $submission)
    {
        //
    }
}
