<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Periksa apakah user yang sedang login memiliki salah satu
     * dari peran yang diizinkan.
     *
     * Pemakaian di route:
     *   middleware('role:admin')           → hanya admin
     *   middleware('role:admin,dosen')     → admin ATAU dosen
     *
     * @param  string  ...$roles  Daftar peran yang diizinkan (variadic)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // 1. Pastikan user sudah login
        // 2. Pastikan role user ada di daftar yang diizinkan
        abort_unless(
            $request->user() && in_array($request->user()->role, $roles),
            403,
            'Anda tidak memiliki akses ke halaman ini.'
        );

        return $next($request);
    }
}
