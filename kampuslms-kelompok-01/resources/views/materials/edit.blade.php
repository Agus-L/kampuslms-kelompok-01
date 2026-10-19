<x-layout :title="'Edit Materi — ' . $material->title">
    <div class="form-container">
        {{-- Breadcrumb --}}
        <nav class="breadcrumb">
            <a href="{{ route('dosen.courses.index') }}" class="breadcrumb-link">Mata Kuliah</a>
            <span class="breadcrumb-separator">›</span>
            <a href="{{ route('dosen.courses.materials.index', $material->course) }}" class="breadcrumb-link">Materi</a>
            <span class="breadcrumb-separator">›</span>
            <span class="breadcrumb-current">Edit: {{ $material->title }}</span>
        </nav>

        {{-- Header --}}
        <div class="page-header">
            <h1 class="page-title">Edit Materi Kuliah</h1>
            <p class="page-subtitle">Mata Kuliah: <strong>{{ $material->course->name }}</strong></p>
        </div>

        {{-- Form Card --}}
        <div class="form-card">
            {{-- enctype="multipart/form-data" WAJIB agar berkas pengganti dapat dikirim --}}
            <form action="{{ route('dosen.materials.update', $material) }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')

                <div class="form-section-title">✏️ Perbarui Materi</div>

                {{-- Judul --}}
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="title" class="form-label">Judul Materi <span class="required">*</span></label>
                    <input type="text" id="title" name="title"
                           class="form-input @error('title') form-input-error @enderror"
                           value="{{ old('title', $material->title) }}" required>
                    @error('title')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Deskripsi (Opsional) --}}
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="description" class="form-label">Deskripsi <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: normal;">(Opsional)</span></label>
                    <textarea id="description" name="description" rows="4"
                              class="form-input @error('description') form-input-error @enderror">{{ old('description', $material->description) }}</textarea>
                    @error('description')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tipe Materi --}}
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="type" class="form-label">Tipe Materi <span class="required">*</span></label>
                    <select id="type" name="type" class="form-input @error('type') form-input-error @enderror" required>
                        <option value="file" {{ old('type', $material->type) === 'file' ? 'selected' : '' }}>📁 Berkas Dokumen</option>
                        <option value="link" {{ old('type', $material->type) === 'link' ? 'selected' : '' }}>🔗 Tautan Eksternal</option>
                    </select>
                    @error('type')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Berkas Saat Ini & Upload Pengganti (jika tipe file) --}}
                <div id="file-upload-section" class="form-group" style="margin-bottom: 1.25rem;">
                    @if ($material->type === 'file' && $material->file_path)
                        <div style="padding: 0.85rem 1rem; background: rgba(99, 102, 241, 0.08); border: 1px solid rgba(99, 102, 241, 0.25); border-radius: 0.5rem; margin-bottom: 0.75rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <span style="font-size: 0.8rem; color: var(--text-muted, #94a3b8); display: block; margin-bottom: 0.2rem;">Berkas saat ini:</span>
                                    <span style="color: var(--text-main, #f8fafc); font-family: monospace; font-weight: 600;">
                                        📄 {{ $material->original_name }}
                                    </span>
                                    <span style="font-size: 0.8rem; color: var(--text-muted, #94a3b8); margin-left: 0.5rem;">
                                        ({{ number_format($material->file_size / 1024, 1) }} KB)
                                    </span>
                                </div>
                                <a href="{{ route('dosen.materials.download', $material) }}" class="btn btn-secondary" style="font-size: 0.8rem; padding: 0.3rem 0.7rem;">
                                    ⬇️ Unduh
                                </a>
                            </div>
                        </div>
                    @endif

                    <label for="file" class="form-label">
                        {{ $material->type === 'file' && $material->file_path ? 'Ganti Berkas (Opsional)' : 'Unggah Berkas Dokumen' }}
                    </label>
                    <input type="file" id="file" name="file"
                           class="form-input @error('file') form-input-error @enderror"
                           accept=".pdf,.ppt,.pptx,.doc,.docx"
                           style="padding: 0.6rem;">
                    <p class="form-hint" style="font-size: 0.8rem; color: var(--text-muted, #94a3b8); margin-top: 0.35rem;">
                        Format: PDF, PPT, PPTX, DOC, DOCX. Maks: <strong>20 MB</strong>.
                        {{ $material->file_path ? 'Kosongkan jika tidak ingin mengganti berkas.' : '' }}
                    </p>
                    @error('file')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- URL Eksternal --}}
                <div id="link-section" class="form-group" style="margin-bottom: 1.25rem; display: none;">
                    <label for="external_url" class="form-label">URL Tautan Eksternal</label>
                    <input type="url" id="external_url" name="external_url"
                           class="form-input @error('external_url') form-input-error @enderror"
                           value="{{ old('external_url', $material->external_url) }}">
                    @error('external_url')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Actions --}}
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <a href="{{ route('dosen.courses.materials.index', $material->course) }}" class="btn btn-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        💾 Perbarui Materi
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Toggle visibility antara upload file dan input URL berdasarkan tipe --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const typeSelect   = document.getElementById('type');
            const fileSection  = document.getElementById('file-upload-section');
            const linkSection  = document.getElementById('link-section');

            function toggleSections() {
                if (typeSelect.value === 'file') {
                    fileSection.style.display = 'block';
                    linkSection.style.display = 'none';
                } else {
                    fileSection.style.display = 'none';
                    linkSection.style.display = 'block';
                }
            }

            typeSelect.addEventListener('change', toggleSections);
            toggleSections();
        });
    </script>
</x-layout>
