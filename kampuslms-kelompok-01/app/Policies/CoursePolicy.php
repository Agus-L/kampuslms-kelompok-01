<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat katalog umum mata kuliah.
     *
     * Admin: Diizinkan melihat katalog global seluruh mata kuliah di kampus.
     * Dosen & Mahasiswa: Ditolak (diarahkan ke daftar mata kuliah perannya).
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Tentukan apakah pengguna dapat melihat detail mata kuliah tertentu.
     *
     * Admin: Diizinkan melihat detail mata kuliah mana pun.
     * Dosen: Diizinkan HANYA jika ia adalah pengampu mata kuliah ini.
     * Mahasiswa: Diizinkan HANYA jika terdaftar aktif di mata kuliah ini.
     */
    public function view(User $user, Course $course): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $course->lecturer_id === $user->id;
        }

        if ($user->role === 'mahasiswa') {
            return $course->students()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna dapat membuat mata kuliah baru.
     *
     * Admin: Diizinkan.
     * Dosen & Mahasiswa: Ditolak.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Tentukan apakah pengguna dapat memperbarui informasi mata kuliah.
     *
     * Admin: Diizinkan.
     * Dosen & Mahasiswa: Ditolak.
     */
    public function update(User $user, Course $course): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Tentukan apakah pengguna dapat menghapus mata kuliah.
     *
     * Admin: Diizinkan.
     * Dosen & Mahasiswa: Ditolak.
     */
    public function delete(User $user, Course $course): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Tentukan apakah pengguna dapat mengelola enrollment mahasiswa.
     *
     * Admin: Diizinkan pada seluruh mata kuliah.
     * Dosen: Diizinkan HANYA pada mata kuliah yang diampunya sendiri.
     * Mahasiswa: Ditolak.
     */
    public function manageEnrollment(User $user, Course $course): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $course->lecturer_id === $user->id;
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna dapat mengakses transkrip nilai mahasiswa pada mata kuliah ini.
     * Mendelegasikan pemeriksaan ke TranscriptPolicy (Tahap 2).
     */
    public function viewTranscript(User $user, Course $course, ?User $student = null): bool
    {
        if (! $student) {
            return false;
        }

        return app(TranscriptPolicy::class)->viewStudentCourseTranscript($user, $course, $student);
    }
}
