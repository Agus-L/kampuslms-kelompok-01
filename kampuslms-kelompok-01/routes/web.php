<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;

// ──────────────────────────────────────────────────────
// Halaman Statis
// ──────────────────────────────────────────────────────

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tentang', function () {
    return view('tentang');
})->name('tentang');

// ──────────────────────────────────────────────────────
// Modul Mata Kuliah (Courses)
// ──────────────────────────────────────────────────────
//
// Route::resource() mendaftarkan 7 route RESTful sekaligus:
//   GET    /courses              → index   (daftar semua MK)
//   GET    /courses/create       → create  (form tambah MK)
//   POST   /courses              → store   (simpan MK baru)
//   GET    /courses/{course}     → show    (detail satu MK)
//   GET    /courses/{course}/edit→ edit    (form edit MK)
//   PUT    /courses/{course}     → update  (simpan perubahan)
//   DELETE /courses/{course}     → destroy (hapus MK)

Route::resource('courses', CourseController::class);

// Route::get('/courses/create', [CourseController::class, 'create'])
//     ->name('courses.create');

// ──────────────────────────────────────────────────────
// Modul Pengguna (Users)
// ──────────────────────────────────────────────────────
//
// Route::resource() mendaftarkan route RESTful:
//   GET    /users              → index   (daftar semua pengguna)
//   GET    /users/create       → create  (form tambah pengguna)
//   POST   /users              → store   (simpan pengguna baru)
//   GET    /users/{user}/edit  → edit    (form edit pengguna)
//   PUT    /users/{user}       → update  (simpan perubahan)
//   DELETE /users/{user}       → destroy (hapus pengguna)

Route::resource('users', UserController::class)->except(['show']);

