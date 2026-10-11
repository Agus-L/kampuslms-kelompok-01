<x-layout :title="'Materi Kuliah — ' . $course->name">
    <div class="page-container">
        {{-- Breadcrumb --}}
        <nav class="breadcrumb">
            @if(auth()->user()->role === 'dosen')
                <a href="{{ route('dosen.courses.index') }}" class="breadcrumb-link">Mata Kuliah</a>
                <span class="breadcrumb-separator">›</span>
                <a href="{{ route('dosen.courses.show', $course) }}" class="breadcrumb-link">{{ $course->name }}</a>
            @else
                <a href="{{ route('mahasiswa.courses.index') }}" class="breadcrumb-link">Mata Kuliah</a>
                <span class="breadcrumb-separator">›</span>
                <a href="{{ route('mahasiswa.courses.show', $course) }}" class="breadcrumb-link">{{ $course->name }}</a>
            @endif
            <span class="breadcrumb-separator">›</span>
            <span class="breadcrumb-current">Materi</span>
        </nav>

        {{-- Header --}}
        <div class="page-header">
            <div class="page-header-top">
                <div>
                    <h1 class="page-title">📑 Materi Kuliah: {{ $course->name }}</h1>
                    <p class="page-subtitle">{{ $course->code }} • {{ $course->sks }} SKS • Dosen: {{ $course->lecturer->name ?? '-' }}</p>
                </div>
                @can('create', [\App\Models\Material::class, $course])
                <div>
                    <a href="{{ route('dosen.courses.materials.create', $course) }}" class="btn btn-create">
                        + Tambah Materi
                    </a>
                </div>
                @endcan
            </div>
        </div>

        {{-- Navigasi Tab Kursus (Materi & Tugas) --}}
        @php
            $rolePrefix = auth()->user()->role === 'dosen' ? 'dosen' : 'mahasiswa';
        @endphp
        <div style="display: flex; gap: 0.75rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--card-border, #334155); padding-bottom: 0.75rem;">
            <a href="{{ route($rolePrefix . '.courses.materials.index', $course) }}"
               style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1.1rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.9rem; text-decoration: none; background: var(--primary, #6366f1); color: #ffffff;">
                📑 Materi Kuliah
            </a>
            <a href="{{ route($rolePrefix . '.courses.assignments.index', $course) }}"
               style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1.1rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.9rem; text-decoration: none; background: var(--card-bg, #1e293b); color: var(--text-muted, #94a3b8); border: 1px solid var(--card-border, #334155);">
                📝 Penugasan
            </a>
        </div>

        {{-- Flash message --}}
        @if (session('success'))
            <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #10b981; padding: 0.85rem 1.25rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                ✓ {{ session('success') }}
            </div>
        @endif

        {{-- List Materi --}}
        @if ($materials->isEmpty())
            <div style="text-align: center; padding: 3rem 1rem; background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 1rem; color: var(--text-muted, #94a3b8);">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📂</div>
                <h3 style="color: var(--text-main, #f8fafc); font-weight: 700; margin-bottom: 0.25rem;">Belum Ada Materi</h3>
                <p>Belum ada materi perkuliahan yang diunggah untuk kelas ini.</p>
            </div>
        @else
            <div style="display: grid; gap: 1rem;">
                @foreach ($materials as $material)
                    <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                                <span style="font-size: 1.2rem;">{{ $material->type === 'link' ? '🔗' : '📄' }}</span>
                                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main, #f8fafc); margin: 0;">
                                    {{ $material->title }}
                                </h3>
                                <span style="font-size: 0.75rem; padding: 0.15rem 0.5rem; border-radius: 9999px; background: rgba(99, 102, 241, 0.15); color: #818cf8; font-weight: 600;">
                                    {{ strtoupper($material->type) }}
                                </span>
                            </div>
                            <p style="color: var(--text-muted, #94a3b8); font-size: 0.85rem; margin: 0 0 0.5rem 0;">
                                {{ Str::limit($material->description, 120) }}
                            </p>
                            <div style="font-size: 0.75rem; color: var(--text-muted, #94a3b8);">
                                🕒 Diunggah: {{ $material->created_at->format('d M Y, H:i') }}
                            </div>
                        </div>

                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            @if(auth()->user()->role === 'dosen')
                                <a href="{{ route('dosen.materials.show', $material) }}" class="btn btn-secondary" style="font-size: 0.82rem; padding: 0.4rem 0.8rem;">
                                    Lihat Detail
                                </a>
                                @can('update', $material)
                                <a href="{{ route('dosen.materials.edit', $material) }}" class="btn btn-edit" style="font-size: 0.82rem; padding: 0.4rem 0.8rem;">
                                    ✏️ Edit
                                </a>
                                @endcan
                                @can('delete', $material)
                                <form action="{{ route('dosen.materials.destroy', $material) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus materi ini?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-delete" style="font-size: 0.82rem; padding: 0.4rem 0.8rem;">
                                        🗑️ Hapus
                                    </button>
                                </form>
                                @endcan
                            @else
                                <a href="{{ route('mahasiswa.materials.show', $material) }}" class="btn btn-secondary" style="font-size: 0.82rem; padding: 0.4rem 0.8rem;">
                                    Lihat / Unduh
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="margin-top: 1.5rem;">
                {{ $materials->links() }}
            </div>
        @endif
    </div>
</x-layout>
