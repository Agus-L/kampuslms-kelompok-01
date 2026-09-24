{{--
    Course Edit View — Form Edit Mata Kuliah
    Route: GET /courses/{course}/edit → courses.edit
    Controller: CourseController@edit

    View ini menerima:
    - $course    : Model Course yang akan diedit
    - $lecturers : Collection berisi User dengan role dosen
--}}

<x-layout :title="'Edit ' . $course->name . ' — Kampus LMS'">

    <div class="form-container">
        {{-- ── Breadcrumb ── --}}
        <nav class="breadcrumb">
            <a href="{{ route('courses.index') }}" class="breadcrumb-link">Mata Kuliah</a>
            <span class="breadcrumb-separator">›</span>
            <a href="{{ route('courses.show', $course) }}" class="breadcrumb-link">{{ $course->name }}</a>
            <span class="breadcrumb-separator">›</span>
            <span class="breadcrumb-current">Edit</span>
        </nav>

        {{-- ── Header ── --}}
        <div class="page-header">
            <h1 class="page-title">Edit Mata Kuliah</h1>
            <p class="page-subtitle">
                Perbarui informasi untuk <strong style="color: var(--primary-light)">{{ $course->name }}</strong>.
            </p>
        </div>

        {{-- ── Form ── --}}
        <div class="form-card">
            <form action="{{ route('courses.update', $course) }}" method="POST" novalidate>
            @csrf
            @method('PUT')

            {{-- ── Seksi 1: Identitas ── --}}
            <div class="form-section-title">📋 Identitas Mata Kuliah</div>

            {{-- Kode MK + SKS dalam 1 baris --}}
            <div class="form-row">
                {{-- Kode MK --}}
                <div class="form-group">
                    <label for="code" class="form-label">
                        Kode MK <span class="required">*</span>
                    </label>
                    <input type="text" id="code" name="code"
                           class="form-input @error('code') form-input-error @enderror"
                           value="{{ old('code', $course->code) }}"
                           placeholder="Cth: IF2514024"
                           maxlength="20"
                           required>
                    @error('code')
                        <p class="form-error">{{ $message }}</p>
                    @else
                        <p class="form-hint">Maksimal 20 karakter, harus unik.</p>
                    @enderror
                </div>

                {{-- SKS --}}
                <div class="form-group">
                    <label for="sks" class="form-label">
                        Bobot SKS <span class="required">*</span>
                    </label>
                    <input type="number" id="sks" name="sks"
                           class="form-input @error('sks') form-input-error @enderror"
                           value="{{ old('sks', $course->sks) }}"
                           min="1" max="6"
                           placeholder="1 – 6"
                           required>
                    @error('sks')
                        <p class="form-error">{{ $message }}</p>
                    @else
                        <p class="form-hint">Antara 1 hingga 6 SKS.</p>
                    @enderror
                </div>
            </div>

            {{-- Nama MK --}}
            <div class="form-group">
                <label for="name" class="form-label">
                    Nama Mata Kuliah <span class="required">*</span>
                </label>
                <input type="text" id="name" name="name"
                       class="form-input @error('name') form-input-error @enderror"
                       value="{{ old('name', $course->name) }}"
                       placeholder="Contoh: Pemrograman Web"
                       maxlength="255"
                       required>
                @error('name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div class="form-group">
                <label for="description" class="form-label">
                    Deskripsi <span style="font-weight: normal; color: var(--text-muted);">(Opsional)</span>
                </label>
                <textarea id="description" name="description" rows="4"
                          class="form-textarea @error('description') form-input-error @enderror"
                          placeholder="Jelaskan materi, capaian pembelajaran, dan tujuan mata kuliah...">{{ old('description', $course->description) }}</textarea>
                @error('description')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-divider"></div>

            {{-- ── Seksi 2: Pengajar & Status ── --}}
            <div class="form-section-title">👤 Pengajar & Status</div>

            {{-- Dosen Pengampu + Status dalam 1 baris --}}
            <div class="form-row">
                {{-- Dosen Pengampu --}}
                <div class="form-group">
                    <label for="lecturer_id" class="form-label">
                        Dosen Pengampu <span class="required">*</span>
                    </label>
                    <select id="lecturer_id" name="lecturer_id"
                            class="form-select @error('lecturer_id') form-input-error @enderror"
                            required>
                        <option value="">— Pilih Dosen —</option>
                        @foreach ($lecturers as $lecturer)
                            <option value="{{ $lecturer->id }}"
                                    {{ old('lecturer_id', $course->lecturer_id) == $lecturer->id ? 'selected' : '' }}>
                                {{ $lecturer->name }}
                                @if ($lecturer->nim_nip)
                                    ({{ $lecturer->nim_nip }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('lecturer_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status --}}
                <div class="form-group">
                    <label for="status" class="form-label">
                        Status <span class="required">*</span>
                    </label>
                    <select id="status" name="status"
                            class="form-select @error('status') form-input-error @enderror"
                            required>
                        <option value="draft"    {{ old('status', $course->status) == 'draft'    ? 'selected' : '' }}>📝 Draft</option>
                        <option value="active"   {{ old('status', $course->status) == 'active'   ? 'selected' : '' }}>✅ Active</option>
                        <option value="archived" {{ old('status', $course->status) == 'archived' ? 'selected' : '' }}>🗃️ Archived</option>
                    </select>
                    @error('status')
                        <p class="form-error">{{ $message }}</p>
                    @else
                        <p class="form-hint">Draft tidak terlihat mahasiswa.</p>
                    @enderror
                </div>
            </div>

            {{-- ── Tombol Submit ── --}}
            <div class="form-actions">
                <button type="submit" class="btn btn-create">
                    💾 Simpan Perubahan
                </button>
                <a href="{{ route('courses.show', $course) }}" class="btn btn-cancel">
                    ✕ Batal
                </a>
            </div>
            </form>
        </div>
    </div>

</x-layout>
