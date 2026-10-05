<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AssignmentResource;
use App\Models\Course;
use Illuminate\Http\Request;

/**
 * API CourseAssignmentController — Daftar Tugas dalam Satu Mata Kuliah.
 *
 * Endpoint:
 *   GET /api/v1/courses/{id}/assignments
 *
 * Mendukung filter ?status= dan ?page= sesuai kontrak.
 * Akses: auth + scope (dosen pengampu ATAU mahasiswa terdaftar).
 */
class CourseAssignmentController extends Controller
{
    /**
     * GET /api/v1/courses/{course}/assignments
     *
     * Menampilkan daftar tugas dalam satu mata kuliah.
     * Support query params: ?status=draft|published, ?page=N
     */
    public function index(Request $request, Course $course)
    {
        $user = $request->user();

        // Scope check — pastikan user berhak akses course ini
        if ($user->role === 'dosen') {
            abort_unless(
                $course->lecturer_id === $user->id,
                403,
                'Anda tidak memiliki akses ke sumber daya ini.'
            );
        } else {
            abort_unless(
                $course->students()->where('user_id', $user->id)->exists(),
                403,
                'Anda tidak memiliki akses ke sumber daya ini.'
            );
        }

        // Query assignments dengan filter opsional
        $query = $course->assignments()
            ->withCount('submissions')
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->query('status'));
            })
            ->latest();

        $paginated = $query->paginate(15);

        return new \App\Http\Resources\V1\AssignmentCollection($paginated);
    }
}
