<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Http\Request;

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
 * ⚠️ IDOR TINGGI: Route ini harus di-scope ke
 * auth()->user()->submissions agar mahasiswa tidak bisa
 * melihat nilai submission orang lain.
 * → Akan dipindahkan ke Policy di minggu 7.
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
        //
    }
}
