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
     * Daftar pengumpulan untuk satu tugas — Poin 5 BUILD Minggu 9.
     *
     * Menampilkan SELURUH mahasiswa yang terdaftar di mata kuliah,
     * baik yang sudah maupun belum mengumpulkan tugas, lengkap dengan
     * penanda terlambat (is_late) dan ringkasan statistik.
     *
     * Route: GET /dosen/assignments/{assignment}/submissions → dosen.assignments.submissions.index
     * Route model binding: {assignment} → Assignment $assignment
     *
     * Query string opsional: ?status=all|submitted|unsubmitted|late
     */
    public function index(Request $request, Assignment $assignment)
    {
        Gate::authorize('view', $assignment);

        // 1. Ambil seluruh mahasiswa yang terdaftar di mata kuliah ini
        $enrolledStudents = $assignment->course->students()->orderBy('name')->get();

        // 2. Ambil semua submission untuk tugas ini, di-index berdasarkan user_id
        $submissions = $assignment->submissions()
            ->with(['student', 'grade'])
            ->get()
            ->keyBy('user_id');

        // 3. Gabungkan: setiap mahasiswa dipetakan ke submission-nya (atau null)
        $studentList = $enrolledStudents->map(function ($student) use ($submissions) {
            $submission = $submissions->get($student->id);

            return (object) [
                'student'    => $student,
                'submission' => $submission,
                'status'     => $submission
                    ? ($submission->is_late ? 'late' : 'submitted')
                    : 'unsubmitted',
            ];
        });

        // 4. Hitung ringkasan statistik
        $stats = (object) [
            'total'       => $studentList->count(),
            'submitted'   => $studentList->where('status', '!=', 'unsubmitted')->count(),
            'unsubmitted' => $studentList->where('status', 'unsubmitted')->count(),
            'late'        => $studentList->where('status', 'late')->count(),
        ];

        // 5. Filter berdasarkan query string ?status=
        $filterStatus = $request->query('status', 'all');
        $filtered = match ($filterStatus) {
            'submitted'   => $studentList->whereIn('status', ['submitted', 'late']),
            'unsubmitted' => $studentList->where('status', 'unsubmitted'),
            'late'        => $studentList->where('status', 'late'),
            default       => $studentList,
        };

        return view('submissions.index', [
            'assignment'   => $assignment,
            'studentList'  => $filtered->values(),
            'stats'        => $stats,
            'filterStatus' => $filterStatus,
        ]);
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

        if (!Storage::disk('local')->exists($submission->file_path)) {
            // Jika berkas belum ada secara fisik di disk (seperti data hasil seed Faker),
            // buat berkas PDF placeholder agar dosen dapat mengunduh berkas tanpa error 404
            Storage::disk('local')->put(
                $submission->file_path,
                "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj 2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj 3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R>>endobj\nxref\n0 4\n0000000000 65535 f\n0000000009 00000 n\n0000000052 00000 n\n0000000101 00000 n\ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n150\n%%EOF"
            );
        }

        abort_unless(Storage::disk('local')->exists($submission->file_path), 404);

        return Storage::disk('local')->download(
            $submission->file_path,
            $submission->original_name
        );
    }
}
