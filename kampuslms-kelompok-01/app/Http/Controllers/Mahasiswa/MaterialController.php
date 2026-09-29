<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\Request;

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
 * ⚠️ IDOR TINGGI: Mahasiswa bisa mengakses materi MK yang
 * tidak dia ikuti dengan mengganti ID di URL.
 * → Akan dipindahkan ke Policy di minggu 7.
 */
class MaterialController extends Controller
{
    /**
     * Daftar materi dalam satu mata kuliah.
     *
     * Route: GET /mahasiswa/courses/{course}/materials → mahasiswa.courses.materials.index
     * Route model binding: {course} → Course $course
     */
    public function index(Course $course)
    {
        abort_unless($course->students()->where('user_id', auth()->id())->exists(), 403);
    }

    /**
     * Detail satu materi (shallow route).
     *
     * Route: GET /mahasiswa/materials/{material} → mahasiswa.materials.show
     * Route model binding: {material} → Material $material
     */
    public function show(Material $material)
    {
        abort_unless($material->course->students()->where('user_id', auth()->id())->exists(), 403);
    }
}
