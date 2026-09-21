{{--
    Course Index View — Daftar Semua Mata Kuliah
    Route: GET /courses → courses.index
    Controller: CourseController@index

    View ini menerima variabel $courses (Eloquent Collection berisi
    model Course) yang dikirim dari controller via compact('courses').
--}}

{{--
    <x-layout> memanggil komponen resources/views/components/layout.blade.php.
    Prop :title dioper agar tag <title> di layout menampilkan judul yang sesuai.
--}}
<x-layout :title="'Daftar Mata Kuliah — Kampus LMS'">

    {{-- ── Header Halaman ── --}}
    <div class="page-header">
        <div class="page-header-top">
            <div>
                <div class="page-badge">
                    <span class="page-badge-dot"></span>
                    Modul Mata Kuliah
                </div>
                <h1 class="page-title">Daftar Mata Kuliah</h1>
                <p class="page-subtitle">
                    Jelajahi mata kuliah yang tersedia.
                    Klik pada mata kuliah untuk melihat detail lengkapnya.
                </p>
            </div>
            {{-- Tombol Tambah MK --}}
            <a href="{{ route('courses.create') }}" class="btn btn-create">
                + Tambah Mata Kuliah
            </a>
        </div>
    </div>

    {{-- ── Statistik Ringkas ── --}}
    <div class="stats-bar">
        <div class="stat-item">
            <span class="stat-icon">📚</span>
            <span class="stat-value">{{ $courses->count() }}</span>
            <span class="stat-label">Mata Kuliah</span>
        </div>
        <div class="stat-item">
            <span class="stat-icon">✅</span>
            <span class="stat-value">{{ $courses->where('status', 'active')->count() }}</span>
            <span class="stat-label">Aktif</span>
        </div>
        <div class="stat-item">
            <span class="stat-icon">📝</span>
            <span class="stat-value">{{ $courses->where('status', 'draft')->count() }}</span>
            <span class="stat-label">Draft</span>
        </div>
    </div>

    {{-- ── Grid Kartu Mata Kuliah ── --}}
    <div class="courses-grid">
        @forelse ($courses as $course)
            <div class="course-card">
                {{-- Badge status + kode MK --}}
                <div class="course-card-header">
                    <span class="course-kode">
                        📖 {{ $course->code }}
                    </span>
                    <span class="course-status course-status-{{ $course->status }}">
                        {{ ucfirst($course->status) }}
                    </span>
                </div>

                {{-- Nama mata kuliah --}}
                <h2 class="course-nama">{{ $course->name }}</h2>

                {{-- Nama dosen pengampu --}}
                <p class="course-dosen">
                    👤 {{ $course->lecturer->name ?? 'Belum ditentukan' }}
                </p>

                {{-- Meta info: SKS --}}
                <div class="course-meta">
                    <span class="course-meta-item">
                        ⏱ <span class="course-meta-value">{{ $course->sks }}</span> SKS
                    </span>
                </div>

                {{-- Tombol Aksi --}}
                <div class="course-actions">
                    <a href="{{ route('courses.show', $course) }}" class="course-link">
                        Lihat Detail
                        <span class="course-link-arrow">→</span>
                    </a>
                    <div class="course-actions-secondary">
                        <a href="{{ route('courses.edit', $course) }}" class="btn btn-edit" title="Edit">
                            ✏️ Edit
                        </a>
                        <form action="{{ route('courses.destroy', $course) }}" method="POST"
                              onsubmit="return confirm('Yakin ingin menghapus mata kuliah {{ $course->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-delete" title="Hapus">
                                🗑️ Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <p>📭 Belum ada mata kuliah. <a href="{{ route('courses.create') }}">Tambah sekarang</a>.</p>
            </div>
        @endforelse
    </div>

</x-layout>
