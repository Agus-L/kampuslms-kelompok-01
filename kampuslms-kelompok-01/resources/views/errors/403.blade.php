{{--
    403 Forbidden — Custom Error Page
    ──────────────────────────────────
    BUILD nomor 6 — Minggu 5: Halaman 403 kustom yang informatif
    tapi tidak membocorkan informasi.

    KEAMANAN:
      ❌ JANGAN tampilkan info siapa pemilik data
      ❌ JANGAN tampilkan detail teknis
      ✅ Pesan generik yang ramah
--}}

<x-layout title="403 - Akses Ditolak">
    <div class="error-page">
        <h1 class="error-page-code">403</h1>
        <h2 class="error-page-title">Akses Ditolak</h2>
        <p class="error-page-message">
            Anda tidak memiliki izin untuk mengakses halaman ini.
            Pastikan Anda sudah login dengan akun yang memiliki hak akses yang sesuai.
        </p>
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

