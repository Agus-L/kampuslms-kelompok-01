<?php

use Illuminate\Support\Facades\Route;

// ──────────────────────────────────────────────────────
// Import Controller — Umum
// ──────────────────────────────────────────────────────
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;

// ──────────────────────────────────────────────────────
// Import Controller — Admin
// ──────────────────────────────────────────────────────
use App\Http\Controllers\UserController              as AdminUserController;
use App\Http\Controllers\CourseController             as AdminCourseController;
use App\Http\Controllers\Admin\EnrollmentController  as AdminEnrollmentController;

// ──────────────────────────────────────────────────────
// Import Controller — Dosen
// ──────────────────────────────────────────────────────
use App\Http\Controllers\Dosen\CourseController      as DosenCourseController;
use App\Http\Controllers\Dosen\MaterialController    as DosenMaterialController;
use App\Http\Controllers\Dosen\AssignmentController  as DosenAssignmentController;
use App\Http\Controllers\Dosen\SubmissionController  as DosenSubmissionController;
use App\Http\Controllers\Dosen\GradeController       as DosenGradeController;

// ──────────────────────────────────────────────────────
// Import Controller — Mahasiswa
// ──────────────────────────────────────────────────────
use App\Http\Controllers\Mahasiswa\CourseController     as MahasiswaCourseController;
use App\Http\Controllers\Mahasiswa\MaterialController   as MahasiswaMaterialController;
use App\Http\Controllers\Mahasiswa\AssignmentController as MahasiswaAssignmentController;
use App\Http\Controllers\Mahasiswa\SubmissionController as MahasiswaSubmissionController;
use App\Http\Controllers\Mahasiswa\GradeController      as MahasiswaGradeController;


/*
|--------------------------------------------------------------------------
| Web Routes — KampusLMS
|--------------------------------------------------------------------------
|
| Disusun berdasarkan peran (role-based route groups) sesuai materi Minggu 5.
|
| Middleware yang dipakai:
|   - auth       : bawaan Laravel, memastikan user sudah login
|   - role:xxx   : custom, mengecek kolom `role` di tabel users
|
| Konvensi:
|   - Setiap grup punya prefix URL dan name prefix sendiri
|   - shallow() untuk route bersarang agar URL tidak terlalu dalam
|   - scopeBindings() memastikan child benar-benar milik parent
|
*/

// ──────────────────────────────────────────────────────
// GRUP 0 — Guest (Tanpa Middleware)
// Halaman publik yang bisa diakses siapa saja.
// ──────────────────────────────────────────────────────

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/tentang', function () {
    return view('tentang');
})->name('tentang');

// ──────────────────────────────────────────────────────
// Route Login Sementara (Development Helper)
// ──────────────────────────────────────────────────────
// Middleware 'auth' akan redirect ke route('login') jika user belum login.
// Secara default, otomatis login sebagai Admin KampusLMS agar Anda bisa
// langsung mengakses & menguji fitur tanpa terhalang autentikasi.
// Tersedia parameter ?role=dosen atau ?role=mahasiswa untuk menguji proteksi 403.
Route::get('/login', function (\Illuminate\Http\Request $request) {
    $role = $request->query('role', 'admin');
    $user = \App\Models\User::where('role', $role)->first();

    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user);
    }

    if ($role === 'dosen') {
        return redirect()->route('dosen.courses.index');
    }

    if ($role === 'mahasiswa') {
        return redirect()->route('mahasiswa.courses.index');
    }

    return redirect()->route('admin.courses.index');
})->name('login');

Route::get('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home');
})->name('logout');


// ══════════════════════════════════════════════════════
// Semua route di bawah ini memerlukan login (auth)
// ══════════════════════════════════════════════════════

