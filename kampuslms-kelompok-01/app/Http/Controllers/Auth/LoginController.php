<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Menampilkan formulir login.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Memproses percobaan login pengguna (mendukung Email atau NIM/NIP).
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
            'remember' => 'nullable|boolean',
        ], [
            'login.required'    => 'Email atau NIM / NIP wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $loginInput = trim($validated['login']);
        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'nim_nip';

        $credentials = [
            $field     => $loginInput,
            'password' => $validated['password'],
        ];

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang kembali, ' . $user->name . ' (' . ucfirst($user->role) . ')!');
        }

        return back()
            ->withInput($request->only('login', 'remember'))
            ->withErrors([
                'login' => 'Email/NIM/NIP atau kata sandi yang Anda masukkan salah.',
            ]);
    }

    /**
     * Mengeluarkan pengguna dari sesi (Logout).
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sesi.');
    }
}
