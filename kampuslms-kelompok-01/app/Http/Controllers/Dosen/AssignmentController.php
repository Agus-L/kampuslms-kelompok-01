<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;

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
 * ⚠️ IDOR TINGGI: Semua method harus memastikan course
 * milik dosen yang sedang login (lecturer_id === auth()->id()).
 * → Akan dipindahkan ke Policy di minggu 7.
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
        abort_unless($course->lecturer_id === auth()->id(), 403);
    }

    /**
     * Form tambah tugas baru.
     *
     * Route: GET /dosen/courses/{course}/assignments/create → dosen.courses.assignments.create
     * Route model binding: {course} → Course $course
     */
    public function create(Course $course)
    {
        abort_unless($course->lecturer_id === auth()->id(), 403);
    }

    /**
     * Simpan tugas baru ke database.
     *
     * Route: POST /dosen/courses/{course}/assignments → dosen.courses.assignments.store
     * Route model binding: {course} → Course $course
     */
    public function store(Request $request, Course $course)
    {
        abort_unless($course->lecturer_id === auth()->id(), 403);
    }

    /**
     * Detail satu tugas (shallow route).
     *
     * Route: GET /dosen/assignments/{assignment} → dosen.assignments.show
     * Route model binding: {assignment} → Assignment $assignment
     */
    public function show(Assignment $assignment)
    {
        abort_unless($assignment->course->lecturer_id === auth()->id(), 403);
    }

    /**
     * Form edit tugas (shallow route).
     *
     * Route: GET /dosen/assignments/{assignment}/edit → dosen.assignments.edit
     * Route model binding: {assignment} → Assignment $assignment
     */
    public function edit(Assignment $assignment)
    {
        abort_unless($assignment->course->lecturer_id === auth()->id(), 403);
    }

    /**
     * Simpan perubahan tugas (shallow route).
     *
     * Route: PUT /dosen/assignments/{assignment} → dosen.assignments.update
     * Route model binding: {assignment} → Assignment $assignment
     */
    public function update(Request $request, Assignment $assignment)
    {
        abort_unless($assignment->course->lecturer_id === auth()->id(), 403);
    }

    /**
     * Hapus tugas (shallow route).
     *
     * Route: DELETE /dosen/assignments/{assignment} → dosen.assignments.destroy
     * Route model binding: {assignment} → Assignment $assignment
     */
    public function destroy(Assignment $assignment)
    {
        abort_unless($assignment->course->lecturer_id === auth()->id(), 403);
    }
}
