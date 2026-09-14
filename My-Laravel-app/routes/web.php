<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;

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

