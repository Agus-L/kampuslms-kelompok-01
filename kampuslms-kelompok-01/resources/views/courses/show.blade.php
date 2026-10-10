{{--
    Course Show View — Detail Satu Mata Kuliah
    Route: GET /courses/{course} → courses.show
    Controller: CourseController@show

    View ini menerima variabel $course (model Course dengan relasi
    lecturer dan students yang sudah di-eager-load).
--}}

<x-layout :title="$course->name . ' — Kampus LMS'">

    {{-- ── Breadcrumb ── --}}
    <nav class="breadcrumb">
        <a href="{{ route('admin.courses.index') }}" class="breadcrumb-link">Mata Kuliah</a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-current">{{ $course->name }}</span>
    </nav>

    {{-- ── Header Detail ── --}}
    <div class="detail-header">
        <div class="detail-header-top">
            <div>
                <span class="detail-badge">
                    📖 {{ $course->code }}
                </span>
                <span class="course-status course-status-{{ $course->status }}">
                    {{ ucfirst($course->status) }}
                </span>
            </div>
            {{-- Tombol Aksi --}}
            <div class="detail-actions">
                @can('update', $course)
                <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-edit">
                    ✏️ Edit
                </a>
                @endcan
                @can('delete', $course)
                <button type="button" class="btn btn-delete"
                        data-modal-target="delete-course-{{ $course->id }}">
                    🗑️ Hapus
                </button>
                <x-delete-modal
                    id="delete-course-{{ $course->id }}"
                    :action="route('admin.courses.destroy', $course)"
                    :itemName="$course->name"
                />
                @endcan
            </div>
        </div>
        <h1 class="detail-title">{{ $course->name }}</h1>
        <p class="detail-dosen">👤 {{ $course->lecturer->name ?? 'Belum ditentukan' }}</p>
    </div>

    {{-- ── Info Cards ── --}}
    <div class="info-grid">
        <div class="info-card">
            <div class="info-card-icon">📖</div>
            <div class="info-card-label">Kode MK</div>
            <div class="info-card-value">{{ $course->code }}</div>
        </div>
        <div class="info-card">
            <div class="info-card-icon">⏱</div>
            <div class="info-card-label">Bobot SKS</div>
            <div class="info-card-value">{{ $course->sks }} SKS</div>
        </div>
        <div class="info-card">
            <div class="info-card-icon">👥</div>
            <div class="info-card-label">Mahasiswa Terdaftar</div>
            <div class="info-card-value">{{ $course->students->count() }} Orang</div>
        </div>
        <div class="info-card">
            <div class="info-card-icon">📋</div>
            <div class="info-card-label">Status</div>
            <div class="info-card-value">{{ ucfirst($course->status) }}</div>
        </div>
    </div>

    {{-- ── Deskripsi Mata Kuliah ── --}}
    <div class="description-card">
        <h2 class="description-title">📝 Deskripsi Mata Kuliah</h2>
        <p class="description-text">
            {{ $course->description }}
        </p>
    </div>

    {{-- ── Tombol Kembali ── --}}
    <a href="{{ route('admin.courses.index') }}" class="back-link">
        <span class="back-arrow">←</span>
        Kembali ke Daftar Mata Kuliah
    </a>

</x-layout>
