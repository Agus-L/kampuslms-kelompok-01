{{--
    Course Index View — Daftar Semua Mata Kuliah
    Route: GET /courses → courses.index
    Controller: CourseController@index

    View ini menerima variabel $courses (LengthAwarePaginator berisi
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

    {{-- ── Form Pencarian & Filter ── --}}
    <div class="search-filter-bar">
        <form action="{{ route('courses.index') }}" method="GET" class="search-filter-form">
            <div class="search-input-wrapper">
                <span class="search-icon">🔍</span>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari kode atau nama mata kuliah..."
                       class="search-input">
            </div>
            <select name="status" class="filter-select">
                <option value="">Semua Status</option>
                <option value="active"   {{ request('status') == 'active'   ? 'selected' : '' }}>✅ Aktif</option>
                <option value="draft"    {{ request('status') == 'draft'    ? 'selected' : '' }}>📝 Draft</option>
                <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>🗃️ Arsip</option>
            </select>
            <button type="submit" class="btn btn-search">Cari</button>
            @if(request('search') || request('status'))
                <a href="{{ route('courses.index') }}" class="btn btn-reset" title="Reset filter">✕ Reset</a>
            @endif
        </form>
    </div>

    {{-- ── Statistik Ringkas ── --}}
    <div class="stats-bar">
        <div class="stat-item">
            <span class="stat-icon">📚</span>
            <span class="stat-value">{{ $courses->total() }}</span>
            <span class="stat-label">Mata Kuliah</span>
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
                <p>📭 Tidak ada mata kuliah yang ditemukan.
                    @if(request('search') || request('status'))
                        <a href="{{ route('courses.index') }}">Tampilkan semua</a>.
                    @else
                        <a href="{{ route('courses.create') }}">Tambah sekarang</a>.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    {{-- ── Pagination ── --}}
    @if ($courses->hasPages())
        <div class="pagination-wrapper">
            {{ $courses->links() }}
        </div>
    @endif

</x-layout>
