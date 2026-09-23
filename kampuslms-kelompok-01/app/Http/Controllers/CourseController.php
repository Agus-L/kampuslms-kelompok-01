<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * CourseController — CRUD lengkap untuk modul Mata Kuliah.
 *
 * Semua data diambil dari database melalui Eloquent Model Course.
 * Method mengikuti konvensi resource controller Laravel:
 * index, create, store, show, edit, update, destroy.
 */
class CourseController extends Controller
{
    /**
     * index — Menampilkan daftar seluruh mata kuliah.
     *
     * Route: GET /courses → courses.index
     * Eager-load relasi 'lecturer' agar nama dosen bisa ditampilkan
     * tanpa query tambahan per mata kuliah (menghindari N+1).
     */
    public function index(Request $request)
    {
        $query = Course::with('lecturer');

        // Pencarian berdasarkan kode atau nama
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', '%' . $search . '%')
                  ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Pagination 15 per halaman, query string dipertahankan saat berpindah halaman
        $courses = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('courses.index', compact('courses'));
    }

    /**
     * create — Menampilkan form untuk menambah mata kuliah baru.
     *
     * Route: GET /courses/create → courses.create
     * Mengambil daftar dosen untuk dropdown di form.
     */
    public function create()
    {
        $lecturers = User::where('role', 'dosen')->orderBy('name')->get();

        return view('courses.create', compact('lecturers'));
    }

    /**
     * store — Menyimpan mata kuliah baru ke database.
     *
     * Route: POST /courses → courses.store
     * Validasi input terlebih dahulu, lalu buat record baru.
     */
    public function store(StoreCourseRequest $request)
    {
        $validated = $request->validated();

        Course::create($validated);

        return redirect()->route('courses.index')
                         ->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    /**
     * show — Menampilkan detail satu mata kuliah.
     *
     * Route: GET /courses/{course} → courses.show
     * Menggunakan Route Model Binding: Laravel otomatis mencari
     * Course berdasarkan ID dan melempar 404 jika tidak ditemukan.
     */
    public function show(Course $course)
    {
        // Eager-load relasi yang dibutuhkan di halaman detail
        $course->load('lecturer', 'students');

        return view('courses.show', compact('course'));
    }

    /**
     * edit — Menampilkan form untuk mengedit mata kuliah.
     *
     * Route: GET /courses/{course}/edit → courses.edit
     */
    public function edit(Course $course)
    {
        $lecturers = User::where('role', 'dosen')->orderBy('name')->get();

        return view('courses.edit', compact('course', 'lecturers'));
    }

    /**
     * update — Menyimpan perubahan mata kuliah ke database.
     *
     * Route: PUT /courses/{course} → courses.update
     * Rule unique untuk 'code' dikecualikan untuk ID course yang
     * sedang diedit, agar tidak konflik dengan dirinya sendiri.
     */
    public function update(UpdateCourseRequest $request, Course $course)
    {
        $validated = $request->validated();

        $course->update($validated);

        return redirect()->route('courses.show', $course)
                         ->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    /**
     * destroy — Menghapus mata kuliah dari database.
     *
     * Route: DELETE /courses/{course} → courses.destroy
     * Menggunakan try-catch karena tabel lain (materials, assignments)
     * memiliki FK cascadeOnDelete, namun course_user tidak.
     * Jika ada constraint yang menghalangi, tampilkan pesan error.
     */
    public function destroy(Course $course)
    {
        try {
            $course->delete();

            return redirect()->route('courses.index')
                             ->with('success', 'Mata kuliah "' . $course->name . '" berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('courses.index')
                             ->with('error', 'Gagal menghapus mata kuliah. Pastikan tidak ada data terkait.');
        }
    }
}
