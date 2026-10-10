<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
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
     * Form tambah materi baru.
     *
     * Route: GET /dosen/courses/{course}/materials/create → dosen.courses.materials.create
     * Route model binding: {course} → Course $course
     */
    public function create(Request $request, Course $course)
    {
        Gate::authorize('create', [Material::class, $course]);

        if ($request->wantsJson()) {
            return response()->json(['course' => $course]);
        }

        return view('materials.create', compact('course'));
    }

    /**
     * Simpan materi baru ke database.
     *
     * Route: POST /dosen/courses/{course}/materials → dosen.courses.materials.store
     * Route model binding: {course} → Course $course
     */
    public function store(StoreMaterialRequest $request, Course $course)
    {
        Gate::authorize('create', [Material::class, $course]);

        $data = $request->validated();
        $data['description'] = $data['description'] ?? '';
        $material = new Material($data);
        $material->course_id = $course->id;
        $material->uploaded_by = auth()->id();

        // Metadata berkas (Minggu 7 metadata-only, sebelum upload fisik Minggu 9)
        if ($material->type === 'file' && empty($material->file_path)) {
            $material->original_name = $material->original_name ?: $material->title . '.pdf';
            $material->file_path = $material->file_path ?: 'materials/' . $material->original_name;
            $material->mime_type = $material->mime_type ?: 'application/pdf';
            $material->file_size = $material->file_size ?: 102400; // 100 KB default metadata
        }

        $material->save();

        if ($request->wantsJson()) {
            return response()->json([
                'message'  => 'Materi berhasil ditambahkan.',
                'material' => $material,
            ], 201);
        }

        return redirect()->route('dosen.courses.materials.index', $course)
            ->with('success', 'Materi berhasil ditambahkan.');
    }

    /**
     * Detail satu materi (shallow route).
     *
     * Route: GET /dosen/materials/{material} → dosen.materials.show
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

    /**
     * Form edit materi (shallow route).
     *
     * Route: GET /dosen/materials/{material}/edit → dosen.materials.edit
     * Route model binding: {material} → Material $material
     */
    public function edit(Request $request, Material $material)
    {
        Gate::authorize('update', $material);

        $material->load('course');

        if ($request->wantsJson()) {
            return response()->json($material);
        }

        return view('materials.edit', compact('material'));
    }

    /**
     * Simpan perubahan materi (shallow route).
     *
     * Route: PUT /dosen/materials/{material} → dosen.materials.update
     * Route model binding: {material} → Material $material
     */
    public function update(UpdateMaterialRequest $request, Material $material)
    {
        Gate::authorize('update', $material);

        $data = $request->validated();
        $data['description'] = $data['description'] ?? '';
        $material->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'message'  => 'Materi berhasil diperbarui.',
                'material' => $material,
            ]);
        }

        return redirect()->route('dosen.courses.materials.index', $material->course_id)
            ->with('success', 'Materi berhasil diperbarui.');
    }

    /**
     * Hapus materi (shallow route).
     *
     * Route: DELETE /dosen/materials/{material} → dosen.materials.destroy
     * Route model binding: {material} → Material $material
     */
    public function destroy(Request $request, Material $material)
    {
        Gate::authorize('delete', $material);

        $course = $material->course;
        $material->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Materi berhasil dihapus.',
            ], 200);
        }

        return redirect()->route('dosen.courses.materials.index', $course)
            ->with('success', 'Materi berhasil dihapus.');
    }
}
