<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

/**
 * Mahasiswa\MaterialController — Melihat dan mengunduh materi kuliah.
 *
 * Route model binding diterapkan:
 * - Route bersarang (index): menerima Course $course
 *   sebagai parent → /mahasiswa/courses/{course}/materials
 * - Route shallow (show, download): menerima Material $material
 *   langsung → /mahasiswa/materials/{material}
 *
 * Route hanya mendaftarkan: index, show + custom download
 * Mahasiswa hanya baca dan unduh materi, tidak bisa CRUD.
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

        $course->loadMissing('lecturer');

        $materials = $course->materials()
            ->with('course')
            ->latest()
            ->paginate(15);

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

        return view('materials.show', compact('material'));
    }

    /**
     * Unduh berkas materi (shallow route).
     *
     * Route: GET /mahasiswa/materials/{material}/download → mahasiswa.materials.download
     * Route model binding: {material} → Material $material
     *
     * Akses dikendalikan oleh MaterialPolicy@download:
     * - Mahasiswa hanya bisa mengunduh berkas materi dari mata kuliah yang diikutinya
     * - Berkas dikirim dari disk privat (storage/app/private) melalui PHP,
     *   sehingga web server tidak bisa melayani berkas secara langsung
     */
    public function download(Request $request, Material $material)
    {
        Gate::authorize('download', $material);

        // Pastikan materi bertipe file dan berkas ada di disk
        if ($material->type !== 'file' || !$material->file_path) {
            abort(404, 'Berkas materi tidak tersedia.');
        }

        if (!Storage::disk('local')->exists($material->file_path)) {
            abort(404, 'Berkas tidak ditemukan di server.');
        }

        // Kirim berkas dengan nama asli yang user-friendly
        return Storage::disk('local')->download(
            $material->file_path,
            $material->original_name
        );
    }
}
