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
// KENAPA tidak pakai Route::resource('courses', ...)?
// → Kita baru butuh 2 route (index & show). Menulis secara
//   eksplisit lebih mudah dipahami bagi pemula daripada
//   menyembunyikan 7 route di balik satu baris resource().
//   Nanti saat semua method CRUD siap, bisa direfaktor
//   menjadi Route::resource().
//
// KENAPA pakai ->name('courses.xxx')?
// → Supaya di Blade kita bisa menulis route('courses.index')
//   alih-alih hardcode '/courses'. Jika URI berubah,
//   semua tautan otomatis ikut berubah.

// GET /courses → Daftar semua mata kuliah
Route::get('/courses', [CourseController::class, 'index'])
    ->name('courses.index');

// GET /courses/{course} → Detail satu mata kuliah
// {course} adalah parameter dinamis (ID mata kuliah)
Route::get('/courses/{course}', [CourseController::class, 'show'])
    ->name('courses.show');


