<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
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
    public function index(Course $course)
    {
        Gate::authorize('view', $course);

        $assignments = $course->assignments()->latest()->paginate(15);

        return response()->json($assignments);
    }

    /**
     * Form tambah tugas baru.
     *
     * Route: GET /dosen/courses/{course}/assignments/create → dosen.courses.assignments.create
     * Route model binding: {course} → Course $course
     */
    public function create(Course $course)
    {
        Gate::authorize('create', [Assignment::class, $course]);
    }

    /**
     * Simpan tugas baru ke database.
     *
     * Route: POST /dosen/courses/{course}/assignments → dosen.courses.assignments.store
     * Route model binding: {course} → Course $course
     */
    public function store(Request $request, Course $course)
    {
        Gate::authorize('create', [Assignment::class, $course]);
    }

    /**
     * Detail satu tugas (shallow route).
     *
     * Route: GET /dosen/assignments/{assignment} → dosen.assignments.show
     * Route model binding: {assignment} → Assignment $assignment
     */
    public function show(Assignment $assignment)
    {
        Gate::authorize('view', $assignment);
    }

    /**
     * Form edit tugas (shallow route).
     *
     * Route: GET /dosen/assignments/{assignment}/edit → dosen.assignments.edit
     * Route model binding: {assignment} → Assignment $assignment
     */
    public function edit(Assignment $assignment)
    {
        Gate::authorize('update', $assignment);
    }

    /**
     * Simpan perubahan tugas (shallow route).
     *
     * Route: PUT /dosen/assignments/{assignment} → dosen.assignments.update
     * Route model binding: {assignment} → Assignment $assignment
     */
    public function update(Request $request, Assignment $assignment)
    {
        Gate::authorize('update', $assignment);
    }

    /**
     * Hapus tugas (shallow route).
     *
     * Route: DELETE /dosen/assignments/{assignment} → dosen.assignments.destroy
     * Route model binding: {assignment} → Assignment $assignment
     */
    public function destroy(Assignment $assignment)
    {
        Gate::authorize('delete', $assignment);
    }
}
