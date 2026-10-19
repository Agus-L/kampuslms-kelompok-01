<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
        $courses = Course::query()
            ->with('lecturer')
            ->when($request->filled('q'), fn ($query) =>
                $query->where('name', 'like', '%' . $request->q . '%')
                      ->orWhere('code', 'like', '%' . $request->q . '%'))
            ->when($request->filled('status'), fn ($query) =>
                $query->where('status', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // -------------------------------------------------------------------------
        // KODE RUSAK (CP NOMOR 4 — FILTER DISIMPAN KE SESSION / STICKY FILTER):
        // -------------------------------------------------------------------------
        // 1. Simpan pencarian ke session jika ada input di URL
        // if ($request->has('q')) {
        //     session(['course_search' => $request->q]);
        // }
        // if ($request->has('status')) {
        //     session(['course_status' => $request->status]);
        // }
        // // 2. Baca filter DARI SESSION (bukan dari URL), menyebabkan filter menempel terus
        // $search = session('course_search');
        // $status = session('course_status');
        // $courses = Course::query()
        //     ->with('lecturer')
        //     ->when($search, fn ($query) =>
        //         $query->where('name', 'like', '%' . $search . '%')
        //               ->orWhere('code', 'like', '%' . $search . '%'))
        //     ->when($status, fn ($query) =>
        //         $query->where('status', $status))
        //     ->latest()
        //     ->paginate(15);

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
        Gate::authorize('create', Course::class);

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
        Gate::authorize('create', Course::class);

        $validated = $request->validated();

        Course::create($validated);

        return redirect()->route('admin.courses.index')
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
        Gate::authorize('view', $course);

        $role = auth()->user()->role ?? 'guest';

        // Eager-load relasi yang dibutuhkan di halaman detail
        $course->load([
            'lecturer',
            'students',
            'materials' => fn ($q) => $q->latest(),
            'assignments' => $role === 'mahasiswa'
                ? fn ($q) => $q->where('status', 'published')->latest()
                : fn ($q) => $q->latest(),
        ]);

        return view('courses.show', compact('course'));
    }

    /**
     * edit — Menampilkan form untuk mengedit mata kuliah.
     *
     * Route: GET /courses/{course}/edit → courses.edit
     */
    public function edit(Course $course)
    {
        Gate::authorize('update', $course);

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
        Gate::authorize('update', $course);

        $validated = $request->validated();

        $course->update($validated);

        return redirect()->route('admin.courses.show', $course)
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
        Gate::authorize('delete', $course);

        try {
            $course->delete();

            return redirect()->route('admin.courses.index')
                             ->with('success', 'Mata kuliah "' . $course->name . '" berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.courses.index')
                             ->with('error', 'Gagal menghapus mata kuliah. Pastikan tidak ada data terkait.');
        }
    }
}
