<div>
    {{-- ── Header Halaman ── --}}
    <div class="page-header">
        <div class="page-header-top">
            <div>
                <div class="page-badge">
                    <span class="page-badge-dot"></span>
                    Katalog Interaktif Livewire
                </div>
                <h1 class="page-title">Daftar Mata Kuliah</h1>
                <p class="page-subtitle">
                    Jelajahi mata kuliah di Kampus LMS secara cepat dan interaktif tanpa reload halaman.
                </p>
            </div>
            @can('create', App\Models\Course::class)
            <div>
                <a href="{{ route('admin.courses.create') }}" class="btn btn-create">
                    + Tambah Mata Kuliah
                </a>
            </div>
            @endcan
        </div>
    </div>

    {{-- ── Statistik Ringkas ── --}}
    <div class="stats-bar" style="margin-bottom: 1.5rem;">
        <div class="stat-item">
            <span class="stat-icon">📚</span>
            <span class="stat-value">{{ $stats['total'] }}</span>
            <span class="stat-label">Total Mata Kuliah</span>
        </div>
        <div class="stat-item">
            <span class="stat-icon">✅</span>
            <span class="stat-value">{{ $stats['active'] }}</span>
            <span class="stat-label">Aktif</span>
        </div>
        @if(!auth()->check() || auth()->user()->role !== 'mahasiswa')
        <div class="stat-item">
            <span class="stat-icon">📝</span>
            <span class="stat-value">{{ $stats['draft'] }}</span>
            <span class="stat-label">Draft</span>
        </div>
        <div class="stat-item">
            <span class="stat-icon">📦</span>
            <span class="stat-value">{{ $stats['archived'] }}</span>
            <span class="stat-label">Diarsipkan</span>
        </div>
        @endif
    </div>

    {{-- ── Search & Filter Bar Livewire ── --}}
    <div class="search-filter-bar">
        <div class="search-filter-form" style="display: flex; gap: 0.75rem; flex-wrap: wrap; width: 100%;">
            {{-- Input Pencarian Realtime --}}
            <div class="search-input-wrapper" style="flex: 1; min-width: 260px; position: relative;">
                <span class="search-icon">🔍</span>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    class="search-input"
                    placeholder="Ketik nama, kode MK, atau dosen pengampu..."
                    aria-label="Cari mata kuliah realtime"
                >
                <div wire:loading wire:target="search" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); font-size: 0.85rem; color: var(--primary-light);">
                    ⏳
                </div>
            </div>

            {{-- Filter Status Realtime --}}
            <select wire:model.live="status" class="filter-select" aria-label="Filter status">
                <option value="">Semua Status</option>
                <option value="active">Active (Aktif)</option>
                @if(!auth()->check() || auth()->user()->role !== 'mahasiswa')
                <option value="draft">Draft (Konsep)</option>
                <option value="archived">Archived (Arsip)</option>
                @endif
            </select>

            {{-- Tombol Reset Filter --}}
            @if ($search !== '' || $status !== '')
                <button type="button" wire:click="resetFilters" class="btn btn-reset" title="Hapus semua filter">
                    ✕ Reset Filter
                </button>
            @endif
        </div>
    </div>

    {{-- Loading indicator for any filter change --}}
    <div wire:loading.delay style="width: 100%; text-align: center; margin-bottom: 1rem;">
        <span style="display: inline-flex; align-items: center; gap: 0.5rem; background: var(--card-bg); border: 1px solid var(--primary); padding: 0.4rem 1rem; border-radius: 9999px; font-size: 0.85rem; color: var(--primary-light);">
            <span class="page-badge-dot"></span> Memperbarui daftar mata kuliah...
        </span>
    </div>

    {{-- Info Filter Aktif --}}
    @if ($search !== '' || $status !== '')
        <div class="search-info" style="margin-bottom: 1.5rem;">
            <span class="search-info-icon">📋</span>
            <span>
                Ditemukan <strong>{{ $courses->total() }}</strong> mata kuliah
                @if ($search !== '')
                    untuk kata kunci "<strong>{{ $search }}</strong>"
                @endif
                @if ($status !== '')
                    dengan status <strong>{{ ucfirst($status) }}</strong>
                @endif
            </span>
        </div>
    @endif

    {{-- ── Grid Kartu Mata Kuliah ── --}}
    @php
        $role = auth()->user()->role ?? 'guest';
    @endphp

    <div class="courses-grid" wire:loading.class="opacity-50">
        @forelse ($courses as $course)
            @php
                $showRoute = match($role) {
                    'dosen'     => route('dosen.courses.show', $course),
                    'mahasiswa' => route('mahasiswa.courses.show', $course),
                    'admin'     => route('admin.courses.show', $course),
                    default     => route('login'),
                };
            @endphp

            <div class="course-card" style="display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    {{-- Header Kartu: Kode & Status --}}
                    <div class="course-card-header">
                        <span class="course-kode">
                            📖 {{ $course->code }}
                        </span>
                        <span class="course-status course-status-{{ $course->status }}">
                            {{ ucfirst($course->status) }}
                        </span>
                    </div>

                    {{-- Nama Mata Kuliah --}}
                    <h2 class="course-nama" style="font-size: 1.15rem; font-weight: 800; line-height: 1.35; margin: 0.5rem 0;">
                        {{ $course->name }}
                    </h2>

                    {{-- Dosen Pengampu --}}
                    <p class="course-dosen" style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                        👤 {{ $course->lecturer->name ?? 'Dosen Belum Ditentukan' }}
                    </p>

                    {{-- Deskripsi Singkat --}}
                    @if($course->description)
                        <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 1rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $course->description }}
                        </p>
                    @endif
                </div>

                <div>
                    {{-- Meta Info --}}
                    <div class="course-meta" style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1rem; padding-top: 0.75rem; border-top: 1px solid var(--card-border);">
                        <span class="course-meta-item" style="font-size: 0.8rem; color: var(--text-muted);">
                            ⏱ <strong style="color: var(--text-main);">{{ $course->sks }}</strong> SKS
                        </span>
                        <span class="course-meta-item" style="font-size: 0.8rem; color: var(--text-muted);">
                            📑 <strong style="color: var(--text-main);">{{ $course->materials_count }}</strong> Materi
                        </span>
                        <span class="course-meta-item" style="font-size: 0.8rem; color: var(--text-muted);">
                            📝 <strong style="color: var(--text-main);">{{ $course->assignments_count }}</strong> Tugas
                        </span>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="course-actions">
                        <a href="{{ $showRoute }}" class="course-link">
                            {{ $role === 'guest' ? 'Masuk untuk Akses' : 'Buka Kelas' }}
                            <span class="course-link-arrow">→</span>
                        </a>

                        @if($role === 'admin')
                            <div class="course-actions-secondary" style="display: flex; gap: 0.4rem;">
                                @can('update', $course)
                                <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-edit" title="Edit" style="font-size: 0.78rem; padding: 0.35rem 0.65rem;">
                                    ✏️
                                </a>
                                @endcan
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state" style="grid-column: 1 / -1; text-align: center; padding: 3.5rem 1rem; background: var(--card-bg); border: 1px dashed var(--card-border); border-radius: 1rem;">
                <div style="font-size: 3rem; margin-bottom: 0.5rem;">🔍</div>
                <h3 style="color: var(--text-main); font-weight: 700; margin-bottom: 0.35rem;">Tidak Ada Mata Kuliah</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem;">
                    Tidak ditemukan mata kuliah yang sesuai dengan kata kunci atau filter saat ini.
                </p>
                @if ($search !== '' || $status !== '')
                    <button type="button" wire:click="resetFilters" class="btn btn-primary" style="font-size: 0.85rem; padding: 0.5rem 1.1rem;">
                        Tampilkan Semua Mata Kuliah
                    </button>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div style="margin-top: 2rem;">
        {{ $courses->links() }}
    </div>
</div>
