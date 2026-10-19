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
 * - Route shallow (show, edit, update, download): menerima Submission $submission
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
        // Jika sudah pernah submit, arahkan ke form edit (updateOrCreate)
        $existing = $assignment->submissions()->where('user_id', auth()->id())->first();
        if ($existing) {
            return redirect()->route('mahasiswa.submissions.edit', $existing)
                ->with('info', 'Anda sudah mengumpulkan tugas ini. Silakan perbarui di sini.');
        }

        Gate::authorize('create', [Submission::class, $assignment]);

        return view('submissions.create', compact('assignment'));
    }

    /**
     * Simpan submission baru (atau timpa yang lama via updateOrCreate).
     *
     * Route: POST /mahasiswa/assignments/{assignment}/submissions → mahasiswa.assignments.submissions.store
     * Route model binding: {assignment} → Assignment $assignment
     *
     * Ketentuan modul minggu 9:
     * - Berkas disimpan di disk privat (local), BUKAN storage/app/public
     * - Nama berkas fisik acak (store() otomatis), nama asli di kolom original_name
     * - Validasi: mimes pdf/doc/docx, maks 5 MB, wajib ada
     * - updateOrCreate agar submit ulang menimpa submission lama
     * - is_late dihitung di server
     * - Tolak jika allow_late = false dan sudah lewat deadline
     */
    public function store(Request $request, Assignment $assignment)
    {
        $existing = $assignment->submissions()->where('user_id', auth()->id())->first();
        if ($existing) {
            Gate::authorize('update', $existing);
        } else {
            Gate::authorize('create', [Submission::class, $assignment]);
        }

        // Tolak jika sudah lewat deadline dan allow_late = false
        if (!$assignment->allow_late && now()->gt($assignment->due_at)) {
            return back()->withErrors([
                'file' => 'Batas waktu pengumpulan telah terlewati dan tugas ini tidak mengizinkan keterlambatan.',
            ]);
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],   // 5 MB
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'file.required' => 'Berkas tugas wajib diunggah.',
            'file.max'      => 'Ukuran berkas maksimal 5 MB.',
            'file.mimes'    => 'Format berkas harus PDF, DOC, atau DOCX.',
        ]);

        $file = $request->file('file');

        // Simpan di disk privat dengan nama acak (store() otomatis)
        $path = $file->store("submissions/{$assignment->id}", 'local');

        // Hapus berkas lama jika ada (untuk kasus updateOrCreate menimpa)
        $oldSubmission = $assignment->submissions()->where('user_id', auth()->id())->first();
        if ($oldSubmission && $oldSubmission->file_path && Storage::disk('local')->exists($oldSubmission->file_path)) {
            Storage::disk('local')->delete($oldSubmission->file_path);
        }

        // updateOrCreate: mahasiswa yang submit ulang menimpa submission lama
        // user_id tidak di $fillable (keamanan), tapi dipakai di kolom pencarian
        Submission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'user_id' => auth()->id()],
            [
                'file_path'     => $path,
                'original_name' => $file->getClientOriginalName(),
                'file_size'     => $file->getSize(),
                'note'          => $request->note,
                'submitted_at'  => now(),
                'is_late'       => now()->gt($assignment->due_at),
            ]
        );

        $statusMessage = now()->gt($assignment->due_at)
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

        $assignment = $submission->assignment;

        // Tolak jika sudah lewat deadline dan allow_late = false
        if (!$assignment->allow_late && now()->gt($assignment->due_at)) {
            return back()->withErrors([
                'file' => 'Batas waktu pengumpulan telah terlewati dan tugas ini tidak mengizinkan keterlambatan.',
            ]);
        }

        $request->validate([
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],   // 5 MB
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'file.max'   => 'Ukuran berkas maksimal 5 MB.',
            'file.mimes' => 'Format berkas harus PDF, DOC, atau DOCX.',
        ]);

        $data = [
            'note' => $request->note,
        ];

        if ($request->hasFile('file')) {
            // Hapus berkas lama dari disk privat
            if (Storage::disk('local')->exists($submission->file_path)) {
                Storage::disk('local')->delete($submission->file_path);
            }
            $file = $request->file('file');
            $data['file_path'] = $file->store("submissions/{$submission->assignment_id}", 'local');
            $data['original_name'] = $file->getClientOriginalName();
            $data['file_size'] = $file->getSize();
            $data['submitted_at'] = now();
            $data['is_late'] = now()->gt($assignment->due_at);
        }

        $submission->update($data);

        return redirect()->route('mahasiswa.assignments.show', $submission->assignment)
            ->with('success', 'Pengumpulan tugas berhasil diperbarui.');
    }

    /**
     * Unduh berkas submission (endpoint terpisah dengan otorisasi Policy).
     *
     * Route: GET /mahasiswa/submissions/{submission}/download → mahasiswa.submissions.download
     * Route model binding: {submission} → Submission $submission
     *
     * Akses dikendalikan oleh SubmissionPolicy@download:
     * - Mahasiswa hanya bisa mengunduh berkas submission miliknya sendiri
     * - Berkas dikirim dari disk privat (storage/app/private) melalui PHP,
     *   sehingga web server tidak bisa melayani berkas secara langsung
     */
    public function download(Submission $submission)
    {
        Gate::authorize('download', $submission);

        abort_unless(Storage::disk('local')->exists($submission->file_path), 404);

        return Storage::disk('local')->download(
            $submission->file_path,
            $submission->original_name
        );
    }
}
