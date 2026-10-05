<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreAssignmentRequest;
use App\Http\Requests\Api\V1\UpdateAssignmentRequest;
use App\Http\Resources\V1\AssignmentResource;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;

/**
 * API AssignmentController — CRUD Tugas (standalone).
 *
 * Endpoint:
 *   POST       /api/v1/assignments           → store  (dosen, 201)
 *   PUT/PATCH  /api/v1/assignments/{id}       → update (dosen pemilik, 200)
 *   DELETE     /api/v1/assignments/{id}       → destroy(dosen pemilik, 204)
 *
 * Otorisasi:
 *   - Hanya dosen yang bisa membuat tugas
 *   - Hanya dosen pemilik (pengampu course) yang bisa update/delete
 */
class AssignmentController extends Controller
{
    /**
     * POST /api/v1/assignments
     *
     * Membuat tugas baru. created_by diisi dari auth.
     * Dosen harus pengampu course_id yang dikirim.
     */
    public function store(StoreAssignmentRequest $request)
    {
        $user = $request->user();

        // Cek: hanya dosen
        abort_unless(
            $user->role === 'dosen',
            403,
            'Anda tidak memiliki akses ke sumber daya ini.'
        );

        // Cek: dosen harus pengampu course yang dikirim
        $course = Course::findOrFail($request->validated('course_id'));
        abort_unless(
            $course->lecturer_id === $user->id,
            403,
            'Anda tidak memiliki akses ke sumber daya ini.'
        );

        // Buat assignment — created_by diisi dari server, bukan input user
        $assignment = new Assignment($request->validated());
        $assignment->created_by = $user->id;
        $assignment->save();

        return (new AssignmentResource($assignment))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * PUT/PATCH /api/v1/assignments/{assignment}
     *
     * Memperbarui tugas. Hanya dosen pemilik (pengampu course).
     */
    public function update(UpdateAssignmentRequest $request, Assignment $assignment)
    {
        $user = $request->user();

        // Eager load course untuk cek kepemilikan
        $assignment->load('course');

        // Cek: dosen harus pengampu course tempat assignment ini berada
        abort_unless(
            $user->role === 'dosen' && $assignment->course->lecturer_id === $user->id,
            403,
            'Anda tidak memiliki akses ke sumber daya ini.'
        );

        $assignment->update($request->validated());

        return new AssignmentResource($assignment);
    }

    /**
     * DELETE /api/v1/assignments/{assignment}
     *
     * Menghapus tugas. Hanya dosen pemilik. Response 204 No Content.
     */
    public function destroy(Request $request, Assignment $assignment)
    {
        $user = $request->user();

        // Eager load course untuk cek kepemilikan
        $assignment->load('course');

        // Cek: dosen harus pengampu course tempat assignment ini berada
        abort_unless(
            $user->role === 'dosen' && $assignment->course->lecturer_id === $user->id,
            403,
            'Anda tidak memiliki akses ke sumber daya ini.'
        );

        $assignment->delete();

        return response()->noContent(); // 204
    }
}
