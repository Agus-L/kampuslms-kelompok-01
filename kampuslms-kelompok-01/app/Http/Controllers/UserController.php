<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * UserController — CRUD lengkap untuk modul Pengguna.
 *
 * KEAMANAN: Kolom 'role' TIDAK termasuk dalam $fillable model User,
 * sehingga nilainya WAJIB diisi secara eksplisit (manual assignment)
 * di method store() dan update(). Ini mencegah mass-assignment attack
 * pada kolom role.
 */
class UserController extends Controller
{
    /**
     * index — Menampilkan daftar seluruh pengguna.
     *
     * Route: GET /users → users.index
     */
    public function index()
    {
        $users = User::orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    /**
     * create — Menampilkan form untuk menambah pengguna baru.
     *
     * Route: GET /users/create → users.create
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * store — Menyimpan pengguna baru ke database.
     *
     * Route: POST /users → users.store
     *
     * PENTING: Role diisi secara EKSPLISIT ($user->role = ...),
     * bukan melalui mass assignment, karena kolom role tidak ada
     * di dalam $fillable model User.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'nim_nip'  => 'nullable|string|max:50|unique:users,nim_nip',
            'role'     => ['required', Rule::in(['admin', 'dosen', 'mahasiswa'])],
        ]);

        // Mass assignment hanya untuk kolom yang ada di $fillable
        $user = new User();
        $user->name     = $validated['name'];
        $user->email    = $validated['email'];
        $user->password = $validated['password'];
        $user->nim_nip  = $validated['nim_nip'] ?? null;

        // Role diisi secara EKSPLISIT — bukan mass assignment
        $user->role = $validated['role'];

        $user->save();

        return redirect()->route('users.index')
                         ->with('success', 'Pengguna "' . $user->name . '" berhasil ditambahkan.');
    }

    /**
     * edit — Menampilkan form untuk mengedit pengguna.
     *
     * Route: GET /users/{user}/edit → users.edit
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * update — Menyimpan perubahan pengguna ke database.
     *
     * Route: PUT /users/{user} → users.update
     *
     * PENTING: Role diisi secara EKSPLISIT ($user->role = ...),
     * bukan melalui mass assignment.
     * Rule unique untuk email dan nim_nip dikecualikan untuk ID user
     * yang sedang diedit, agar tidak konflik dengan dirinya sendiri.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'nim_nip'  => [
                'nullable', 'string', 'max:50',
                Rule::unique('users', 'nim_nip')->ignore($user->id),
            ],
            'role'     => ['required', Rule::in(['admin', 'dosen', 'mahasiswa'])],
        ]);

        // Keamanan: Cegah self-demotion / penghapusan privilege satu-satunya Administrator
        if ($user->role === 'admin' && $validated['role'] !== 'admin') {
            $adminCount = User::where('role', 'admin')->whereNull('deleted_at')->count();
            if ($adminCount <= 1) {
                return back()->withInput()->withErrors([
                    'role' => 'Tidak dapat mengubah role satu-satunya Administrator yang tersisa.',
                ]);
            }
        }

        // Mass assignment hanya untuk kolom yang ada di $fillable
        $user->name  = $validated['name'];
        $user->email = $validated['email'];

        // Password hanya diupdate jika diisi
        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->nim_nip = $validated['nim_nip'] ?? null;

        // Role diisi secara EKSPLISIT — bukan mass assignment
        $user->role = $validated['role'];

        $user->save();

        return redirect()->route('users.index')
                         ->with('success', 'Pengguna "' . $user->name . '" berhasil diperbarui.');
    }

    /**
     * destroy — Menghapus pengguna dari database (soft delete).
     *
     * Route: DELETE /users/{user} → users.destroy
     */
    public function destroy(User $user)
    {
        // Keamanan: Cegah pengguna menghapus akunnya sendiri jika sedang login
        if (auth()->check() && auth()->id() === $user->id) {
            return redirect()->route('users.index')
                             ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.');
        }

        // Keamanan: Cegah penghapusan satu-satunya Administrator
        if ($user->role === 'admin') {
            $adminCount = User::where('role', 'admin')->whereNull('deleted_at')->count();
            if ($adminCount <= 1) {
                return redirect()->route('users.index')
                                 ->with('error', 'Tidak dapat menghapus satu-satunya Administrator yang tersisa.');
            }
        }

        try {
            $name = $user->name;
            $user->delete();

            return redirect()->route('users.index')
                             ->with('success', 'Pengguna "' . $name . '" berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('users.index')
                             ->with('error', 'Gagal menghapus pengguna. Pastikan tidak ada data terkait.');
        }
    }
}
