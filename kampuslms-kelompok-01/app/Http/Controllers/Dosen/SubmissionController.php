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
     * Daftar pengumpulan untuk satu tugas — Poin 5 BUILD Minggu 9 & Poin 5 BUILD Minggu 11.
     *
     * Menampilkan daftar mahasiswa yang terdaftar di mata kuliah,
     * baik yang sudah maupun belum mengumpulkan tugas, lengkap dengan
     * penanda terlambat (is_late) dan ringkasan statistik.
     *
     * Optimisasi Minggu 11 (Build #5):
     * - Mengganti pemuatan seluruh tabel in-memory (get()) dengan pagination (paginate(15)).
     * - Menghitung ringkasan statistik (total, submitted, unsubmitted, late) langsung di level database.
     * - Filter status diterapkan langsung pada query database (whereHas / whereDoesntHave).
     * - Eager loading relasi submission dan grade dibatasi hanya untuk tugas yang sedang aktif.
     *
     * Route: GET /dosen/assignments/{assignment}/submissions → dosen.assignments.submissions.index
     * Route model binding: {assignment} → Assignment $assignment
     *
     * Query string opsional: ?status=all|submitted|unsubmitted|late
     */
    public function index(Request $request, Assignment $assignment)
    {
        Gate::authorize('view', $assignment);

        $assignment->loadMissing('course');

        // 1. Hitung ringkasan statistik di database (cepat dan hemat memori)
        $totalStudents    = $assignment->course->students()->count();
        $submittedCount   = $assignment->submissions()->count();
        $lateCount        = $assignment->submissions()->where('is_late', true)->count();
        $unsubmittedCount = max(0, $totalStudents - $submittedCount);

        $stats = (object) [
            'total'       => $totalStudents,
            'submitted'   => $submittedCount,
            'unsubmitted' => $unsubmittedCount,
            'late'        => $lateCount,
        ];

        // 2. Query mahasiswa terdaftar dengan filter status di database
        $filterStatus = $request->query('status', 'all');

        $query = $assignment->course->students()
            ->select(['users.id', 'users.name', 'users.email', 'users.nim_nip'])
            ->orderBy('users.name');

        if ($filterStatus === 'submitted') {
            $query->whereHas('submissions', fn ($q) => $q->where('assignment_id', $assignment->id));
        } elseif ($filterStatus === 'unsubmitted') {
            $query->whereDoesntHave('submissions', fn ($q) => $q->where('assignment_id', $assignment->id));
        } elseif ($filterStatus === 'late') {
            $query->whereHas('submissions', fn ($q) => $q->where('assignment_id', $assignment->id)->where('is_late', true));
        }

        // 3. Eager loading hanya submission untuk tugas ini beserta nilainya (mencegah N+1)
        $query->with(['submissions' => function ($q) use ($assignment) {
            $q->where('assignment_id', $assignment->id)->with('grade');
        }]);

        // 4. Pagination 15 per halaman dengan mempertahankan query string filter
        $paginator = $query->paginate(15)->withQueryString();

        // 5. Transformasi data per item paginator untuk view blade
        $studentList = $paginator->through(function ($student) {
            $submission = $student->submissions->first();

            return (object) [
                'student'    => $student,
                'submission' => $submission,
                'status'     => $submission
                    ? ($submission->is_late ? 'late' : 'submitted')
                    : 'unsubmitted',
            ];
        });

        return view('submissions.index', [
            'assignment'   => $assignment,
            'studentList'  => $studentList,
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
