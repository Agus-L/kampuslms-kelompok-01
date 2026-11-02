<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMaterialRequest;
use App\Http\Requests\UpdateMaterialRequest;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

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
 *
 * Penyimpanan berkas (Minggu 9):
 * - Berkas disimpan di disk `local` (storage/app/private/materials/courses/{course_id}/)
 * - Nama berkas fisik menggunakan hash acak; nama asli disimpan di kolom `original_name`
 * - Berkas TIDAK disimpan di storage/app/public untuk mencegah akses langsung tanpa otorisasi
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

        $course->loadMissing('lecturer');

        $materials = $course->materials()
            ->with('course')
            ->latest()
            ->paginate(15);

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

        return view('materials.create', compact('course'));
    }

    /**
     * Simpan materi baru ke database beserta berkas fisiknya.
     *
     * Route: POST /dosen/courses/{course}/materials → dosen.courses.materials.store
     * Route model binding: {course} → Course $course
     *
     * Alur penyimpanan berkas:
     * 1. Validasi mimes (pdf, pptx, doc, docx) dan ukuran (max 20 MB) di StoreMaterialRequest
     * 2. Simpan berkas ke disk `local` dengan nama acak (hashName) di folder materials/courses/{id}/
     * 3. Simpan nama asli (getClientOriginalName) ke kolom `original_name` agar pengguna
     *    melihat nama yang familiar saat mengunduh
     */
    public function store(StoreMaterialRequest $request, Course $course)
    {
        Gate::authorize('create', [Material::class, $course]);

        $data = $request->validated();
        $data['description'] = $data['description'] ?? '';
        $material = new Material($data);
        $material->course_id = $course->id;
        $material->uploaded_by = auth()->id();

        // ─── Upload berkas fisik ke disk privat ───
        if ($material->type === 'file' && $request->hasFile('file')) {
            $uploadedFile = $request->file('file');

            // Simpan ke disk `local` (storage/app/private/materials/courses/{course_id}/)
            // storeAs() dengan hashName() menghasilkan nama acak, mencegah tebakan URL
            $directory = 'materials/courses/' . $course->id;
            $storedPath = $uploadedFile->store($directory, 'local');

            $material->file_path     = $storedPath;
            $material->original_name = $uploadedFile->getClientOriginalName();
            $material->file_size     = $uploadedFile->getSize();
            $material->mime_type     = $uploadedFile->getClientMimeType();

            // Bersihkan field yang tidak relevan untuk tipe file
            $material->external_url = null;
        }

        // ─── Tipe link: bersihkan field berkas ───
        if ($material->type === 'link') {
            $material->file_path     = null;
            $material->original_name = null;
            $material->file_size     = null;
            $material->mime_type     = null;
        }

        $material->save();

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

        return view('materials.edit', compact('material'));
    }

    /**
     * Simpan perubahan materi (shallow route).
     *
     * Route: PUT /dosen/materials/{material} → dosen.materials.update
     * Route model binding: {material} → Material $material
     *
     * Jika dosen mengunggah berkas pengganti:
     * 1. Hapus berkas lama dari disk privat
     * 2. Simpan berkas baru dengan nama acak
     * 3. Perbarui metadata (original_name, file_size, mime_type)
     */
    public function update(UpdateMaterialRequest $request, Material $material)
    {
        Gate::authorize('update', $material);

        $data = $request->validated();
        $data['description'] = $data['description'] ?? '';

        // ─── Upload berkas pengganti (jika ada) ───
        if ($request->hasFile('file')) {
            // Hapus berkas lama dari disk privat (jika ada)
            if ($material->file_path && Storage::disk('local')->exists($material->file_path)) {
                Storage::disk('local')->delete($material->file_path);
            }

            $uploadedFile = $request->file('file');
            $directory = 'materials/courses/' . $material->course_id;
            $storedPath = $uploadedFile->store($directory, 'local');

            $data['file_path']     = $storedPath;
            $data['original_name'] = $uploadedFile->getClientOriginalName();
            $data['file_size']     = $uploadedFile->getSize();
            $data['mime_type']     = $uploadedFile->getClientMimeType();
            $data['external_url']  = null;
        }

        // ─── Perubahan ke tipe link: hapus berkas fisik ───
        if (($data['type'] ?? $material->type) === 'link') {
            if ($material->file_path && Storage::disk('local')->exists($material->file_path)) {
                Storage::disk('local')->delete($material->file_path);
            }
            $data['file_path']     = null;
            $data['original_name'] = null;
            $data['file_size']     = null;
            $data['mime_type']     = null;
        }

        // Jangan mass-assign field `file` (itu UploadedFile, bukan kolom DB)
        unset($data['file']);

        $material->update($data);

        return redirect()->route('dosen.courses.materials.index', $material->course_id)
            ->with('success', 'Materi berhasil diperbarui.');
    }

    /**
     * Hapus materi beserta berkas fisiknya (shallow route).
     *
     * Route: DELETE /dosen/materials/{material} → dosen.materials.destroy
     * Route model binding: {material} → Material $material
     */
    public function destroy(Request $request, Material $material)
    {
        Gate::authorize('delete', $material);

        // Hapus berkas fisik dari disk privat sebelum menghapus record
        if ($material->file_path && Storage::disk('local')->exists($material->file_path)) {
            Storage::disk('local')->delete($material->file_path);
        }

        $course = $material->course;
        $material->delete();

        return redirect()->route('dosen.courses.materials.index', $course)
            ->with('success', 'Materi berhasil dihapus.');
    }

    /**
     * Unduh berkas materi (shallow route) — khusus dosen pengampu.
     *
     * Route: GET /dosen/materials/{material}/download → dosen.materials.download
     * Route model binding: {material} → Material $material
     *
     * Berkas dikirim dari disk privat melalui controller (streamed response),
     * sehingga Policy tetap berlaku dan web server tidak bisa diakses langsung.
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
