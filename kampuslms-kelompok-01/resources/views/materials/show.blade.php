<x-layout :title="'Detail Materi — ' . $material->title">
    <div class="page-container">
        {{-- Breadcrumb --}}
        <nav class="breadcrumb">
            @if(auth()->user()->role === 'dosen')
                <a href="{{ route('dosen.courses.index') }}" class="breadcrumb-link">Mata Kuliah</a>
                <span class="breadcrumb-separator">›</span>
                <a href="{{ route('dosen.courses.materials.index', $material->course) }}" class="breadcrumb-link">Materi</a>
            @else
                <a href="{{ route('mahasiswa.courses.index') }}" class="breadcrumb-link">Mata Kuliah</a>
                <span class="breadcrumb-separator">›</span>
                <a href="{{ route('mahasiswa.courses.materials.index', $material->course) }}" class="breadcrumb-link">Materi</a>
            @endif
            <span class="breadcrumb-separator">›</span>
            <span class="breadcrumb-current">{{ $material->title }}</span>
        </nav>

        {{-- Detail Header --}}
        <div class="detail-header" style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 1rem; padding: 2rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div>
                    <span style="font-size: 0.8rem; font-weight: 700; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 0.25rem 0.6rem; border-radius: 0.25rem;">
                        {{ $material->course->code }} — {{ $material->course->name }}
                    </span>
                    <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main, #f8fafc); margin: 0.5rem 0 0.25rem 0;">
                        {{ $material->title }}
                    </h1>
                    <p style="color: var(--text-muted, #94a3b8); font-size: 0.85rem; margin: 0;">
                        🕒 Diunggah pada: {{ $material->created_at->format('d F Y, H:i') }} WITA
                    </p>
                </div>

                @can('update', $material)
                <div style="display: flex; gap: 0.5rem;">
                    <a href="{{ route('dosen.materials.edit', $material) }}" class="btn btn-edit">
                        ✏️ Edit
                    </a>
                </div>
                @endcan
            </div>

            {{-- Deskripsi --}}
            <div style="padding-top: 1rem; border-top: 1px solid var(--card-border, #334155); margin-top: 1rem;">
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-main, #f8fafc); margin-bottom: 0.5rem;">Deskripsi & Catatan Materi</h3>
                <p style="color: var(--text-muted, #94a3b8); line-height: 1.6; margin: 0;">
                    {{ $material->description ?: 'Tidak ada catatan atau deskripsi tambahan untuk materi ini.' }}
                </p>
            </div>

            {{-- Lampiran / Link --}}
            <div style="margin-top: 1.5rem; padding: 1.25rem; background: rgba(255, 255, 255, 0.03); border-radius: 0.75rem; border: 1px solid var(--card-border, #334155);">
                <div style="font-weight: 600; color: var(--text-main, #f8fafc); margin-bottom: 0.5rem;">
                    {{ $material->type === 'link' ? '🔗 Tautan Materi Eksternal:' : '📄 Berkas Materi:' }}
                </div>
                @if ($material->type === 'link')
                    <a href="{{ $material->external_url }}" target="_blank" rel="noopener noreferrer" style="color: #38bdf8; word-break: break-all; font-weight: 600;">
                        {{ $material->external_url }} ↗
                    </a>
                @else
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
                        <div>
                            <span style="color: var(--text-main, #f8fafc); font-family: monospace; font-weight: 600;">
                                📄 {{ $material->original_name ?: $material->title . '.pdf' }}
                            </span>
                            @if ($material->file_size)
                                <span style="font-size: 0.8rem; color: var(--text-muted, #94a3b8); margin-left: 0.5rem;">
                                    ({{ $material->humanReadableSize() }})
                                </span>
                            @endif
                            @if ($material->mime_type)
                                <span style="font-size: 0.75rem; padding: 0.15rem 0.5rem; border-radius: 9999px; background: rgba(16, 185, 129, 0.12); color: #10b981; font-weight: 600; margin-left: 0.5rem;">
                                    {{ strtoupper(str_replace('application/', '', $material->mime_type)) }}
                                </span>
                            @endif
                        </div>

                        {{-- Tombol Unduh — memanggil endpoint download yang melewati Policy --}}
                        @if ($material->file_path)
                            @php
                                $downloadRoute = auth()->user()->role === 'dosen'
                                    ? route('dosen.materials.download', $material)
                                    : route('mahasiswa.materials.download', $material);
                            @endphp
                            <a href="{{ $downloadRoute }}"
                               class="btn btn-primary"
                               style="font-size: 0.85rem; padding: 0.5rem 1rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                                ⬇️ Unduh Berkas
                            </a>
                        @else
                            <span style="font-size: 0.8rem; color: #f59e0b; font-weight: 600;">
                                ⚠️ Berkas belum diunggah
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Tombol Kembali --}}
        <div>
            @if(auth()->user()->role === 'dosen')
                <a href="{{ route('dosen.courses.materials.index', $material->course) }}" class="back-link">
                    ← Kembali ke Daftar Materi
                </a>
            @else
                <a href="{{ route('mahasiswa.courses.materials.index', $material->course) }}" class="back-link">
                    ← Kembali ke Daftar Materi
                </a>
            @endif
        </div>
    </div>
</x-layout>
