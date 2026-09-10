{{--
    Course Index View — Daftar Semua Mata Kuliah
    Route: GET /courses → courses.index
    Controller: CourseController@index

    View ini menerima variabel $courses (array berisi data mata kuliah)
    yang dikirim dari controller via compact('courses').
--}}

{{--
    <x-layout> memanggil komponen resources/views/components/layout.blade.php.
    Prop :title dioper agar tag <title> di layout menampilkan judul yang sesuai.
    KENAPA pakai titik dua (:title)?
    → Artinya nilai prop adalah ekspresi PHP, bukan string mentah.
--}}
<x-layout :title="'Daftar Mata Kuliah — Kampus LMS'">

    {{--
        CSS halaman ini (page-header, courses-grid, course-card, dsb.)
        sudah dipindahkan ke resources/css/app.css dan dimuat via
        @vite() di layout.blade.php — tidak perlu <x-slot:head> lagi.
    --}}

    {{-- ── Header Halaman ── --}}
    <div class="page-header">
        <div class="page-badge">
            <span class="page-badge-dot"></span>
            Modul Mata Kuliah
        </div>
        <h1 class="page-title">Daftar Mata Kuliah</h1>
        <p class="page-subtitle">
            Jelajahi mata kuliah yang tersedia pada semester ini.
            Klik pada mata kuliah untuk melihat detail lengkapnya.
        </p>
    </div>

    {{-- ── Statistik Ringkas ── --}}
    <div class="stats-bar">
        <div class="stat-item">
            <span class="stat-icon">📚</span>
            {{--
                count($courses) menghitung jumlah elemen array.
                KENAPA {{ }} dan bukan {!! !!}?
                → {{ }} otomatis meng-escape karakter HTML untuk
                  mencegah serangan XSS. Selalu gunakan {{ }}
                  kecuali benar-benar perlu render HTML mentah.
            --}}
            <span class="stat-value">{{ count($courses) }}</span>
            <span class="stat-label">Mata Kuliah</span>
        </div>
        <div class="stat-item">
            <span class="stat-icon">📅</span>
            <span class="stat-value">Semester 5</span>
            <span class="stat-label">Periode Aktif</span>
        </div>
    </div>

    {{-- ── Grid Kartu Mata Kuliah ── --}}
    <div class="courses-grid">
        {{--
            @foreach mengulangi setiap elemen di array $courses.
            $course adalah variabel sementara berisi satu mata kuliah per iterasi.
        --}}
        @foreach ($courses as $course)
            <div class="course-card">
                {{-- Kode mata kuliah (badge kecil di atas) --}}
                <span class="course-kode">
                    📖 {{ $course['kode'] }}
                </span>

                {{-- Nama mata kuliah --}}
                <h2 class="course-nama">{{ $course['nama'] }}</h2>

                {{-- Nama dosen pengampu --}}
                <p class="course-dosen">
                    👤 {{ $course['dosen'] }}
                </p>

                {{-- Meta info: SKS, Mahasiswa, Semester --}}
                <div class="course-meta">
                    <span class="course-meta-item">
                        ⏱ <span class="course-meta-value">{{ $course['sks'] }}</span> SKS
                    </span>
                    <span class="course-meta-item">
                        👥 <span class="course-meta-value">{{ $course['mahasiswa'] }}</span> Mahasiswa
                    </span>
                    <span class="course-meta-item">
                        🎓 Semester <span class="course-meta-value">{{ $course['semester'] }}</span>
                    </span>
                </div>

                {{-- ═══════════════════════════════════════════
                    DEMO: Perbandingan route() vs Hardcode
                    URI sudah diubah ke /mata-kuliah/{id} di web.php
                ═══════════════════════════════════════════ --}}

                {{-- ✅ PAKAI route() — otomatis ikut berubah --}}
                <a href="{{ route('courses.show', $course['id']) }}" class="course-link">
                    Lihat Detail
                    <span class="course-link-arrow">→</span>
                </a>

                <!-- {{-- HARDCODE --}}
                <a href="/courses/{{ $course['id'] }}" class="course-link" style="background:#ef4444; margin-top:6px;">
                    Hardcode /courses/ → klik ini (broken!)
                    <span class="course-link-arrow">→</span> -->
                </a>
            </div>
        @endforeach
    </div>

</x-layout>
