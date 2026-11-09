<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

/**
 * TranscriptPolicy — Kebijakan Otorisasi Transkrip Nilai & Rekap Presensi Tugas (Tahap 2).
 *
 * Menerapkan kontrol akses berbasis peran (RBAC) dan perlindungan batasan (boundary defense):
 * 1. Admin: Memiliki hak audit global untuk melihat & mengekspor transkrip siapa pun.
 * 2. Dosen Pengampu: Dibatasi secara ketat hanya pada mata kuliah miliknya sendiri ($course->lecturer_id === $user->id)
 *    dan mahasiswa yang terdaftar aktif di kelas tersebut. Dosen dilarang keras mengintip atau mengunduh
 *    transkrip mata kuliah milik dosen lain (Pencegahan pelanggaran boundary pengampu).
 * 3. Mahasiswa: Dibatasi secara mandiri (self-service) hanya untuk data miliknya sendiri. Dilarang keras
 *    memanipulasi parameter URL untuk melihat atau mengunduh transkrip mahasiswa lain (Pencegahan IDOR).
 */
class TranscriptPolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat transkrip & rekap presensi tugas
     * seorang mahasiswa pada mata kuliah spesifik (Course-level Transcript).
     */
    public function viewStudentCourseTranscript(User $user, Course $course, User $student): bool
    {
        // 1. Admin memiliki otoritas penuh ke seluruh mata kuliah
        if ($user->role === 'admin') {
            return true;
        }

        // 2. Dosen Pengampu: HANYA jika ia mengampu mata kuliah ini
        //    DAN mahasiswa terdaftar aktif di mata kuliah ini
        if ($user->role === 'dosen') {
            $isLecturer = (int) $course->lecturer_id === (int) $user->id;

            $isEnrolled = $course->relationLoaded('students')
                ? $course->students->contains('id', $student->id)
                : $course->students()->where('users.id', $student->id)->exists();

            return $isLecturer && $isEnrolled;
        }

        // 3. Mahasiswa: HANYA jika melihat nilainya sendiri dan terdaftar aktif di mata kuliah ini (Anti-IDOR)
        if ($user->role === 'mahasiswa') {
            $isSelf = (int) $user->id === (int) $student->id;

            $isEnrolled = $course->relationLoaded('students')
                ? $course->students->contains('id', $student->id)
                : $course->students()->where('users.id', $student->id)->exists();

            return $isSelf && $isEnrolled;
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna dapat mengekspor berkas PDF transkrip & presensi tugas
     * per mahasiswa pada mata kuliah spesifik.
     */
    public function exportStudentCourseTranscript(User $user, Course $course, User $student): bool
    {
        return $this->viewStudentCourseTranscript($user, $course, $student);
    }

    /**
     * Tentukan apakah pengguna dapat melihat transkrip nilai kumulatif mahasiswa (seluruh mata kuliah).
     *
     * - Admin: Diizinkan untuk verifikasi berkas yudisium, SKL, dan audit akademik.
     * - Mahasiswa: Diizinkan HANYA untuk datanya sendiri (Anti-IDOR).
     * - Dosen: Ditolak (Dosen hanya berwenang pada mata kuliah yang diampunya sendiri).
     */
    public function viewCumulativeTranscript(User $user, User $student): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'mahasiswa') {
            return (int) $user->id === (int) $student->id;
        }

        // Dosen tidak memiliki akses transkrip kumulatif lintas mata kuliah
        return false;
    }

    /**
     * Tentukan apakah pengguna dapat mengekspor berkas PDF transkrip kumulatif mahasiswa.
     */
    public function exportCumulativeTranscript(User $user, User $student): bool
    {
        return $this->viewCumulativeTranscript($user, $student);
    }
}

