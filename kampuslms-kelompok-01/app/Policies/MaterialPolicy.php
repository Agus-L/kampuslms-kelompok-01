<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\Material;
use App\Models\User;

class MaterialPolicy
{
    /**
     * Tentukan apakah pengguna dapat melihat daftar materi.
     *
     * Admin: Diizinkan melihat daftar seluruh materi.
     * Dosen & Mahasiswa: Diizinkan melihat daftar materi dalam lingkup mata kuliahnya.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'dosen', 'mahasiswa'], true);
    }

    /**
     * Tentukan apakah pengguna dapat melihat dan mengunduh berkas materi.
     *
     * Admin: Diizinkan melihat dan mengunduh berkas materi apa pun.
     * Dosen: Diizinkan HANYA jika materi berada di mata kuliah yang ia ampu.
     * Mahasiswa: Diizinkan HANYA jika terdaftar di mata kuliah tempat materi berada.
     */
    public function view(User $user, Material $material): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $material->course && $material->course->lecturer_id === $user->id;
        }

        if ($user->role === 'mahasiswa') {
            return $material->course && $material->course->students()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna dapat mengunduh berkas materi dari disk privat.
     *
     * Logika sama dengan `view`: hanya pengguna yang berhak melihat materi
     * yang juga berhak mengunduh berkasnya. Dipisah sebagai metode tersendiri
     * agar Gate::authorize('download', $material) dapat dipanggil secara eksplisit
     * di controller download, memberikan kejelasan semantik pada kode.
     *
     * Admin: Diizinkan mengunduh berkas materi apa pun.
     * Dosen: Diizinkan HANYA jika materi berada di mata kuliah yang ia ampu.
     * Mahasiswa: Diizinkan HANYA jika terdaftar di mata kuliah tempat materi berada.
     */
    public function download(User $user, Material $material): bool
    {
        return $this->view($user, $material);
    }

    /**
     * Tentukan apakah pengguna dapat membuat materi baru.
     *
     * Admin: Diizinkan membuat materi di mata kuliah mana pun.
     * Dosen: Diizinkan membuat materi HANYA pada mata kuliah yang ia ampu sendiri.
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
     * Tentukan apakah pengguna dapat memperbarui materi.
     *
     * Admin: Diizinkan memperbarui materi apa pun.
     * Dosen: Diizinkan memperbarui HANYA jika materi berada di mata kuliah yang ia ampu.
     * Mahasiswa: Ditolak.
     */
    public function update(User $user, Material $material): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $material->course && $material->course->lecturer_id === $user->id;
        }

        return false;
    }

    /**
     * Tentukan apakah pengguna dapat menghapus materi.
     *
     * Admin: Diizinkan menghapus materi apa pun.
     * Dosen: Diizinkan menghapus HANYA jika materi berada di mata kuliah yang ia ampu.
     * Mahasiswa: Ditolak.
     */
    public function delete(User $user, Material $material): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'dosen') {
            return $material->course && $material->course->lecturer_id === $user->id;
        }

        return false;
    }
}
