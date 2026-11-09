<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Services\TranscriptService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Mahasiswa\TranscriptController — Transkrip Kumulatif & Rekap Presensi Tugas (Tahap 3).
 *
 * Menerapkan kontrol akses berbasis peran (RBAC):
 * - Mahasiswa hanya dapat melihat & mengunduh transkrip miliknya sendiri (Anti-IDOR).
 * - Otorisasi ditegakkan via TranscriptPolicy ('viewCumulativeTranscript' & 'exportCumulativeTranscript').
 * - Komputasi terpadu dipusatkan di TranscriptService (Bebas N+1 Query).
 */
class TranscriptController extends Controller
{
    public function __construct(
        protected TranscriptService $transcriptService
    ) {}

    /**
     * Menampilkan pratinjau (preview) transkrip nilai kumulatif dan rekap presensi tugas mahasiswa.
     *
     * Route: GET /mahasiswa/transcript → mahasiswa.transcript.index
     */
    public function index(Request $request)
    {
        $student = $request->user();

        // Otorisasi mandiri (self-service) via TranscriptPolicy
        Gate::authorize('viewCumulativeTranscript', $student);

        // Ambil data transkrip kumulatif bebas N+1
        $data = $this->transcriptService->getStudentCumulativeData($student);

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        if (view()->exists('transcripts.mahasiswa')) {
            return view('transcripts.mahasiswa', compact('data', 'student'));
        }

        if (view()->exists('mahasiswa.transcript.index')) {
            return view('mahasiswa.transcript.index', compact('data', 'student'));
        }

        return view('transcripts.index', [
            'data'    => $data,
            'student' => $student,
            'type'    => 'cumulative',
        ]);
    }

    /**
     * Mengunduh dokumen resmi transkrip nilai kumulatif dan presensi tugas dalam format PDF.
     *
     * Route: GET /mahasiswa/transcript/export/pdf → mahasiswa.transcript.export.pdf
     */
    public function exportPdf(Request $request)
    {
        $student = $request->user();

        // Otorisasi ekspor via TranscriptPolicy
        Gate::authorize('exportCumulativeTranscript', $student);

        $data = $this->transcriptService->getStudentCumulativeData($student);
        $filename = "Transkrip_{$student->nim_nip}.pdf";

        if (class_exists(Pdf::class) && view()->exists('transcripts.pdf')) {
            $pdf = Pdf::loadView('transcripts.pdf', $data)
                ->setPaper('a4', 'landscape');

            if ($request->has('preview') || $request->query('preview')) {
                return $pdf->stream($filename);
            }

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

