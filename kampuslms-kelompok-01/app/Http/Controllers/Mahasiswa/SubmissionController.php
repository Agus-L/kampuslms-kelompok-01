<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

use Illuminate\Support\Facades\Storage;

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
 * Otorisasi menggunakan SubmissionPolicy & AssignmentPolicy via Gate::authorize.
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
        Gate::authorize('view', $assignment);

        $submissions = $assignment->submissions()
            ->where('user_id', auth()->id())
            ->with('grade')
            ->get();

        return view('submissions.index', compact('assignment', 'submissions'));
    }

    /**
     * Form untuk mengumpulkan tugas baru.
     *
     * Route: GET /mahasiswa/assignments/{assignment}/submissions/create → mahasiswa.assignments.submissions.create
     * Route model binding: {assignment} → Assignment $assignment
     */
    public function create(Assignment $assignment)
    {
        Gate::authorize('create', [Submission::class, $assignment]);

        $existing = $assignment->submissions()->where('user_id', auth()->id())->first();
        if ($existing) {
            return redirect()->route('mahasiswa.assignments.show', $assignment)
                ->with('error', 'Anda sudah mengumpulkan tugas ini.');
        }

        return view('submissions.create', compact('assignment'));
    }

    /**
     * Simpan submission baru.
     *
     * Route: POST /mahasiswa/assignments/{assignment}/submissions → mahasiswa.assignments.submissions.store
     * Route model binding: {assignment} → Assignment $assignment
     */
    public function store(Request $request, Assignment $assignment)
    {
        Gate::authorize('create', [Submission::class, $assignment]);

        $request->validate([
            'file' => ['required', 'file', 'max:20480', 'mimes:pdf,zip,rar,doc,docx,ppt,pptx,xls,xlsx,txt,jpg,jpeg,png'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'file.required' => 'Berkas tugas wajib diunggah.',
            'file.max'      => 'Ukuran berkas maksimal 20 MB.',
            'file.mimes'    => 'Format berkas harus berupa dokumen, arsip zip/rar, atau gambar.',
        ]);

        $file = $request->file('file');
        $filePath = $file->store('submissions/' . $assignment->id, 'public');

        $isLate = now()->isAfter($assignment->due_at);

        $submission = new Submission([
            'assignment_id' => $assignment->id,
            'file_path'     => $filePath,
            'original_name' => $file->getClientOriginalName(),
            'file_size'     => $file->getSize(),
            'note'          => $request->note,
            'submitted_at'  => now(),
            'is_late'       => $isLate,
        ]);
        $submission->user_id = auth()->id();
        $submission->save();

        $statusMessage = $isLate
            ? 'Tugas berhasil dikumpulkan (Terlambat melewati batas waktu).'
            : 'Tugas berhasil dikumpulkan tepat waktu.';

        return redirect()->route('mahasiswa.assignments.show', $assignment)
            ->with('success', $statusMessage);
    }

    /**
     * Detail satu submission (shallow route).
     *
     * Route: GET /mahasiswa/submissions/{submission} → mahasiswa.submissions.show
     * Route model binding: {submission} → Submission $submission
     */
    public function show(Submission $submission)
    {
        Gate::authorize('view', $submission);

        $submission->load(['assignment.course', 'grade.grader']);

        return view('submissions.show', compact('submission'));
    }

    /**
     * Form edit submission (shallow route).
     *
     * Route: GET /mahasiswa/submissions/{submission}/edit → mahasiswa.submissions.edit
     * Route model binding: {submission} → Submission $submission
     */
    public function edit(Submission $submission)
    {
        Gate::authorize('update', $submission);

        $assignment = $submission->assignment;

        return view('submissions.edit', compact('submission', 'assignment'));
    }

    /**
     * Simpan perubahan submission (shallow route).
     *
     * Route: PUT /mahasiswa/submissions/{submission} → mahasiswa.submissions.update
     * Route model binding: {submission} → Submission $submission
     */
    public function update(Request $request, Submission $submission)
    {
        Gate::authorize('update', $submission);

        $request->validate([
            'file' => ['nullable', 'file', 'max:20480', 'mimes:pdf,zip,rar,doc,docx,ppt,pptx,xls,xlsx,txt,jpg,jpeg,png'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'file.max'   => 'Ukuran berkas maksimal 20 MB.',
            'file.mimes' => 'Format berkas harus berupa dokumen, arsip zip/rar, atau gambar.',
        ]);

        $data = [
            'note' => $request->note,
        ];

        if ($request->hasFile('file')) {
            if (Storage::disk('public')->exists($submission->file_path)) {
                Storage::disk('public')->delete($submission->file_path);
            }
            $file = $request->file('file');
            $data['file_path'] = $file->store('submissions/' . $submission->assignment_id, 'public');
            $data['original_name'] = $file->getClientOriginalName();
            $data['file_size'] = $file->getSize();
            $data['submitted_at'] = now();
            $data['is_late'] = now()->isAfter($submission->assignment->due_at);
        }

        $submission->update($data);

        return redirect()->route('mahasiswa.assignments.show', $submission->assignment)
            ->with('success', 'Pengumpulan tugas berhasil diperbarui.');
    }
}
