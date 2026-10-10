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
            <form action="{{ route('dosen.materials.update', $material) }}" method="POST" novalidate>
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
                        <option value="file" {{ old('type', $material->type) === 'file' ? 'selected' : '' }}>Berkas Dokumen</option>
                        <option value="link" {{ old('type', $material->type) === 'link' ? 'selected' : '' }}>Tautan Eksternal</option>
                    </select>
                    @error('type')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- URL Eksternal --}}
                <div class="form-group" style="margin-bottom: 1.25rem;">
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
</x-layout>
