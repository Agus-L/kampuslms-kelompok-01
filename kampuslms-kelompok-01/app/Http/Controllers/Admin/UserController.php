<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Admin\UserController — CRUD pengguna oleh Admin.
 *
 * Route model binding diterapkan: parameter {user} di URL
 * otomatis di-resolve menjadi instance User oleh Laravel.
 * Jika user tidak ditemukan, Laravel melempar 404 otomatis.
 *
 * Catatan: route resource untuk users menggunakan ->except(['show']),
 * sehingga method show() tidak akan dipanggil melalui route.
 * Tetap disediakan untuk kelengkapan resource controller.
 */
class UserController extends Controller
{
    /**
     * Daftar seluruh pengguna.
     *
     * Route: GET /admin/users → admin.users.index
     */
    public function index()
    {
        //
    }

    /**
     * Form tambah pengguna baru.
     *
     * Route: GET /admin/users/create → admin.users.create
     */
    public function create()
    {
        //
    }

    /**
     * Simpan pengguna baru ke database.
     *
     * Route: POST /admin/users → admin.users.store
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Detail satu pengguna.
     *
     * Route: Tidak terdaftar (excluded dari resource).
     * Route model binding: {user} → User $user
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Form edit pengguna.
     *
     * Route: GET /admin/users/{user}/edit → admin.users.edit
     * Route model binding: {user} → User $user
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Simpan perubahan pengguna.
     *
     * Route: PUT /admin/users/{user} → admin.users.update
     * Route model binding: {user} → User $user
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Hapus pengguna.
     *
     * Route: DELETE /admin/users/{user} → admin.users.destroy
     * Route model binding: {user} → User $user
     */
    public function destroy(User $user)
    {
        //
    }
}
