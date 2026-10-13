<x-layout :title="'Buat Tugas Baru — ' . $course->name">
    <div class="form-container">
        {{-- Breadcrumb --}}
        <nav class="breadcrumb">
            <a href="{{ route('dosen.courses.index') }}" class="breadcrumb-link">Mata Kuliah</a>
            <span class="breadcrumb-separator">›</span>
            <a href="{{ route('dosen.courses.assignments.index', $course) }}" class="breadcrumb-link">Tugas</a>
            <span class="breadcrumb-separator">›</span>
            <span class="breadcrumb-current">Buat Baru</span>
        </nav>

        {{-- Header --}}
        <div class="page-header">
            <h1 class="page-title">Buat Tugas Baru</h1>
            <p class="page-subtitle">Mata Kuliah: <strong>{{ $course->name }} ({{ $course->code }})</strong></p>
        </div>

        {{-- Form Card --}}
        <div class="form-card">
            <form action="{{ route('dosen.courses.assignments.store', $course) }}" method="POST" novalidate>
                @csrf

                <div class="form-section-title">📝 Detail Penugasan</div>

                {{-- Judul Tugas --}}
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="title" class="form-label">Judul Tugas <span class="required">*</span></label>
                    <input type="text" id="title" name="title"
                           class="form-input @error('title') form-input-error @enderror"
                           value="{{ old('title') }}" placeholder="Cth: Tugas 1 - Perancangan Skema Database" required>
                    @error('title')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Instruksi & Deskripsi Tugas (Opsional) --}}
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="instructions" class="form-label">Instruksi / Deskripsi Tugas <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: normal;">(Opsional)</span></label>
                    <textarea id="instructions" name="instructions" rows="5"
                              class="form-input @error('instructions') form-input-error @enderror"
                              placeholder="Tuliskan instruksi atau deskripsi pengerjaan tugas...">{{ old('instructions') }}</textarea>
                    @error('instructions')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    {{-- Deadline (due_at) --}}
                    <div class="form-group">
                        <label for="due_at" class="form-label">Tenggat Waktu (Deadline) <span class="required">*</span></label>
                        <input type="datetime-local" id="due_at" name="due_at"
                               class="form-input @error('due_at') form-input-error @enderror"
                               value="{{ old('due_at') }}" required>
                        @error('due_at')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Skor Maksimal --}}
                    <div class="form-group">
                        <label for="max_score" class="form-label">Skor Maksimal <span class="required">*</span></label>
                        <input type="number" id="max_score" name="max_score"
                               class="form-input @error('max_score') form-input-error @enderror"
                               value="{{ old('max_score', 100) }}" min="1" max="100" required>
                        @error('max_score')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    {{-- Status --}}
                    <div class="form-group">
                        <label for="status" class="form-label">Status Penugasan</label>
                        <select id="status" name="status" class="form-input">
                            <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Dipublikasikan (Mahasiswa Bisa Lihat)</option>
                            <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft (Hanya Dosen)</option>
                            <option value="closed" {{ old('status') === 'closed' ? 'selected' : '' }}>Ditutup</option>
                        </select>
                    </div>

                    {{-- Pengumpulan Terlambat --}}
                    <div class="form-group" style="display: flex; align-items: center; padding-top: 1.5rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-main, #f8fafc);">
                            <input type="checkbox" name="allow_late" value="1" {{ old('allow_late') ? 'checked' : '' }}>
                            <span>Izinkan Pengumpulan Terlambat (Late Submission)</span>
                        </label>
                    </div>
                </div>

                {{-- Actions --}}
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <a href="{{ route('dosen.courses.assignments.index', $course) }}" class="btn btn-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        💾 Terbitkan Tugas
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>
