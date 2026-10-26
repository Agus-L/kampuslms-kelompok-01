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
        Gate::authorize('view', $course);

        // Ambil semua tugas di MK ini, urut berdasarkan waktu pembuatan
        $assignments = $course->assignments()
            ->orderBy('created_at', 'asc')
            ->get();

        // Ambil semua mahasiswa yang terdaftar beserta submission & nilai
        // untuk tugas-tugas di MK ini
        $students = $course->students()
            ->with(['submissions' => function ($query) use ($assignments) {
                $query->whereIn('assignment_id', $assignments->pluck('id'))
                      ->with('grade');
            }])
            ->orderBy('name', 'asc')
            ->get();

        // Hitung statistik kelas
        $allScores = collect();
        foreach ($students as $student) {
            foreach ($student->submissions as $sub) {
                if ($sub->grade && $sub->grade->score !== null) {
                    $allScores->push($sub->grade->score);
                }
            }
        }

        $stats = (object) [
            'avg'          => $allScores->isNotEmpty() ? round($allScores->avg(), 2) : null,
            'max'          => $allScores->isNotEmpty() ? $allScores->max() : null,
            'min'          => $allScores->isNotEmpty() ? $allScores->min() : null,
            'graded_count' => $allScores->count(),
            'total_students' => $students->count(),
        ];

        return view('courses.grades.rekap', compact('course', 'assignments', 'students', 'stats'));
    }
}
