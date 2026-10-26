<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Dosen\EnrollmentController — Dosen mengelola pendaftaran mahasiswa pada mata kuliah yang diampunya.
 */
class EnrollmentController extends Controller
{
    /**
     * Daftar mahasiswa yang terdaftar di mata kuliah ini.
     */
    public function index(Course $course)
    {
        Gate::authorize('manageEnrollment', $course);

        $students = $course->students()
            ->latest('course_user.created_at')
            ->paginate(15);

        $availableStudents = User::where('role', 'mahasiswa')
            ->whereNotIn('id', $course->students()->pluck('users.id'))
            ->orderBy('name')
            ->get();

        return view('enrollments.index', compact('course', 'students', 'availableStudents'));
    }

    /**
     * Form untuk mendaftarkan mahasiswa baru ke mata kuliah.
     */
    public function create(Course $course)
    {
        Gate::authorize('manageEnrollment', $course);

        $availableStudents = User::where('role', 'mahasiswa')
            ->whereNotIn('id', $course->students()->pluck('users.id'))
            ->orderBy('name')
            ->get();

        return view('enrollments.create', compact('course', 'availableStudents'));
    }

    /**
     * Simpan pendaftaran mahasiswa ke mata kuliah oleh dosen.
     */
    public function store(Request $request, Course $course)
    {
        Gate::authorize('manageEnrollment', $course);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ], [
            'user_id.required' => 'Silakan pilih mahasiswa yang akan didaftarkan.',
            'user_id.exists'   => 'Mahasiswa tidak valid.',
        ]);

        $student = User::where('id', $validated['user_id'])->where('role', 'mahasiswa')->firstOrFail();

        if ($course->students()->where('user_id', $student->id)->exists()) {
            return back()->with('error', "Mahasiswa {$student->name} sudah terdaftar pada mata kuliah ini.");
        }

        $course->students()->attach($student->id, [
            'enrolled_at' => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return back()->with('success', "Mahasiswa {$student->name} berhasil didaftarkan ke kelas {$course->name}.");
    }

    /**
     * Hapus pendaftaran mahasiswa dari mata kuliah.
     */
    public function destroy(string $enrollment)
    {
        $pivot = DB::table('course_user')->where('id', $enrollment)->first();
        if (!$pivot) {
            $pivot = DB::table('course_user')->where('user_id', $enrollment)->first();
        }

        if (!$pivot) {
            abort(404, 'Data pendaftaran mahasiswa tidak ditemukan.');
        }

        $course = Course::findOrFail($pivot->course_id);
        Gate::authorize('manageEnrollment', $course);

        $student = User::find($pivot->user_id);
        $course->students()->detach($pivot->user_id);

        $studentName = $student->name ?? 'Mahasiswa';
        return back()->with('success', "{$studentName} berhasil dikeluarkan dari mata kuliah {$course->name}.");
    }
}
