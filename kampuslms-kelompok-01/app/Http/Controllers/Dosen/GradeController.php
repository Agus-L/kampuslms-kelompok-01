<?php

namespace App\Http\Controllers\Dosen;

use App\Events\SubmissionGraded;
use App\Http\Controllers\Controller;
use App\Http\Requests\GradeRequest;
use App\Models\Course;
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
 * Route hanya mendaftarkan: store, update, rekap
 * (lihat ->only(['store', 'update']) dan route GET courses/{course}/grades di routes/web.php)
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

    /**
     * Rekap nilai (Gradebook/Buku Nilai) per mata kuliah — Poin 7 Milestone M3.
     *
     * Menampilkan tabel matriks seluruh mahasiswa vs seluruh tugas di mata kuliah
     * yang diampu dosen ini, beserta statistik kelas (rata-rata, tertinggi, terendah).
     *
     * Route: GET /dosen/courses/{course}/grades → dosen.courses.grades.rekap
     * Route model binding: {course} → Course $course
     *
     * Catatan Performa (untuk optimisasi Minggu 11):
     * - Saat ini menggunakan Eager Loading standar.
     * - Bisa dioptimasi dengan subquery atau raw aggregate di Minggu 11.
     */
    public function rekap(Course $course)
    {
        // Perbaikan Temuan 1 (review A09): Pisahkan ability viewGradebook
        Gate::authorize('viewGradebook', $course);

        // Optimisasi Minggu 11 (Penyebab #2): Ambil HANYA kolom yang dibutuhkan (hindari SELECT *)
        // Menghemat alokasi memori Eloquent, bandwidth DB, dan tidak memuat kolom sensitif/teks panjang
        $assignments = $course->assignments()
            ->select(['assignments.id', 'assignments.course_id', 'assignments.title', 'assignments.max_score'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Ambil mahasiswa dan relasi bersarang dengan kolom spesifik
        // Perbaikan Temuan 2 (review A09): Terapkan pagination 20 per halaman
        $students = $course->students()
            ->select(['users.id', 'users.name', 'users.nim_nip', 'users.email'])
            ->with(['submissions' => function ($query) use ($assignments) {
                $query->select(['submissions.id', 'submissions.assignment_id', 'submissions.user_id'])
                      ->whereIn('assignment_id', $assignments->pluck('id'))
                      ->with(['grade' => function ($gQuery) {
                          $gQuery->select(['grades.id', 'grades.submission_id', 'grades.score']);
                      }]);
            }])
            ->orderBy('name', 'asc')
            ->paginate(20)
            ->withQueryString();

        // Perbaikan Temuan 3 (review A09): Pindahkan kalkulasi nilai per mahasiswa
        // dari Blade (@php) ke Controller agar tampilan bersih (Separation of Concerns).
        $students->each(function ($student) use ($assignments) {
            $totalScore  = 0;
            $gradedCount = 0;

            foreach ($assignments as $assignment) {
                $sub = $student->submissions->firstWhere('assignment_id', $assignment->id);
                if ($sub && $sub->grade) {
                    $totalScore  += $sub->grade->score;
                    $gradedCount++;

                    // Hitung persentase per tugas dan simpan langsung di objek grade
                    $sub->grade->percentage = $assignment->max_score > 0
                        ? ($sub->grade->score / $assignment->max_score) * 100
                        : 0;
                }
            }

            // Simpan hasil perhitungan sebagai properti dinamis di objek student
            $student->graded_count  = $gradedCount;
            $student->average_score = $gradedCount > 0 ? round($totalScore / $gradedCount, 2) : null;
        });

        // Optimisasi Minggu 11 (Penyebab #1) & Perbaikan Temuan 2:
        // Raw Aggregate dihitung dari SELURUH peserta kelas (bukan dari paginator) agar statistik kelas utuh
        $assignmentIds = $assignments->pluck('id');
        $allStudentIds = $course->students()->pluck('users.id');

        $aggregate = Grade::whereHas('submission', function ($query) use ($assignmentIds, $allStudentIds) {
            $query->whereIn('assignment_id', $assignmentIds)
                  ->whereIn('user_id', $allStudentIds);
        })
        ->selectRaw('AVG(score) as avg_score, MAX(score) as max_score, MIN(score) as min_score, COUNT(score) as graded_count')
        ->first();

        $stats = (object) [
            'avg'            => ($aggregate && $aggregate->avg_score !== null) ? round((float) $aggregate->avg_score, 2) : null,
            'max'            => ($aggregate && $aggregate->max_score !== null) ? (float) $aggregate->max_score : null,
            'min'            => ($aggregate && $aggregate->min_score !== null) ? (float) $aggregate->min_score : null,
            'graded_count'   => (int) ($aggregate->graded_count ?? 0),
            'total_students' => $students->total(),
        ];

        return view('courses.grades.rekap', compact('course', 'assignments', 'students', 'stats'));
    }
}
