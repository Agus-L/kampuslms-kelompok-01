{{--
    Course Show View — Detail Satu Mata Kuliah
    Route: GET /courses/{course} → courses.show
    Controller: CourseController@show

    View ini menerima variabel $course (array asosiatif berisi
    data satu mata kuliah) yang dikirim dari controller.
--}}

<x-layout :title="$course['nama'] . ' — Kampus LMS'">

    {{--
        CSS halaman ini (breadcrumb, detail-header, info-grid, dsb.)
        sudah dipindahkan ke resources/css/app.css dan dimuat via
        @vite() di layout.blade.php — tidak perlu <x-slot:head> lagi.
    --}}

    {{-- ── Breadcrumb ── --}}
    <nav class="breadcrumb">
        {{--
            Tautan "Mata Kuliah" kembali ke halaman index.
            KENAPA pakai route('courses.index')?
            → Konsistensi — semua tautan menggunakan named route.
        --}}
        <a href="{{ route('courses.index') }}" class="breadcrumb-link">Mata Kuliah</a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-current">{{ $course['nama'] }}</span>
    </nav>

    {{-- ── Header Detail ── --}}
    <div class="detail-header">
        <span class="detail-badge">
            📖 {{ $course['kode'] }}
        </span>
        {{-- h1 untuk SEO — satu halaman hanya boleh punya satu h1 --}}
        <h1 class="detail-title">{{ $course['nama'] }}</h1>
        <p class="detail-dosen">👤 {{ $course['dosen'] }}</p>
    </div>

    {{-- ── Info Cards: Ringkasan data penting ── --}}
    <div class="info-grid">
        <div class="info-card">
            <div class="info-card-icon">⏱</div>
            <div class="info-card-label">Bobot SKS</div>
            <div class="info-card-value">{{ $course['sks'] }} SKS</div>
        </div>
        <div class="info-card">
            <div class="info-card-icon">🎓</div>
            <div class="info-card-label">Semester</div>
            <div class="info-card-value">Semester {{ $course['semester'] }}</div>
        </div>
        <div class="info-card">
            <div class="info-card-icon">👥</div>
            <div class="info-card-label">Jumlah Mahasiswa</div>
            <div class="info-card-value">{{ $course['mahasiswa'] }} Orang</div>
        </div>
        <div class="info-card">
            <div class="info-card-icon">📍</div>
            <div class="info-card-label">Ruangan</div>
            <div class="info-card-value">{{ $course['ruangan'] }}</div>
        </div>
        <div class="info-card">
            <div class="info-card-icon">📅</div>
            <div class="info-card-label">Jadwal</div>
            <div class="info-card-value">{{ $course['jadwal'] }}</div>
        </div>
    </div>

    {{-- ── Deskripsi Mata Kuliah ── --}}
    <div class="description-card">
        <h2 class="description-title">📝 Deskripsi Mata Kuliah</h2>
        <p class="description-text">
            {{ $course['deskripsi'] }}
        </p>
    </div>

    {{-- ── Tombol Kembali ── --}}
    <a href="{{ route('courses.index') }}" class="back-link">
        <span class="back-arrow">←</span>
        Kembali ke Daftar Mata Kuliah
    </a>

</x-layout>
