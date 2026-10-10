<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Dosen\MaterialController — CRUD materi kuliah oleh Dosen.
 *
 * Route model binding diterapkan:
 * - Route bersarang (index, create, store): menerima Course $course
 *   sebagai parent → /dosen/courses/{course}/materials
 * - Route shallow (show, edit, update, destroy): menerima Material $material
 *   langsung → /dosen/materials/{material}
 *
 * scopeBindings() pada grup dosen memastikan {material}
 * benar-benar milik {course} tersebut pada route bersarang.
 *
 * Otorisasi menggunakan MaterialPolicy & CoursePolicy via Gate::authorize.
 */
class MaterialController extends Controller
{
    /**
     * Daftar materi dalam satu mata kuliah.
     *
     * Route: GET /dosen/courses/{course}/materials → dosen.courses.materials.index
     * Route model binding: {course} → Course $course
     */
    public function index(Course $course)
    {
        Gate::authorize('view', $course);
    }

    /**
     * Form tambah materi baru.
     *
     * Route: GET /dosen/courses/{course}/materials/create → dosen.courses.materials.create
     * Route model binding: {course} → Course $course
     */
    public function create(Course $course)
    {
        Gate::authorize('create', [Material::class, $course]);
    }

    /**
     * Simpan materi baru ke database.
     *
     * Route: POST /dosen/courses/{course}/materials → dosen.courses.materials.store
     * Route model binding: {course} → Course $course
     */
    public function store(Request $request, Course $course)
    {
        Gate::authorize('create', [Material::class, $course]);
    }

    /**
     * Detail satu materi (shallow route).
     *
     * Route: GET /dosen/materials/{material} → dosen.materials.show
     * Route model binding: {material} → Material $material
     */
    public function show(Material $material)
    {
        Gate::authorize('view', $material);
    }

    /**
     * Form edit materi (shallow route).
     *
     * Route: GET /dosen/materials/{material}/edit → dosen.materials.edit
     * Route model binding: {material} → Material $material
     */
    public function edit(Material $material)
    {
        Gate::authorize('update', $material);
    }

    /**
     * Simpan perubahan materi (shallow route).
     *
     * Route: PUT /dosen/materials/{material} → dosen.materials.update
     * Route model binding: {material} → Material $material
     */
    public function update(Request $request, Material $material)
    {
        Gate::authorize('update', $material);
    }

    /**
     * Hapus materi (shallow route).
     *
     * Route: DELETE /dosen/materials/{material} → dosen.materials.destroy
     * Route model binding: {material} → Material $material
     */
    public function destroy(Material $material)
    {
        Gate::authorize('delete', $material);
    }
}
