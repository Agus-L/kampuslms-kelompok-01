{{--
    Course Index View — Daftar Semua Mata Kuliah
    Route: GET /courses → courses.index
    Controller: CourseController@index

    View ini menerima variabel $courses (LengthAwarePaginator berisi
    model Course) yang dikirim dari controller via compact('courses').

    Fitur Minggu 4 (BUILD No. 4):
    - Pencarian berdasarkan kode atau nama mata kuliah (query string ?q=...)
    - Filter berdasarkan status (query string ?status=...)
    - Pagination 15 per halaman dengan filter yang bertahan (withQueryString)
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
            @can('create', App\Models\Course::class)
            <a href="{{ route('admin.courses.create') }}" class="btn btn-create">
                + Tambah Mata Kuliah
            </a>
            @endcan
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         Minggu 4 — Pencarian & Filter (query string, bukan session)

         KENAPA query string?
         → Pengguna bisa membagikan URL hasil pencarian.
         → Tombol back browser tetap berfungsi.
         → Membuka dua tab tidak saling mengganggu.
         → Sesuai prinsip modul 4.1: "filter dan pencarian ke query string,
           jangan ke session."
    ══════════════════════════════════════════════════════ --}}
    @php
        $role = auth()->user()->role ?? 'guest';
        $courseIndexRoute = match($role) {
            'dosen' => route('dosen.courses.index'),
            'mahasiswa' => route('mahasiswa.courses.index'),
            default => route('admin.courses.index'),
        };
    @endphp
    <div class="search-filter-bar">
        <form method="GET" action="{{ $courseIndexRoute }}" class="search-filter-form">
            {{-- Input Pencarian --}}
            <div class="search-input-wrapper">
                <span class="search-icon">🔍</span>
                <input
                    type="text"
                    name="q"
                    class="search-input"
                    placeholder="Cari kode atau nama mata kuliah..."
                    value="{{ request('q') }}"
                    aria-label="Cari mata kuliah"
                >
            </div>

            {{-- Filter Status --}}
            <select name="status" class="filter-select" aria-label="Filter berdasarkan status">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                @if($role !== 'mahasiswa')
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                @endif
            </select>

            {{-- Tombol Cari --}}
            <button type="submit" class="btn btn-search">
                🔎 Cari
            </button>

            {{-- Tombol Reset — hanya tampil jika ada filter aktif --}}
            @if (request('q') || request('status'))
                <a href="{{ $courseIndexRoute }}" class="btn btn-reset" title="Hapus semua filter">
                    ✕ Reset
                </a>
            @endif
        </form>
    </div>

    {{-- ── Info Hasil Pencarian ── --}}
    @if (request('q') || request('status'))
        <div class="search-info">
            <span class="search-info-icon">📋</span>
            <span>
                Menampilkan <strong>{{ $courses->total() }}</strong> hasil
                @if (request('q'))
                    untuk pencarian "<strong>{{ request('q') }}</strong>"
                @endif
                @if (request('status'))
                    dengan status <strong>{{ ucfirst(request('status')) }}</strong>
                @endif
            </span>
        </div>
    @endif

    {{-- ── Statistik Ringkas ── --}}
    <div class="stats-bar">
        <div class="stat-item">
            <span class="stat-icon">📚</span>
            <span class="stat-value">{{ $courses->total() }}</span>
            <span class="stat-label">Mata Kuliah</span>
        </div>
        <div class="stat-item">
            <span class="stat-icon">✅</span>
            <span class="stat-value">{{ $courses->where('status', 'active')->count() }}</span>
            <span class="stat-label">Aktif</span>
        </div>
        @if($role !== 'mahasiswa')
        <div class="stat-item">
            <span class="stat-icon">📝</span>
            <span class="stat-value">{{ $courses->where('status', 'draft')->count() }}</span>
            <span class="stat-label">Draft</span>
        </div>
        @endif
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

                {{-- Meta info: SKS & Count --}}
                <div class="course-meta" style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <span class="course-meta-item">
                        ⏱ <span class="course-meta-value">{{ $course->sks }}</span> SKS
                    </span>
                    @if(isset($course->materials_count))
                    <span class="course-meta-item">
                        📑 <span class="course-meta-value">{{ $course->materials_count }}</span> Materi
                    </span>
                    @endif
                    @if(isset($course->assignments_count))
                    <span class="course-meta-item">
                        📝 <span class="course-meta-value">{{ $course->assignments_count }}</span> Tugas
                    </span>
                    @endif
                </div>

                {{-- Tombol Aksi --}}
                @php
                    $showRoute = match($role) {
                        'dosen' => route('dosen.courses.show', $course),
                        'mahasiswa' => route('mahasiswa.courses.show', $course),
                        default => route('admin.courses.show', $course),
                    };
                @endphp
                <div class="course-actions">
                    <a href="{{ $showRoute }}" class="course-link">
                        Lihat Detail
                        <span class="course-link-arrow">→</span>
                    </a>
                    @if($role === 'admin')
                        <div class="course-actions-secondary">
                            @can('update', $course)
                            <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-edit" title="Edit">
                                ✏️ Edit
                            </a>
                            @endcan
                            @can('delete', $course)
                            <button type="button" class="btn btn-delete" title="Hapus"
                                    data-modal-target="delete-course-{{ $course->id }}">
                                🗑️ Hapus
                            </button>
                            <x-delete-modal
                                id="delete-course-{{ $course->id }}"
                                :action="route('admin.courses.destroy', $course)"
                                :itemName="$course->name"
                            />
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-state">
                @if (request('q') || request('status'))
                    <p>📭 Tidak ada mata kuliah yang cocok dengan pencarian/filter Anda.
                        <a href="{{ $courseIndexRoute }}">Tampilkan semua</a>.
                    </p>
                @else
                    <p>📭 Belum ada mata kuliah.
                        @if($role === 'admin')
                            <a href="{{ route('admin.courses.create') }}">Tambah sekarang</a>.
                        @endif
                    </p>
                @endif
            </div>
        @endforelse
    </div>

    {{-- ══════════════════════════════════════════════════════
         Minggu 4 — Pagination Links

         $courses->links() menampilkan tautan navigasi halaman.
         Karena controller sudah memanggil ->withQueryString(),
         tautan halaman 2 dst. otomatis membawa filter yang aktif.
         Contoh: /courses?q=basis&status=active&page=2

         TANPA withQueryString(), klik halaman 2 akan kehilangan
         filter — bug klasik yang disebutkan di modul 4.1.
    ══════════════════════════════════════════════════════ --}}
    @if ($courses->hasPages())
        <div class="pagination-wrapper">
            {{ $courses->links() }}
        </div>
    @endif

</x-layout>
