{{--
    Layout Component — resources/views/components/layout.blade.php

    KENAPA Blade Component (x-layout), bukan @extends/@section?
    → Laravel 12 merekomendasikan pendekatan komponen karena:
      1. Lebih deklaratif & mudah dibaca (seperti tag HTML biasa)
      2. Mendukung props & slot secara native
      3. Tidak perlu menghafal nama @section — cukup tulis konten di
         dalam <x-layout>...</x-layout>, dan itu otomatis menjadi {{ $slot }}

    KENAPA ada $title = 'Kampus LMS'?
    → Default value supaya halaman yang lupa mengoper prop title
      tetap menampilkan judul yang wajar.

    File ini otomatis dikenali Laravel sebagai komponen karena berada
    di folder resources/views/components/.
--}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Judul halaman — bisa diubah per-halaman melalui prop :title --}}
    <title>{{ $title ?? 'Kampus LMS' }}</title>

    {{--
        @vite() memuat resources/css/app.css dan resources/js/app.js
        melalui pipeline Vite (Laravel 12).

        Keunggulan dibanding inline <style>:
          • Hot Module Replacement (HMR) — perubahan CSS langsung terlihat
            di browser tanpa refresh manual, saat `npm run dev` berjalan.
          • Cache-busting otomatis — Vite menambahkan hash pada nama file
            saat `npm run build`, sehingga browser selalu memuat CSS terbaru.
          • Semua CSS dikelola di resources/css/app.css — satu sumber kebenaran.

        SYARAT: jalankan `npm run dev` paralel dengan `php artisan serve`
        agar aset tersedia saat development.
    --}}
    {{-- Script anti-flicker tema: dieksekusi sebelum render body agar tidak berkedip --}}
    <script>
        (function () {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const activeTheme = savedTheme ? savedTheme : (prefersDark ? 'dark' : 'dark');
            document.documentElement.setAttribute('data-theme', activeTheme);
        })();
    </script>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @livewireStyles
</head>
<body>
    {{-- ── Navbar ── --}}
    <nav class="navbar">
        {{--
            Brand mengarah ke daftar mata kuliah (admin) sebagai "beranda" modul.
            Menggunakan helper route() agar tautan tidak pernah "patah"
            meskipun URI-nya diubah di web.php.
        --}}
        <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="brand">
            <span class="brand-icon">🎓</span>
            Kampus LMS
        </a>

        <div class="nav-links">
            @if(auth()->check())
                <a href="{{ route('dashboard') }}"
                   class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    Dashboard
                </a>

                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.courses.index') }}"
                       class="nav-link {{ request()->routeIs('admin.courses.*') ? 'active' : '' }}">
                        Mata Kuliah
                    </a>
                    <a href="{{ route('admin.users.index') }}"
                       class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        Pengguna
                    </a>
                @elseif(auth()->user()->role === 'dosen')
                    <a href="{{ route('dosen.courses.index') }}"
                       class="nav-link {{ request()->routeIs('dosen.courses.*') ? 'active' : '' }}">
                        Kelas Saya
                    </a>
                    <a href="{{ route('dosen.transcript.index') }}"
                       class="nav-link {{ request()->routeIs('dosen.transcript.*') ? 'active' : '' }}">
                        Transkrip Pengampu
                    </a>
                @elseif(auth()->user()->role === 'mahasiswa')
                    <a href="{{ route('mahasiswa.courses.index') }}"
                       class="nav-link {{ request()->routeIs('mahasiswa.courses.*') ? 'active' : '' }}">
                        Mata Kuliah
                    </a>
                @endif
            @else
                <a href="{{ route('home') }}"
                   class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                    Beranda
                </a>
            @endif

            <a href="{{ route('tentang') }}"
                class="nav-link {{ request()->routeIs('tentang') ? 'active' : '' }}">
                Tentang
            </a>

            @if(auth()->check())
                {{-- ── Lonceng Notifikasi Realtime Polling (Minggu 10 - Milestone M3) ── --}}
                <livewire:notification-bell />

                <div class="auth-user-badge" style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.3rem 0.75rem; border-radius:9999px; background:var(--badge-bg); border:1px solid var(--badge-border); font-size:0.82rem; color:var(--text-main);">
                    <span>👤 <strong>{{ auth()->user()->name }}</strong> <span style="color:var(--primary-light); text-transform:capitalize;">({{ auth()->user()->role }})</span></span>
                    <form action="{{ route('logout') }}" method="POST" style="display:inline; margin:0; padding:0;">
                        @csrf
                        <button type="submit" title="Keluar dari sesi" style="background:none; border:none; color:#f87171; cursor:pointer; font-weight:600; font-size:0.82rem; padding:0 0.25rem;">
                            Keluar
                        </button>
                    </form>
                </div>
            @else
                <a href="{{ route('login') }}" class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}" style="background:var(--primary); color:#ffffff; padding:0.4rem 0.9rem; border-radius:0.5rem; font-weight:600;">
                    Masuk
                </a>
            @endif

            {{-- ── Switch Button Dark / Light Mode (Pojok Kanan Atas) ── --}}
            <div class="theme-switch-wrapper">
                <span id="theme-label" class="theme-switch-label">Mode Gelap</span>
                <button id="theme-toggle" class="theme-switch" type="button" role="switch" aria-checked="false" aria-label="Ganti mode tampilan" title="Ganti mode tampilan">
                    <span class="theme-switch-slider"></span>
                </button>
            </div>
        </div>
    </nav>

    {{--
        ── Slot Utama ──
        Seluruh konten yang ditulis di antara <x-layout>...</x-layout>
        secara otomatis masuk ke variabel $slot ini.
    --}}
    <main class="main-content">
        {{--
            ── Global Flash Messages ──
            Ditaruh di layout agar berlaku untuk SELURUH halaman.
            Controller cukup memanggil ->with('success', '...') atau
            ->with('error', '...') dan pesan otomatis tampil di sini.
        --}}
        @if (session('success'))
            <div class="alert alert-success" role="alert">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error" role="alert">
                ❌ {{ session('error') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    {{-- ── Footer ── --}}
    <footer class="footer">
        &copy; {{ date('Y') }} Kampus LMS — Kelompok 01 · Pemrograman Web
    </footer>
    @livewireScripts
</body>
</html>