Route::middleware('auth')->group(function () {

    // ──────────────────────────────────────────────────
    // GRUP 1 — Authenticated (Semua Peran)
    // Fitur bersama: dashboard & notifikasi.
    // ──────────────────────────────────────────────────

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::patch('/notifications/{notification}', [NotificationController::class, 'update'])
        ->name('notifications.update');


    // ──────────────────────────────────────────────────
    // GRUP 2 — Admin
    // Middleware : role:admin
    // Prefix URL : /admin
    // Name prefix: admin.
    // ──────────────────────────────────────────────────

    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            // Manajemen User — tanpa show (konsisten week 4)
            Route::resource('users', AdminUserController::class)
                ->except(['show']);

            // Manajemen Courses — full CRUD
            Route::resource('courses', AdminCourseController::class);

            // Enrollment — mendaftarkan mahasiswa ke mata kuliah
            // shallow(): destroy cukup /admin/enrollments/{enrollment}
            Route::resource('courses.enrollments', AdminEnrollmentController::class)
                ->shallow()
                ->only(['index', 'create', 'store', 'destroy']);
        });


    // ──────────────────────────────────────────────────
    // GRUP 3 — Dosen
    // Middleware : role:dosen
    // Prefix URL : /dosen
    // Name prefix: dosen.
    //
    // scopeBindings() memastikan {material} benar-benar
    // milik {course} tersebut (mencegah IDOR bersarang).
    // ──────────────────────────────────────────────────

    Route::middleware('role:dosen')
        ->prefix('dosen')
        ->name('dosen.')
        ->scopeBindings()
        ->group(function () {

            // Mata kuliah yang diampu — hanya lihat
            Route::resource('courses', DosenCourseController::class)
                ->only(['index', 'show']);

            // Materi kuliah — CRUD, bersarang di bawah course
            // shallow(): show/edit/update/destroy cukup /dosen/materials/{material}
            Route::resource('courses.materials', DosenMaterialController::class)
                ->shallow();

            // Tugas — CRUD, bersarang di bawah course
            // shallow(): show/edit/update/destroy cukup /dosen/assignments/{assignment}
            Route::resource('courses.assignments', DosenAssignmentController::class)
                ->shallow();

            // Submission — dosen hanya melihat, tidak membuat
            // shallow(): show cukup /dosen/submissions/{submission}
            Route::resource('assignments.submissions', DosenSubmissionController::class)
                ->shallow()
                ->only(['index', 'show']);

            // Nilai — dosen membuat dan memperbarui nilai
            // shallow(): update cukup /dosen/grades/{grade}
            Route::resource('submissions.grades', DosenGradeController::class)
                ->shallow()
                ->only(['store', 'update']);
        });


    // ──────────────────────────────────────────────────
    // GRUP 4 — Mahasiswa
    // Middleware : role:mahasiswa
    // Prefix URL : /mahasiswa
    // Name prefix: mahasiswa.
    //
    // scopeBindings() memastikan data bersarang saling
    // terkait (assignment milik course yang benar, dsb).
    // ──────────────────────────────────────────────────

    Route::middleware('role:mahasiswa')
        ->prefix('mahasiswa')
        ->name('mahasiswa.')
        ->scopeBindings()
        ->group(function () {

            // Mata kuliah yang diikuti — hanya lihat
            Route::resource('courses', MahasiswaCourseController::class)
                ->only(['index', 'show']);

            // Materi — read-only
            Route::resource('courses.materials', MahasiswaMaterialController::class)
                ->shallow()
                ->only(['index', 'show']);

            // Tugas — read-only
            Route::resource('courses.assignments', MahasiswaAssignmentController::class)
                ->shallow()
                ->only(['index', 'show']);

            // Submission — CRUD tanpa hapus (tugas yang sudah dikumpulkan tidak boleh dihapus)
            Route::resource('assignments.submissions', MahasiswaSubmissionController::class)
                ->shallow()
                ->except(['destroy']);

            // Nilai — mahasiswa hanya melihat nilai sendiri
            Route::resource('submissions.grades', MahasiswaGradeController::class)
                ->shallow()
                ->only(['index']);
        });

});
