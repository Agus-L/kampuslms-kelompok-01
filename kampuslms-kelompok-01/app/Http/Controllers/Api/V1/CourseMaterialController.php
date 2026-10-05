<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\MaterialCollection;
use App\Models\Course;
use Illuminate\Http\Request;

/**
 * API CourseMaterialController — Daftar Materi dalam Satu Mata Kuliah.
 *
 * Endpoint:
 *   GET /api/v1/courses/{id}/materials
 *
 * Akses: auth + scope (dosen pengampu ATAU mahasiswa terdaftar).
 */
class CourseMaterialController extends Controller
{
    /**
     * GET /api/v1/courses/{course}/materials
     *
     * Menampilkan daftar materi kuliah dalam satu mata kuliah.
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

        $materials = $course->materials()->latest()->paginate(15);

        return new MaterialCollection($materials);
    }
}
