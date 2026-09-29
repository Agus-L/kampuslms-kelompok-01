<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\Request;

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
 * ⚠️ IDOR TINGGI: Semua method harus memastikan course
 * milik dosen yang sedang login (lecturer_id === auth()->id()).
 * → Akan dipindahkan ke Policy di minggu 7.
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
        //
    }

    /**
     * Form tambah materi baru.
     *
     * Route: GET /dosen/courses/{course}/materials/create → dosen.courses.materials.create
     * Route model binding: {course} → Course $course
     */
    public function create(Course $course)
    {
        //
    }

    /**
     * Simpan materi baru ke database.
     *
     * Route: POST /dosen/courses/{course}/materials → dosen.courses.materials.store
     * Route model binding: {course} → Course $course
     */
    public function store(Request $request, Course $course)
    {
        //
    }

    /**
     * Detail satu materi (shallow route).
     *
     * Route: GET /dosen/materials/{material} → dosen.materials.show
     * Route model binding: {material} → Material $material
     */
    public function show(Material $material)
    {
        //
    }

    /**
     * Form edit materi (shallow route).
     *
     * Route: GET /dosen/materials/{material}/edit → dosen.materials.edit
     * Route model binding: {material} → Material $material
     */
    public function edit(Material $material)
    {
        //
    }

    /**
     * Simpan perubahan materi (shallow route).
     *
     * Route: PUT /dosen/materials/{material} → dosen.materials.update
     * Route model binding: {material} → Material $material
     */
    public function update(Request $request, Material $material)
    {
        //
    }

    /**
     * Hapus materi (shallow route).
     *
     * Route: DELETE /dosen/materials/{material} → dosen.materials.destroy
     * Route model binding: {material} → Material $material
     */
    public function destroy(Material $material)
    {
        //
    }
}
