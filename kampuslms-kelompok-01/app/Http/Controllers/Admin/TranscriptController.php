<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TranscriptService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Admin\TranscriptController — Pengawasan Transkrip Kumulatif Global (Tahap 3).
 *
 * Menerapkan kontrol akses berbasis peran (RBAC):
 * - Administrator memiliki hak akses global untuk verifikasi kelulusan, yudisium, dan audit akademik.
 * - Otorisasi ditegakkan via TranscriptPolicy ('viewCumulativeTranscript' & 'exportCumulativeTranscript').
 * - Komputasi terpadu dipusatkan di TranscriptService.
 */
class TranscriptController extends Controller
{
    public function __construct(
        protected TranscriptService $transcriptService
    ) {}

    /**
     * Menampilkan transkrip nilai kumulatif dan presensi tugas mahasiswa untuk audit kampus/yudisium.
     *
     * Route: GET /admin/users/{user}/transcript → admin.users.transcript.show
     */
    public function show(Request $request, User $user)
    {
        // Otorisasi audit akademik admin via TranscriptPolicy
        Gate::authorize('viewCumulativeTranscript', $user);

        // Komputasi data kumulatif via TranscriptService
        $data = $this->transcriptService->getStudentCumulativeData($user);

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        if (view()->exists('transcripts.admin')) {
            return view('transcripts.admin', [
                'data'    => $data,
                'student' => $user,
            ]);
        }

        if (view()->exists('admin.transcripts.show')) {
            return view('admin.transcripts.show', [
                'data'    => $data,
                'student' => $user,
            ]);
        }

        return view('transcripts.index', [
            'data'    => $data,
            'student' => $user,
            'type'    => 'cumulative',
        ]);
    }

    /**
     * Mengunduh berkas PDF resmi transkrip kumulatif mahasiswa oleh Admin.
     *
     * Route: GET /admin/users/{user}/transcript/export/pdf → admin.users.transcript.export.pdf
     */
    public function exportPdf(Request $request, User $user)
    {
        // Otorisasi ekspor admin via TranscriptPolicy
        Gate::authorize('exportCumulativeTranscript', $user);

        $data = $this->transcriptService->getStudentCumulativeData($user);
        $filename = "Transkrip_{$user->nim_nip}.pdf";

        if (class_exists(Pdf::class) && view()->exists('transcripts.pdf')) {
            $pdf = Pdf::loadView('transcripts.pdf', $data)
                ->setPaper('a4', 'portrait');

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

