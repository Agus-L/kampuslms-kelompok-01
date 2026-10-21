<x-layout :title="'Revisi Tugas — ' . $submission->assignment->title">
    <div class="page-container">
        {{-- Breadcrumb --}}
        <nav class="breadcrumb">
            <a href="{{ route('mahasiswa.courses.index') }}" class="breadcrumb-link">Mata Kuliah</a>
            <span class="breadcrumb-separator">›</span>
            <a href="{{ route('mahasiswa.courses.assignments.index', $submission->assignment->course) }}" class="breadcrumb-link">Tugas</a>
            <span class="breadcrumb-separator">›</span>
            <a href="{{ route('mahasiswa.assignments.show', $submission->assignment) }}" class="breadcrumb-link">{{ $submission->assignment->title }}</a>
            <span class="breadcrumb-separator">›</span>
            <span class="breadcrumb-current">Revisi Pengumpulan</span>
        </nav>

        <div style="max-width: 800px; margin: 0 auto;">
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 1rem; padding: 2rem;">
                <div style="margin-bottom: 1.5rem;">
                    <span style="font-size: 0.8rem; font-weight: 700; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 0.25rem 0.6rem; border-radius: 0.25rem;">
                        {{ $submission->assignment->course->code }} — {{ $submission->assignment->course->name }}
                    </span>
                    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main, #f8fafc); margin: 0.5rem 0 0.25rem 0;">
                        ✏️ Perbarui / Unggah Ulang Berkas Tugas
                    </h1>
                    <p style="color: var(--text-muted, #94a3b8); margin-top: 0.25rem; font-size: 0.9rem;">
                        {{ $submission->assignment->title }}
                    </p>
                </div>

                <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--card-border, #334155); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; font-size: 0.85rem;">
                    <div style="color: var(--text-muted); margin-bottom: 0.25rem;">Berkas yang sudah dikumpulkan sebelumnya:</div>
                    <div style="font-weight: 700; color: #38bdf8;">
                        📎 {{ $submission->original_name }} ({{ $submission->humanReadableSize() }})
                    </div>
                </div>

                <form action="{{ route('mahasiswa.submissions.update', $submission) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div style="margin-bottom: 1.5rem;">
                        <label for="file" style="display: block; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem; font-size: 0.95rem;">
                            Unggah Berkas Baru (Kosongkan jika tidak ingin mengganti berkas)
                        </label>
                        <input
                            type="file"
                            name="file"
                            id="file"
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
                            Catatan Mahasiswa
                        </label>
                        <textarea
                            name="note"
                            id="note"
                            rows="4"
                            class="search-input"
                            style="width: 100%; resize: vertical;"
                            placeholder="Tuliskan keterangan tambahan mengenai revisi ini..."
                        >{{ old('note', $submission->note) }}</textarea>
                        @error('note')
                            <div style="color: #f87171; font-size: 0.82rem; margin-top: 0.25rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <a href="{{ route('mahasiswa.assignments.show', $submission->assignment) }}" class="btn btn-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.5rem; font-weight: 700;">
                            💾 Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
