<x-layout :title="'Tambah Materi — ' . $course->name">
    <div class="form-container">
        {{-- Breadcrumb --}}
        <nav class="breadcrumb">
            <a href="{{ route('dosen.courses.index') }}" class="breadcrumb-link">Mata Kuliah</a>
            <span class="breadcrumb-separator">›</span>
            <a href="{{ route('dosen.courses.materials.index', $course) }}" class="breadcrumb-link">Materi</a>
            <span class="breadcrumb-separator">›</span>
            <span class="breadcrumb-current">Tambah Baru</span>
        </nav>

        {{-- Header --}}
        <div class="page-header">
            <h1 class="page-title">Tambah Materi Kuliah</h1>
            <p class="page-subtitle">Mata Kuliah: <strong>{{ $course->name }} ({{ $course->code }})</strong></p>
        </div>

        {{-- Form Card --}}
        <div class="form-card">
            {{-- enctype="multipart/form-data" WAJIB agar berkas dapat dikirim ke server --}}
            <form action="{{ route('dosen.courses.materials.store', $course) }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf

                <div class="form-section-title">📄 Informasi Materi</div>

                {{-- Judul --}}
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="title" class="form-label">Judul Materi <span class="required">*</span></label>
                    <input type="text" id="title" name="title"
                           class="form-input @error('title') form-input-error @enderror"
                           value="{{ old('title') }}" placeholder="Cth: Pertemuan 1 - Pengenalan Laravel & MVC" required>
                    @error('title')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Deskripsi (Opsional) --}}
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="description" class="form-label">Deskripsi / Catatan Pembelajaran <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: normal;">(Opsional)</span></label>
                    <textarea id="description" name="description" rows="4"
                              class="form-input @error('description') form-input-error @enderror"
                              placeholder="Tuliskan rangkuman materi atau instruksi bacaan...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tipe Materi --}}
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="type" class="form-label">Tipe Materi <span class="required">*</span></label>
                    <select id="type" name="type" class="form-input @error('type') form-input-error @enderror" required>
                        <option value="file" {{ old('type', 'file') === 'file' ? 'selected' : '' }}>📁 Unggah Berkas (PDF, PPT, DOCX)</option>
                        <option value="link" {{ old('type') === 'link' ? 'selected' : '' }}>🔗 Tautan Eksternal (URL Website / Video)</option>
                    </select>
                    @error('type')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Upload Berkas (jika tipe file) --}}
                <div id="file-upload-section" class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="file" class="form-label">Unggah Berkas Dokumen <span class="required">*</span></label>
                    <input type="file" id="file" name="file"
                           class="form-input @error('file') form-input-error @enderror"
                           accept=".pdf,.ppt,.pptx,.doc,.docx"
                           style="padding: 0.6rem;">
                    <p class="form-hint" style="font-size: 0.8rem; color: var(--text-muted, #94a3b8); margin-top: 0.35rem;">
                        Format yang diterima: PDF, PPT, PPTX, DOC, DOCX. Ukuran maksimal: <strong>20 MB</strong>.
                    </p>
                    @error('file')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- URL Eksternal (jika tipe link) --}}
                <div id="link-section" class="form-group" style="margin-bottom: 1.25rem; display: none;">
                    <label for="external_url" class="form-label">URL Tautan Eksternal <span class="required">*</span></label>
                    <input type="url" id="external_url" name="external_url"
                           class="form-input @error('external_url') form-input-error @enderror"
                           value="{{ old('external_url') }}" placeholder="https://laravel.com/docs/12.x">
                    @error('external_url')
                        <p class="form-error">{{ $message }}</p>
                    @else
                        <p class="form-hint" style="font-size: 0.8rem; color: var(--text-muted, #94a3b8);">Wajib diisi jika tipe yang dipilih adalah link.</p>
                    @enderror
                </div>

                {{-- Actions --}}
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <a href="{{ route('dosen.courses.materials.index', $course) }}" class="btn btn-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        💾 Simpan Materi
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
            toggleSections(); // Jalankan saat halaman dimuat (untuk handle old() value)
        });
    </script>
</x-layout>
