<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CourseDetailResource;
use App\Http\Resources\V1\CourseResource;
use App\Models\Course;
use Illuminate\Http\Request;

/**
 * API CourseController — Daftar & Detail Mata Kuliah.
 *
 * Endpoint:
 *   GET /api/v1/courses      → index (dosen: MK yang diajar; mahasiswa: MK yang diikuti)
 *   GET /api/v1/courses/{id} → show  (detail + jumlah materi/tugas, auth+scope)
 *
 * Semua response menggunakan API Resource sesuai kontrak.
 */
class CourseController extends Controller
{
    /**
     * GET /api/v1/courses
     *
     * Dosen: menampilkan MK yang diajar (lecturer_id).
     * Mahasiswa: menampilkan MK yang diikuti (pivot course_user).
     *
     * Response: collection + meta pagination.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'dosen') {
            // Dosen → mata kuliah yang diampu
            $query = Course::where('lecturer_id', $user->id)->with('lecturer');
        } else {
            // Mahasiswa → mata kuliah yang diikuti via pivot
            $query = $user->courses()->with('lecturer');
        }

        $paginated = $query->latest()->paginate(15);

        return new \App\Http\Resources\V1\CourseCollection($paginated);
    }

    /**
     * GET /api/v1/courses/{id}
     *
     * Detail mata kuliah + jumlah materi & tugas.
     * Scope check: dosen harus pengampu, mahasiswa harus enrolled.
     */
    public function show(Request $request, Course $course)
    {
        $user = $request->user();

        // Scope check — pastikan user berhak akses course ini
        if ($user->role === 'admin') {
            // Admin berhak melihat detail semua mata kuliah
        } elseif ($user->role === 'dosen') {
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

        // Eager load counts untuk menghindari N+1
        $course->loadCount(['materials', 'assignments']);
        $course->load('lecturer');

        return new CourseDetailResource($course);
    }
}
