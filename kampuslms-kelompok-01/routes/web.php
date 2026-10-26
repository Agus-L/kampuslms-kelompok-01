<?php

use Illuminate\Support\Facades\Route;

// ──────────────────────────────────────────────────────
// Import Controller — Auth
// ──────────────────────────────────────────────────────
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;

// ──────────────────────────────────────────────────────
// Import Controller — Umum
// ──────────────────────────────────────────────────────
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Livewire\Courses\CourseList;

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
use App\Http\Controllers\Dosen\EnrollmentController  as DosenEnrollmentController;

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

Route::get('/courses', CourseList::class)->name('courses.index');

// ──────────────────────────────────────────────────────
// Autentikasi Web & Pemulihan Kata Sandi
// ──────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');

    // Registrasi Publik ditiadakan (Khusus Admin yang mendaftarkan melalui modul Pengguna)
    Route::get('/register', function () {
        return redirect()->route('login')->with('error', 'Pendaftaran akun baru hanya dapat dilakukan oleh Administrator Kampus.');
    })->name('register');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


// ══════════════════════════════════════════════════════
// Semua route di bawah ini memerlukan login (auth)
// ══════════════════════════════════════════════════════

Route::middleware(['auth:sanctum,web'])->group(function () {

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

    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.mark-all-read');


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

            // Download berkas materi — endpoint terpisah agar semantik dan mudah di-Policy
            Route::get('materials/{material}/download', [DosenMaterialController::class, 'download'])
                ->name('materials.download');

            // Tugas — CRUD, bersarang di bawah course
            // shallow(): show/edit/update/destroy cukup /dosen/assignments/{assignment}
            Route::resource('courses.assignments', DosenAssignmentController::class)
                ->shallow();

            // Submission — dosen hanya melihat, tidak membuat
            // shallow(): show cukup /dosen/submissions/{submission}
            Route::resource('assignments.submissions', DosenSubmissionController::class)
                ->shallow()
                ->only(['index', 'show']);

            // Download berkas submission — endpoint terpisah agar semantik dan mudah di-Policy
            Route::get('submissions/{submission}/download', [DosenSubmissionController::class, 'download'])
                ->name('submissions.download');

            // Nilai — dosen membuat dan memperbarui nilai
            // shallow(): update cukup /dosen/grades/{grade}
            Route::resource('submissions.grades', DosenGradeController::class)
                ->shallow()
                ->only(['store', 'update']);

            // Enrollment — dosen mendaftarkan dan mengelola mahasiswa di kelasnya
            Route::resource('courses.enrollments', DosenEnrollmentController::class)
                ->shallow()
                ->only(['index', 'create', 'store', 'destroy']);
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

            // Download berkas materi — endpoint terpisah dengan otorisasi Policy
            Route::get('materials/{material}/download', [MahasiswaMaterialController::class, 'download'])
                ->name('materials.download');

            // Tugas — read-only
            Route::resource('courses.assignments', MahasiswaAssignmentController::class)
                ->shallow()
                ->only(['index', 'show']);

            // Submission — CRUD tanpa hapus (tugas yang sudah dikumpulkan tidak boleh dihapus)
            Route::resource('assignments.submissions', MahasiswaSubmissionController::class)
                ->shallow()
                ->except(['destroy']);

            // Download berkas submission — endpoint terpisah dengan otorisasi Policy
            Route::get('submissions/{submission}/download', [MahasiswaSubmissionController::class, 'download'])
                ->name('submissions.download');

            // Nilai — mahasiswa hanya melihat nilai sendiri
            Route::resource('submissions.grades', MahasiswaGradeController::class)
                ->shallow()
                ->only(['index']);
        });
});
