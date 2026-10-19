<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat daftar submission.
     *
     * Admin: Diizinkan melihat seluruh rekap submission di sistem (audit).
     * Dosen: Diizinkan melihat daftar submission pada penugasan mata kuliahnya.
     * Mahasiswa: Diizinkan melihat daftar submission miliknya sendiri.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'dosen', 'mahasiswa'], true);
    }

    /**
     * Tentukan apakah pengguna dapat melihat detail dan mengunduh berkas submission.
     *
     * Admin: Diizinkan untuk keperluan audit sistem.
     * Dosen: Diizinkan HANYA jika submission berasal dari mata kuliah yang diampunya.
     * Mahasiswa: Diizinkan HANYA jika submission adalah miliknya sendiri (Pencegahan IDOR mutlak).
     */
    public function view(User $user, Submission $submission): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $submission->assignment
                && $submission->assignment->course
                && $submission->assignment->course->lecturer_id === $user->id;
        }

        if ($user->role === 'mahasiswa') {
            return $submission->user_id === $user->id;
        }

        return false;
    }

    /**
     * Tentukan apakah mahasiswa dapat mengumpulkan tugas untuk pertama kali.
     *
     * Admin & Dosen: Ditolak.
     * Mahasiswa: Diizinkan HANYA jika terdaftar di mata kuliah tugas ini DAN belum pernah mengumpulkan.
     */
    public function create(User $user, ?Assignment $assignment = null): bool
    {
        if ($user->role !== 'mahasiswa') {
            return false;
        }

        if ($assignment) {
            if ($assignment->status !== 'published') {
                return false;
            }

            // Tolak jika sudah lewat deadline DAN allow_late = false
            if (!$assignment->allow_late && now()->gt($assignment->due_at)) {
                return false;
            }

            $isEnrolled = $assignment->course
                && $assignment->course->students()->where('user_id', $user->id)->exists();

            $alreadySubmitted = $assignment->submissions()
                ->where('user_id', $user->id)
                ->exists();

            return $isEnrolled && !$alreadySubmitted;
        }

        return true;
    }

    /**
     * Tentukan apakah mahasiswa dapat memperbarui/mengunggah ulang berkas submission (revisi).
     *
     * Dosen & Admin: Ditolak (tidak boleh mengubah berkas tugas mahasiswa).
     * Mahasiswa: Diizinkan HANYA jika milik sendiri DAN statusnya Draft / Diizinkan Revisi.
     */
    public function update(User $user, Submission $submission): bool
    {
        if ($user->role !== 'mahasiswa' || $submission->user_id !== $user->id) {
            return false;
        }

        // Jika atribut status tersedia pada model/tabel
        if (isset($submission->status)) {
            return in_array(strtolower($submission->status), ['draft', 'revision', 'diizinkan revisi', 'revision_allowed'], true);
        }

        // Jika belum ada kolom status eksplisit, hanya izinkan jika belum dinilai permanen
        return !$submission->grade()->exists();
    }

    /**
     * Tentukan apakah mahasiswa dapat mengajukan permohonan revisi tugas.
     *
     * Mahasiswa: Diizinkan HANYA jika milik sendiri, status Submitted, dan belum dinilai permanen.
     */
    public function requestRevision(User $user, Submission $submission): bool
    {
        if ($user->role !== 'mahasiswa' || $submission->user_id !== $user->id) {
            return false;
        }

        if (isset($submission->status) && strtolower($submission->status) !== 'submitted') {
            return false;
        }

        return !$submission->grade()->exists();
    }

    /**
     * Tentukan apakah dosen dapat menyetujui pengajuan revisi tugas (revert to Draft).
     *
     * Dosen: Diizinkan HANYA jika tugas berada di mata kuliah yang ia ampu.
     */
    public function allowRevision(User $user, Submission $submission): bool
    {
        if ($user->role !== 'dosen') {
            return false;
        }

        return $submission->assignment
            && $submission->assignment->course
            && $submission->assignment->course->lecturer_id === $user->id;
    }

    /**
     * Tentukan apakah pengguna dapat mengunduh berkas submission.
     *
     * Admin: Diizinkan untuk keperluan audit sistem.
     * Dosen: Diizinkan HANYA jika submission berasal dari mata kuliah yang diampunya.
     * Mahasiswa: Diizinkan HANYA jika submission adalah miliknya sendiri.
     */
    public function download(User $user, Submission $submission): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $submission->assignment
                && $submission->assignment->course
                && $submission->assignment->course->lecturer_id === $user->id;
        }

        if ($user->role === 'mahasiswa') {
            return $submission->user_id === $user->id;
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna dapat menghapus berkas submission.
     *
     * DITOLAK UNTUK SEMUA PERAN (return false) untuk menjaga academic audit trail.
     */
    public function delete(User $user, Submission $submission): bool
    {
        return false;
    }
}
