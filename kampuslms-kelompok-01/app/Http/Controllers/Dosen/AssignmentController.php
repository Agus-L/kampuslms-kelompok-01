<?php

namespace App\Http\Controllers\Dosen;

use App\Events\AssignmentPublished;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssignmentRequest;
use App\Http\Requests\UpdateAssignmentRequest;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Dosen\AssignmentController — CRUD tugas oleh Dosen.
 *
 * Route model binding diterapkan:
 * - Route bersarang (index, create, store): menerima Course $course
 *   sebagai parent → /dosen/courses/{course}/assignments
 * - Route shallow (show, edit, update, destroy): menerima Assignment $assignment
 *   langsung → /dosen/assignments/{assignment}
 *
 * scopeBindings() pada grup dosen memastikan {assignment}
 * benar-benar milik {course} tersebut pada route bersarang.
 *
 * Otorisasi menggunakan AssignmentPolicy & CoursePolicy via Gate::authorize.
 */
class AssignmentController extends Controller
{
    /**
     * Daftar tugas dalam satu mata kuliah.
     *
     * Route: GET /dosen/courses/{course}/assignments → dosen.courses.assignments.index
     * Route model binding: {course} → Course $course
     */
    public function index(Request $request, Course $course)
    {
        Gate::authorize('view', $course);

        $assignments = $course->assignments()->latest()->paginate(15);

        return view('assignments.index', compact('course', 'assignments'));
    }

    /**
     * Form tambah tugas baru.
     *
     * Route: GET /dosen/courses/{course}/assignments/create → dosen.courses.assignments.create
     * Route model binding: {course} → Course $course
     */
    public function create(Request $request, Course $course)
    {
        Gate::authorize('create', [Assignment::class, $course]);

        return view('assignments.create', compact('course'));
    }

    /**
     * Simpan tugas baru ke database.
     *
     * Route: POST /dosen/courses/{course}/assignments → dosen.courses.assignments.store
     * Route model binding: {course} → Course $course
     */
    public function store(StoreAssignmentRequest $request, Course $course)
    {
        Gate::authorize('create', [Assignment::class, $course]);

        $data = $request->validated();
        $data['instructions'] = $data['instructions'] ?? '';
        $assignment = new Assignment($data);
        $assignment->course_id = $course->id;
        $assignment->created_by = auth()->id();

        if (!$request->has('allow_late')) {
            $assignment->allow_late = false;
        }

        if (!$request->has('status')) {
            $assignment->status = 'published';
        }

        $assignment->save();

        // =========================================================================
        // Saat event ini di-dispatch, sistem HANYA menyisipkan data ke antrean (INSERT INTO jobs).
        // Karena proses insert berhasil, web langsung redirect dengan pesan 'Tugas berhasil dibuat'.
        // Jika Queue Worker mati, error TIDAK akan muncul di sini.
        // =========================================================================
        if ($assignment->status === 'published') {
            event(new AssignmentPublished($assignment));
        }

        return redirect()->route('dosen.courses.assignments.index', $course)
            ->with('success', 'Tugas berhasil dibuat.');
    }

    /**
     * Detail satu tugas (shallow route).
     *
     * Route: GET /dosen/assignments/{assignment} → dosen.assignments.show
     * Route model binding: {assignment} → Assignment $assignment
     */
    public function show(Request $request, Assignment $assignment)
    {
        Gate::authorize('view', $assignment);

        $assignment->load(['course', 'submissions.student']);

        return view('assignments.show', compact('assignment'));
    }

    /**
     * Form edit tugas (shallow route).
     *
     * Route: GET /dosen/assignments/{assignment}/edit → dosen.assignments.edit
     * Route model binding: {assignment} → Assignment $assignment
     */
    public function edit(Request $request, Assignment $assignment)
    {
        Gate::authorize('update', $assignment);

        $assignment->load('course');

        return view('assignments.edit', compact('assignment'));
    }

    /**
     * Simpan perubahan tugas (shallow route).
     *
     * Route: PUT /dosen/assignments/{assignment} → dosen.assignments.update
     * Route model binding: {assignment} → Assignment $assignment
     */
    public function update(UpdateAssignmentRequest $request, Assignment $assignment)
    {
        Gate::authorize('update', $assignment);

        $data = $request->validated();
        $data['instructions'] = $data['instructions'] ?? '';
        if (!$request->has('allow_late')) {
            $data['allow_late'] = false;
        }

        $assignment->update($data);

        return redirect()->route('dosen.courses.assignments.index', $assignment->course_id)
            ->with('success', 'Tugas berhasil diperbarui.');
    }

    /**
     * Hapus tugas (shallow route).
     *
     * Route: DELETE /dosen/assignments/{assignment} → dosen.assignments.destroy
     * Route model binding: {assignment} → Assignment $assignment
     */
    public function destroy(Request $request, Assignment $assignment)
    {
        Gate::authorize('delete', $assignment);

        $course = $assignment->course;
        $assignment->delete();

        return redirect()->route('dosen.courses.assignments.index', $course)
            ->with('success', 'Tugas berhasil dihapus.');
    }
}
