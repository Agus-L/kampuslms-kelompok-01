<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Mahasiswa\MaterialController — Melihat materi kuliah.
 *
 * Route model binding diterapkan:
 * - Route bersarang (index): menerima Course $course
 *   sebagai parent → /mahasiswa/courses/{course}/materials
 * - Route shallow (show): menerima Material $material
 *   langsung → /mahasiswa/materials/{material}
 *
 * Route hanya mendaftarkan: index, show
 * (lihat ->only(['index', 'show']) di routes/web.php)
 * Mahasiswa hanya baca materi, tidak bisa CRUD.
 *
 * Otorisasi menggunakan MaterialPolicy & CoursePolicy via Gate::authorize.
 */
class MaterialController extends Controller
{
    /**
     * Daftar materi dalam satu mata kuliah yang diikuti.
     *
     * Route: GET /mahasiswa/courses/{course}/materials → mahasiswa.courses.materials.index
     * Route model binding: {course} → Course $course
     */
    public function index(Request $request, Course $course)
    {
        Gate::authorize('view', $course);

        $materials = $course->materials()->latest()->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($materials);
        }

        return view('materials.index', compact('course', 'materials'));
    }

    /**
     * Detail satu materi (shallow route).
     *
     * Route: GET /mahasiswa/materials/{material} → mahasiswa.materials.show
     * Route model binding: {material} → Material $material
     */
    public function show(Request $request, Material $material)
    {
        Gate::authorize('view', $material);

        $material->load('course');

        if ($request->wantsJson()) {
            return response()->json($material);
        }

        return view('materials.show', compact('material'));
    }
}
