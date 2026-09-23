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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    {{-- ── Navbar ── --}}
    <nav class="navbar">
        {{--
            KENAPA route('courses.index') bukan '/'?
            → Brand mengarah ke daftar mata kuliah sebagai "beranda" modul ini.
              Menggunakan helper route() agar tautan tidak pernah "patah"
              meskipun URI-nya diubah di web.php.
        --}}
        <a href="{{ route('courses.index') }}" class="brand">
            <span class="brand-icon">🎓</span>
            Kampus LMS
        </a>

        <div class="nav-links">
            {{--
                request()->routeIs('courses.index') mengembalikan true
                jika route saat ini bernama 'courses.index'.
                Digunakan untuk memberi class 'active' secara dinamis.
            --}}
            <a href="{{ route('courses.index') }}"
               class="nav-link {{ request()->routeIs('courses.*') ? 'active' : '' }}">
                Mata Kuliah
            </a>
            <a href="{{ route('users.index') }}"
               class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                Pengguna
            </a>
            <a href="{{ route('tentang') }}"
                class="nav-link {{ request()->routeIs('tentang') ? 'active' : '' }}">
                Tentang
            </a>

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
        {{-- ── Flash Messages Global ── --}}
        @if (session('success'))
            <div class="alert alert-success">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                ❌ {{ session('error') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    {{-- ── Footer ── --}}
    <footer class="footer">
        &copy; {{ date('Y') }} Kampus LMS — Kelompok 01 · Pemrograman Web
    </footer>
</body>
</html>
