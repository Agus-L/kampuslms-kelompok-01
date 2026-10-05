<?php

use Illuminate\Support\Facades\Route;

// ──────────────────────────────────────────────────────
// Import Controller — API V1 (Courses & Assignments)
// ──────────────────────────────────────────────────────
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\CourseAssignmentController;
use App\Http\Controllers\Api\V1\AssignmentController;

/*
|--------------------------------------------------------------------------
| API Routes — KampusLMS (Modul: Auth, Courses & Assignments)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1
| Autentikasi: Laravel Sanctum (Bearer token)
| Format response: API Resource sesuai kontrak spek-week6.md
|
*/

Route::prefix('v1')->group(function () {

    // ── Endpoint Publik ────────────────────────────────
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');

    // ── Endpoint Terotentikasi (Sanctum) ──────────────
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

        // Auth & Profil
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // Courses
        Route::get('/courses', [CourseController::class, 'index']);
        Route::get('/courses/{course}', [CourseController::class, 'show']);

        // Course → Assignments (nested)
        Route::get('/courses/{course}/assignments', [CourseAssignmentController::class, 'index']);

        // Assignments CRUD (standalone)
        Route::post('/assignments', [AssignmentController::class, 'store']);
        Route::match(['put', 'patch'], '/assignments/{assignment}', [AssignmentController::class, 'update']);
        Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy']);
    });
});
