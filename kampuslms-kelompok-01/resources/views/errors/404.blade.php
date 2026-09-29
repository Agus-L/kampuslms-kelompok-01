{{--
    404 Not Found — Custom Error Page
    ──────────────────────────────────

    Halaman ini ditampilkan saat user mengakses URL yang tidak ada.
    Menggunakan design system error-page yang sama dengan 403
    agar tampilan error pages konsisten.
--}}

<x-layout title="404 - Halaman Tidak Ditemukan">
    <div class="error-page">
        {{-- Error Code --}}
        <h1 class="error-page-code">404</h1>

        {{-- Title --}}
        <h2 class="error-page-title">Halaman Tidak Ditemukan</h2>

        {{-- Message --}}
        <p class="error-page-message">
            Maaf, halaman yang Anda cari tidak ada atau alamat rutenya salah.
        </p>

        {{-- Action Buttons --}}
        <div class="error-page-actions">
            <a href="javascript:history.back()" class="error-page-btn error-page-btn-secondary">
                Kembali
            </a>

            @auth
                <a href="{{ route('dashboard') }}" class="error-page-btn error-page-btn-primary">
                    Ke Dashboard
                </a>
            @else
                <a href="{{ route('home') }}" class="error-page-btn error-page-btn-primary">
                    Ke Beranda
                </a>
            @endauth
        </div>
    </div>
</x-layout>