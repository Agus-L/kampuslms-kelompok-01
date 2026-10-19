<x-layout :title="'Kumpulkan Tugas — ' . $assignment->title">
    <div class="page-container">
        {{-- Breadcrumb --}}
        <nav class="breadcrumb">
            <a href="{{ route('mahasiswa.courses.index') }}" class="breadcrumb-link">Mata Kuliah</a>
            <span class="breadcrumb-separator">›</span>
            <a href="{{ route('mahasiswa.courses.assignments.index', $assignment->course) }}" class="breadcrumb-link">Tugas</a>
            <span class="breadcrumb-separator">›</span>
            <a href="{{ route('mahasiswa.assignments.show', $assignment) }}" class="breadcrumb-link">{{ $assignment->title }}</a>
            <span class="breadcrumb-separator">›</span>
            <span class="breadcrumb-current">Pengumpulan Tugas</span>
        </nav>

        <div style="max-width: 800px; margin: 0 auto;">
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 1rem; padding: 2rem;">
                <div style="margin-bottom: 1.5rem;">
                    <span style="font-size: 0.8rem; font-weight: 700; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 0.25rem 0.6rem; border-radius: 0.25rem;">
                        {{ $assignment->course->code }} — {{ $assignment->course->name }}
                    </span>
                    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main, #f8fafc); margin: 0.5rem 0 0.25rem 0;">
                        📤 Form Pengumpulan Tugas: {{ $assignment->title }}
                    </h1>
                    <div style="display: flex; gap: 1rem; font-size: 0.85rem; color: var(--text-muted, #94a3b8); margin-top: 0.5rem; flex-wrap: wrap;">
                        <span>⏰ Batas Waktu: <strong style="color: #f87171;">{{ $assignment->due_at->format('d F Y, H:i') }} WITA</strong></span>
                        <span>•</span>
                        <span>🎯 Skor Maksimum: <strong>{{ $assignment->max_score }}</strong></span>
                    </div>
                </div>

                @if(now()->isAfter($assignment->due_at))
                    @if(!$assignment->allow_late)
                        <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; padding: 0.85rem 1.25rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.9rem;">
                            🚫 <strong>Pengumpulan Ditutup:</strong> Batas waktu pengumpulan tugas ini telah terlewati ({{ $assignment->due_at->diffForHumans() }}) dan tugas ini <strong>tidak mengizinkan keterlambatan</strong>.
                        </div>
                    @else
                        <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; padding: 0.85rem 1.25rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.9rem;">
                            ⚠️ <strong>Peringatan Batas Waktu:</strong> Batas waktu pengumpulan tugas ini telah terlewati ({{ $assignment->due_at->diffForHumans() }}). Tugas Anda akan dicatat sebagai <em>Terlambat</em>.
                        </div>
                    @endif
                @endif

                <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--card-border, #334155); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem;">
                    <div style="font-weight: 700; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">Petunjuk Pengerjaan:</div>
                    <div style="font-size: 0.9rem; color: var(--text-main); line-height: 1.5; white-space: pre-line;">
                        {{ $assignment->instructions ?: 'Ikuti petunjuk sesuai materi perkuliahan.' }}
                    </div>
                </div>

                <form action="{{ route('mahasiswa.assignments.submissions.store', $assignment) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div style="margin-bottom: 1.5rem;">
                        <label for="file" style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem; font-size: 0.95rem;">
                            Unggah Berkas Tugas (Wajib) <span style="color: #f87171;">*</span>
                        </label>
                        <input
                            type="file"
                            name="file"
                            id="file"
                            required
                            class="search-input"
                            style="width: 100%; padding: 0.75rem; background: var(--bg); border: 1px dashed var(--card-border, #334155);"
                        >
                        <p style="font-size: 0.8rem; color: var(--text-muted, #94a3b8); margin-top: 0.35rem;">
                            Format yang didukung: PDF, DOC, DOCX (Maks. 5 MB).
                        </p>
                        @error('file')
                            <div style="color: #f87171; font-size: 0.82rem; margin-top: 0.25rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div style="margin-bottom: 1.75rem;">
                        <label for="note" style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem; font-size: 0.95rem;">
                            Catatan Mahasiswa (Opsional)
                        </label>
                        <textarea
                            name="note"
                            id="note"
                            rows="4"
                            class="search-input"
                            style="width: 100%; resize: vertical;"
                            placeholder="Tuliskan keterangan tambahan atau tautan demo jika diperlukan..."
                        >{{ old('note') }}</textarea>
                        @error('note')
                            <div style="color: #f87171; font-size: 0.82rem; margin-top: 0.25rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <a href="{{ route('mahasiswa.assignments.show', $assignment) }}" class="btn btn-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.5rem; font-weight: 700;">
                            🚀 Kirim Tugas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
