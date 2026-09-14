{{--
    Course Edit View — Form Edit Mata Kuliah
    Route: GET /courses/{course}/edit → courses.edit
    Controller: CourseController@edit

    View ini menerima:
    - $course    : Model Course yang akan diedit
    - $lecturers : Collection berisi User dengan role dosen
--}}

<x-layout :title="'Edit ' . $course->name . ' — Kampus LMS'">

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
        <p class="page-subtitle">Perbarui informasi mata kuliah <strong>{{ $course->name }}</strong>.</p>
    </div>

    {{-- ── Form ── --}}
    <div class="form-card">
        <form action="{{ route('courses.update', $course) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Kode MK --}}
            <div class="form-group">
                <label for="code" class="form-label">Kode Mata Kuliah</label>
                <input type="text" id="code" name="code"
                       class="form-input @error('code') form-input-error @enderror"
                       value="{{ old('code', $course->code) }}"
                       placeholder="Contoh: IF2514024"
                       required>
                @error('code')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nama MK --}}
            <div class="form-group">
                <label for="name" class="form-label">Nama Mata Kuliah</label>
                <input type="text" id="name" name="name"
                       class="form-input @error('name') form-input-error @enderror"
                       value="{{ old('name', $course->name) }}"
                       placeholder="Contoh: Pemrograman Web"
                       required>
                @error('name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div class="form-group">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea id="description" name="description" rows="4"
                          class="form-textarea @error('description') form-input-error @enderror"
                          placeholder="Jelaskan isi dan tujuan mata kuliah..."
                          required>{{ old('description', $course->description) }}</textarea>
                @error('description')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- SKS --}}
            <div class="form-group">
                <label for="sks" class="form-label">Jumlah SKS</label>
                <input type="number" id="sks" name="sks"
                       class="form-input @error('sks') form-input-error @enderror"
                       value="{{ old('sks', $course->sks) }}"
                       min="1" max="8"
                       required>
                @error('sks')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Dosen Pengampu --}}
            <div class="form-group">
                <label for="lecturer_id" class="form-label">Dosen Pengampu</label>
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
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status"
                        class="form-select @error('status') form-input-error @enderror"
                        required>
                    <option value="draft"    {{ old('status', $course->status) == 'draft'    ? 'selected' : '' }}>Draft</option>
                    <option value="active"   {{ old('status', $course->status) == 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="archived" {{ old('status', $course->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
                @error('status')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tombol Submit --}}
            <div class="form-actions">
                <button type="submit" class="btn btn-create">
                    💾 Simpan Perubahan
                </button>
                <a href="{{ route('courses.show', $course) }}" class="btn btn-cancel">
                    Batal
                </a>
            </div>
        </form>
    </div>

</x-layout>
