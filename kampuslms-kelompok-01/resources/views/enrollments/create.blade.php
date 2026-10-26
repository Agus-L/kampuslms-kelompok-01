<x-layout :title="'Daftarkan Mahasiswa — ' . $course->name">
    <div class="page-container">
        {{-- Breadcrumb --}}
        @php
            $rolePrefix = (auth()->check() && auth()->user()->role === 'dosen') ? 'dosen' : 'admin';
            $coursesRoute = $rolePrefix === 'dosen' ? route('dosen.courses.index') : route('admin.courses.index');
            $courseShowRoute = $rolePrefix === 'dosen' ? route('dosen.courses.show', $course) : route('admin.courses.show', $course);
        @endphp
        <nav class="breadcrumb">
            <a href="{{ $coursesRoute }}" class="breadcrumb-link">Mata Kuliah</a>
            <span class="breadcrumb-separator">›</span>
            <a href="{{ $courseShowRoute }}" class="breadcrumb-link">{{ $course->name }}</a>
            <span class="breadcrumb-separator">›</span>
            <span class="breadcrumb-current">Daftarkan Mahasiswa</span>
        </nav>

        {{-- Page Header --}}
        <div class="page-header" style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 1rem; padding: 1.75rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <span style="font-size: 0.8rem; font-weight: 700; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 0.25rem 0.6rem; border-radius: 0.25rem;">
                        {{ $course->code }} • {{ $course->sks }} SKS
                    </span>
                    <h1 class="page-title" style="margin-top: 0.5rem; margin-bottom: 0.25rem;">
                        ➕ Daftarkan Mahasiswa ke Mata Kuliah
                    </h1>
                    <p class="page-subtitle" style="margin: 0; font-size: 1.05rem; font-weight: 600; color: var(--text-main);">
                        {{ $course->name }} (Pengampu: {{ $course->lecturer->name ?? 'Belum ditentukan' }})
                    </p>
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <a href="{{ $courseShowRoute }}" class="btn btn-secondary">
                        ← Kembali ke Detail MK
                    </a>
                </div>
            </div>
        </div>

        {{-- Komponen Livewire: Hanya Berisi Mahasiswa yang Belum Terdaftar --}}
        <livewire:courses.enroll-student-list :course="$course" />

        {{-- Tombol Kembali di Bawah --}}
        <div style="margin-top: 1.5rem;">
            <a href="{{ $courseShowRoute }}" class="back-link">
                <span class="back-arrow">←</span>
                Kembali ke Detail Mata Kuliah
            </a>
        </div>
    </div>
</x-layout>
