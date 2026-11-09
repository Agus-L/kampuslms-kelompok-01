<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Services\TranscriptService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Dosen\TranscriptController — Transkrip & Rekap Presensi Tugas per Mahasiswa pada MK Diampu (Tahap 3).
 *
 * Menerapkan kontrol akses berbasis peran (RBAC) & Boundary Defense:
 * - Dosen hanya boleh mengakses data pada mata kuliah miliknya sendiri ($course->lecturer_id === auth()->id()).
 * - Dosen dilarang keras mengintip atau mengekspor transkrip mata kuliah milik dosen lain (403 Forbidden).
 * - Otorisasi ditegakkan via TranscriptPolicy ('viewStudentCourseTranscript' & 'exportStudentCourseTranscript').
 * - Komputasi terpadu dipusatkan di TranscriptService.
 */
class TranscriptController extends Controller
{
    public function __construct(
        protected TranscriptService $transcriptService
    ) {}

    /**
     * Menampilkan rincian transkrip nilai dan rekapitulasi kehadiran tugas mahasiswa
     * pada mata kuliah yang diampu oleh dosen yang sedang login.
     *
     * Route: GET /dosen/courses/{course}/students/{student}/transcript → dosen.courses.students.transcript.show
     */
    public function show(Request $request, Course $course, User $student)
    {
        // Otorisasi dosen pengampu & boundary protection via TranscriptPolicy
        Gate::authorize('viewStudentCourseTranscript', [$course, $student]);

        // Komputasi data nilai dan kehadiran tugas spesifik mata kuliah
        $data = $this->transcriptService->getStudentCourseData($course, $student);

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        if (view()->exists('transcripts.dosen')) {
            return view('transcripts.dosen', compact('data', 'course', 'student'));
        }

        if (view()->exists('dosen.transcripts.show')) {
            return view('dosen.transcripts.show', compact('data', 'course', 'student'));
        }

        return view('transcripts.course', [
            'data'    => $data,
            'course'  => $course,
            'student' => $student,
            'type'    => 'course',
        ]);
    }

    /**
     * Mengunduh berkas PDF resmi transkrip nilai dan rekap presensi tugas mahasiswa
     * khusus pada mata kuliah yang diampu.
     *
     * Route: GET /dosen/courses/{course}/students/{student}/transcript/export/pdf → dosen.courses.students.transcript.export.pdf
     */
    public function exportPdf(Request $request, Course $course, User $student)
    {
        // Otorisasi ekspor dosen pengampu via TranscriptPolicy
        Gate::authorize('exportStudentCourseTranscript', [$course, $student]);

        $data = $this->transcriptService->getStudentCourseData($course, $student);
        $filename = "Transkrip_{$course->code}_{$student->nim_nip}.pdf";

        if (class_exists(Pdf::class) && view()->exists('transcripts.pdf')) {
            $pdf = Pdf::loadView('transcripts.pdf', $data)
                ->setPaper('a4', 'portrait');

            return $pdf->download($filename);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status'   => 'success',
                'filename' => $filename,
                'data'     => $data,
            ]);
        }

        if (view()->exists('transcripts.pdf')) {
            return view('transcripts.pdf', $data);
        }

        return response()->json([
            'status'   => 'success',
            'filename' => $filename,
            'data'     => $data,
        ]);
    }
}

