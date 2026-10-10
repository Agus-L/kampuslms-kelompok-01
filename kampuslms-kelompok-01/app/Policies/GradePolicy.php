<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;

class GradePolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat daftar nilai.
     *
     * Admin: Diizinkan melihat rekapitulasi seluruh nilai untuk pelaporan akademik.
     * Dosen: Diizinkan melihat daftar nilai pada mata kuliah yang diampunya.
     * Mahasiswa: Diizinkan melihat rekap nilai miliknya sendiri.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'dosen', 'mahasiswa'], true);
    }

    /**
     * Tentukan apakah pengguna dapat melihat detail nilai tertentu.
     *
     * Admin: Diizinkan melihat nilai siapa pun.
     * Dosen: Diizinkan HANYA jika submission berada di mata kuliah yang ia ampu.
     * Mahasiswa: Diizinkan HANYA jika nilai tersebut milik submission dirinya sendiri (Pencegahan IDOR mutlak).
     */
    public function view(User $user, Grade $grade): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $grade->submission
                && $grade->submission->assignment
                && $grade->submission->assignment->course
                && $grade->submission->assignment->course->lecturer_id === $user->id;
        }

        if ($user->role === 'mahasiswa') {
            return $grade->submission && $grade->submission->user_id === $user->id;
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna dapat memberi nilai baru pada submission.
     *
     * Dosen: Diizinkan HANYA jika submission berada di mata kuliah miliknya sendiri.
     * Admin & Mahasiswa: Ditolak.
     */
    public function create(User $user, ?Submission $submission = null): bool
    {
        if ($user->role !== 'dosen') {
            return false;
        }

        if ($submission) {
            return $submission->assignment
                && $submission->assignment->course
                && $submission->assignment->course->lecturer_id === $user->id;
        }

        return true;
    }

    /**
     * Tentukan apakah pengguna dapat memperbarui skor atau umpan balik nilai.
     *
     * Dosen: Diizinkan HANYA jika nilai berada di mata kuliah miliknya sendiri.
     * Admin & Mahasiswa: Ditolak.
     */
    public function update(User $user, Grade $grade): bool
    {
        if ($user->role !== 'dosen') {
            return false;
        }

        return $grade->submission
            && $grade->submission->assignment
            && $grade->submission->assignment->course
            && $grade->submission->assignment->course->lecturer_id === $user->id;
    }

    /**
     * Tentukan apakah pengguna dapat mereset/menghapus rekaman nilai.
     *
     * Dosen: Diizinkan HANYA jika nilai berada di mata kuliah miliknya sendiri.
     * Admin & Mahasiswa: Ditolak.
     */
    public function delete(User $user, Grade $grade): bool
    {
        if ($user->role !== 'dosen') {
            return false;
        }

        return $grade->submission
            && $grade->submission->assignment
            && $grade->submission->assignment->course
            && $grade->submission->assignment->course->lecturer_id === $user->id;
    }
}
