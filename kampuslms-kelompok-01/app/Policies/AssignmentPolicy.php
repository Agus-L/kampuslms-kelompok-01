<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;

class AssignmentPolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat daftar penugasan.
     *
     * Admin: Diizinkan melihat semua tugas.
     * Dosen & Mahasiswa: Diizinkan melihat tugas dalam lingkup mata kuliahnya.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'dosen', 'mahasiswa'], true);
    }

    /**
     * Tentukan apakah pengguna dapat melihat instruksi/detail tugas.
     *
     * Admin: Diizinkan melihat detail tugas apa pun.
     * Dosen: Diizinkan HANYA jika tugas berada di mata kuliah yang ia ampu.
     * Mahasiswa: Diizinkan HANYA jika terdaftar di mata kuliah terkait tugas ini.
     */
    public function view(User $user, Assignment $assignment): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $assignment->course && $assignment->course->lecturer_id === $user->id;
        }

        if ($user->role === 'mahasiswa') {
            return $assignment->status === 'published'
                && $assignment->course
                && $assignment->course->students()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna dapat membuat tugas baru.
     *
     * Admin: Diizinkan membuat tugas baru di mata kuliah mana pun.
     * Dosen: Diizinkan membuat tugas baru HANYA pada mata kuliah yang diampunya sendiri.
     * Mahasiswa: Ditolak.
     */
    public function create(User $user, ?Course $course = null): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $course ? $course->lecturer_id === $user->id : true;
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna dapat memperbarui tugas.
     *
     * Admin: Diizinkan memperbarui tugas apa pun.
     * Dosen: Diizinkan memperbarui HANYA jika tugas berada di mata kuliah yang ia ampu.
     * Mahasiswa: Ditolak.
     */
    public function update(User $user, Assignment $assignment): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $assignment->course && $assignment->course->lecturer_id === $user->id;
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna dapat menghapus tugas.
     *
     * Admin: Diizinkan menghapus tugas apa pun.
     * Dosen: Diizinkan menghapus HANYA jika tugas berada di mata kuliah yang ia ampu.
     * Mahasiswa: Ditolak.
     */
    public function delete(User $user, Assignment $assignment): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $assignment->course && $assignment->course->lecturer_id === $user->id;
        }

        return false;
    }
}
