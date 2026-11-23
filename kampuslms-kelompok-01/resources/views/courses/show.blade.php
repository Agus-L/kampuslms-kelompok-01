{{--
    Course Show View — Detail Satu Mata Kuliah
    Menampilkan informasi MK serta modul Materi dan Penugasan terkait.
--}}
@php
    $role = auth()->user()->role ?? 'guest';
    $courseIndexRoute = match($role) {
        'dosen' => route('dosen.courses.index'),
        'mahasiswa' => route('mahasiswa.courses.index'),
        default => route('admin.courses.index'),
    };
@endphp

<x-layout :title="$course->name . ' — Kampus LMS'">

    {{-- ── Breadcrumb ── --}}
    <nav class="breadcrumb">
        <a href="{{ $courseIndexRoute }}" class="breadcrumb-link">Mata Kuliah</a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-current">{{ $course->name }}</span>
    </nav>

    {{-- ── Flash message ── --}}
    @if (session('success'))
        <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #10b981; padding: 0.85rem 1.25rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
            ✓ {{ session('success') }}
        </div>
    @endif

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
            {{-- Tombol Aksi Admin --}}
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
            @can('manageEnrollment', $course)
                <div style="margin-top: 0.35rem;">
                    <a href="#mahasiswa-terdaftar" style="font-size: 0.78rem; color: var(--primary-light); font-weight: 600; text-decoration: underline;">
                        Kelola Mahasiswa ↓
                    </a>
                </div>
            @endcan
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
            {{ $course->description ?? 'Tidak ada deskripsi untuk mata kuliah ini.' }}
        </p>
    </div>

    {{-- ── Tombol Rekap Nilai — Poin 7 Milestone M3 ── --}}
    @if($role === 'dosen' || $role === 'mahasiswa')
    <div style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.08)); border: 1px solid rgba(99, 102, 241, 0.25); border-radius: 1rem; padding: 1.25rem 1.5rem; margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main, #f8fafc); margin: 0 0 0.25rem 0;">
                📊 Rekapitulasi Nilai
            </h2>
            <p style="font-size: 0.85rem; color: var(--text-muted, #94a3b8); margin: 0;">
                @if($role === 'dosen')
                    Lihat buku nilai lengkap (gradebook) seluruh mahasiswa di mata kuliah ini.
                @else
                    Lihat rapor capaian belajar Anda di mata kuliah ini.
                @endif
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            {{-- Perbaikan Temuan 1 (review A09): Gunakan @can viewGradebook --}}
            @can('viewGradebook', $course)
                <a href="{{ route('dosen.courses.grades.rekap', $course) }}" class="btn" style="padding: 0.6rem 1.25rem; font-size: 0.88rem; font-weight: 700; background: #1d4ed8; color: #ffffff; border: 1px solid #1e40af; border-radius: 0.5rem; text-decoration: none; box-shadow: 0 2px 4px rgba(29, 78, 216, 0.25); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.2s ease;"
                   onmouseover="this.style.background='#1e40af'; this.style.borderColor='#1e3a8a'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 10px rgba(29, 78, 216, 0.35)';"
                   onmouseout="this.style.background='#1d4ed8'; this.style.borderColor='#1e40af'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(29, 78, 216, 0.25)';">
                    📊 Rekap Nilai Kelas →
                </a>
                <a href="{{ route('dosen.transcript.index') }}" class="btn" style="padding: 0.6rem 1.25rem; font-size: 0.88rem; font-weight: 700; background: var(--card-bg); color: var(--text-main); border: 1px solid var(--card-border); border-radius: 0.5rem; text-decoration: none; box-shadow: var(--card-shadow); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.2s ease;">
                    📋 Laporan Transkrip (PDF)
                </a>
            @endcan

            @if($role === 'mahasiswa')
                <a href="{{ route('mahasiswa.courses.grades.index', $course) }}" class="btn" style="padding: 0.6rem 1.25rem; font-size: 0.88rem; font-weight: 700; background: #1d4ed8; color: #ffffff; border: 1px solid #1e40af; border-radius: 0.5rem; text-decoration: none; box-shadow: 0 2px 4px rgba(29, 78, 216, 0.25); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.2s ease;"
                   onmouseover="this.style.background='#1e40af'; this.style.borderColor='#1e3a8a'; this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 10px rgba(29, 78, 216, 0.35)';"
                   onmouseout="this.style.background='#1d4ed8'; this.style.borderColor='#1e40af'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(29, 78, 216, 0.25)';">
                    📊 Nilai Saya →
                </a>
                <a href="{{ route('mahasiswa.transcript.index') }}" class="btn" style="padding: 0.6rem 1.25rem; font-size: 0.88rem; font-weight: 700; background: var(--card-bg); color: var(--text-main); border: 1px solid var(--card-border); border-radius: 0.5rem; text-decoration: none; box-shadow: var(--card-shadow); display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.2s ease;">
                    📄 Transkrip Nilai (PDF)
                </a>
            @endif
        </div>
    </div>
    @endif

    {{-- ── Modul Materi Perkuliahan ── --}}
    <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.5rem; margin-top: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div>
                <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--text-main); margin: 0 0 0.25rem 0;">
                    📑 Materi Kuliah
                    <span style="font-size: 0.85rem; font-weight: 600; padding: 0.15rem 0.55rem; background: rgba(99, 102, 241, 0.15); color: var(--primary-light); border-radius: 9999px;">
                        {{ $course->materials->count() }}
                    </span>
                </h2>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">
                    Bahan ajar, modul, dan referensi perkuliahan.
                </p>
            </div>
            <div>
                @if($role === 'dosen')
                <a href="{{ route('dosen.courses.materials.index', $course) }}" class="btn btn-edit" style="padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600;">
                    Kelola Semua Materi →
                </a>
                @elseif($role === 'mahasiswa')
                <a href="{{ route('mahasiswa.courses.materials.index', $course) }}" class="btn btn-edit" style="padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600;">
                    Buka Halaman Materi →
                </a>
                @endif
            </div>
        </div>

        @if ($course->materials->isEmpty())
            <div style="text-align: center; padding: 2.5rem 1rem; background: var(--bg); border: 1px dashed var(--card-border); border-radius: 0.75rem; color: var(--text-muted);">
                <div style="font-size: 2rem; margin-bottom: 0.35rem;">📂</div>
                <p style="margin: 0; font-size: 0.95rem;">Belum ada materi perkuliahan yang ditambahkan.</p>
                @if($role === 'dosen')
                <a href="{{ route('dosen.courses.materials.index', $course) }}" style="display: inline-block; margin-top: 0.75rem; color: var(--primary-light); font-weight: 600; font-size: 0.88rem;">
                    Buka halaman kelola materi untuk menambahkan →
                </a>
                @endif
            </div>
        @else
            <div style="display: grid; gap: 0.75rem;">
                @foreach ($course->materials->take(5) as $material)
                    <div style="background: var(--bg); border: 1px solid var(--card-border); border-radius: 0.65rem; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center; transition: border-color 0.2s;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span style="font-size: 1.35rem;">{{ $material->type === 'link' ? '🔗' : '📄' }}</span>
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <h3 style="font-size: 0.98rem; font-weight: 700; color: var(--text-main); margin: 0;">
                                        {{ $material->title }}
                                    </h3>
                                    <span style="font-size: 0.7rem; padding: 0.1rem 0.45rem; border-radius: 9999px; background: rgba(99, 102, 241, 0.15); color: #818cf8; font-weight: 600;">
                                        {{ strtoupper($material->type) }}
                                    </span>
                                </div>
                                <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0.2rem 0 0 0;">
                                    {{ Str::limit($material->description, 90) ?: 'Tidak ada deskripsi tambahan.' }}
                                </p>
                            </div>
                        </div>
                        <div>
                            @if ($role === 'dosen')
                                <a href="{{ route('dosen.materials.show', $material) }}" class="btn btn-edit" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                                    Lihat Detail →
                                </a>
                            @elseif ($role === 'mahasiswa')
                                <a href="{{ route('mahasiswa.materials.show', $material) }}" class="btn btn-edit" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                                    Buka Materi →
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($course->materials->count() > 5)
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="{{ route($role . '.courses.materials.index', $course) }}" style="color: var(--primary-light); font-size: 0.88rem; font-weight: 600;">
                        Lihat {{ $course->materials->count() - 5 }} materi lainnya →
                    </a>
                </div>
            @endif
        @endif
    </div>

    {{-- ── Modul Penugasan ── --}}
    @php
        $assignmentsList = $role === 'mahasiswa'
            ? $course->assignments->where('status', 'published')
            : $course->assignments;
    @endphp
    <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 1rem; padding: 1.5rem; margin-top: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div>
                <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--text-main); margin: 0 0 0.25rem 0;">
                    📝 Daftar Penugasan
                    <span style="font-size: 0.85rem; font-weight: 600; padding: 0.15rem 0.55rem; background: rgba(99, 102, 241, 0.15); color: var(--primary-light); border-radius: 9999px;">
                        {{ $assignmentsList->count() }}
                    </span>
                </h2>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">
                    Tugas kuliah, latihan, dan batas waktu pengumpulan.
                </p>
            </div>
            <div>
                @if($role === 'dosen')
                <a href="{{ route('dosen.courses.assignments.index', $course) }}" class="btn btn-edit" style="padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600;">
                    Kelola Semua Tugas →
                </a>
                @elseif($role === 'mahasiswa')
                <a href="{{ route('mahasiswa.courses.assignments.index', $course) }}" class="btn btn-edit" style="padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600;">
                    Buka Halaman Tugas →
                </a>
                @endif
            </div>
        </div>

        @if ($assignmentsList->isEmpty())
            <div style="text-align: center; padding: 2.5rem 1rem; background: var(--bg); border: 1px dashed var(--card-border); border-radius: 0.75rem; color: var(--text-muted);">
                <div style="font-size: 2rem; margin-bottom: 0.35rem;">📋</div>
                <p style="margin: 0; font-size: 0.95rem;">Belum ada penugasan yang diberikan untuk kelas ini.</p>
                @if($role === 'dosen')
                <a href="{{ route('dosen.courses.assignments.index', $course) }}" style="display: inline-block; margin-top: 0.75rem; color: var(--primary-light); font-weight: 600; font-size: 0.88rem;">
                    Buka halaman kelola tugas untuk membuat tugas →
                </a>
                @endif
            </div>
        @else
            <div style="display: grid; gap: 0.75rem;">
                @foreach ($assignmentsList->take(5) as $assignment)
                    <div style="background: var(--bg); border: 1px solid var(--card-border); border-radius: 0.65rem; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                <h3 style="font-size: 0.98rem; font-weight: 700; color: var(--text-main); margin: 0;">
                                    {{ $assignment->title }}
                                </h3>
                                @if($role !== 'mahasiswa')
                                <span style="font-size: 0.7rem; padding: 0.1rem 0.45rem; border-radius: 9999px; background: {{ $assignment->status === 'published' ? 'rgba(34, 197, 94, 0.15)' : 'rgba(234, 179, 8, 0.15)' }}; color: {{ $assignment->status === 'published' ? '#22c55e' : '#eab308' }}; font-weight: 600;">
                                    {{ ucfirst($assignment->status) }}
                                </span>
                                @endif
                            </div>
                            <div style="font-size: 0.82rem; color: var(--text-muted); display: flex; gap: 1rem; align-items: center;">
                                <span>📅 Tenggat: <strong>{{ $assignment->due_at ? $assignment->due_at->format('d M Y, H:i') . ' WITA' : 'Tanpa batas waktu' }}</strong></span>
                                <span>🎯 Nilai Max: <strong>{{ $assignment->max_score }}</strong></span>
                            </div>
                        </div>
                        <div>
                            @if ($role === 'dosen')
                                <a href="{{ route('dosen.assignments.show', $assignment) }}" class="btn btn-edit" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                                    Lihat Detail →
                                </a>
                            @elseif ($role === 'mahasiswa')
                                <a href="{{ route('mahasiswa.assignments.show', $assignment) }}" class="btn btn-edit" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                                    Buka Tugas →
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($assignmentsList->count() > 5)
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="{{ route($role . '.courses.assignments.index', $course) }}" style="color: var(--primary-light); font-size: 0.88rem; font-weight: 600;">
                        Lihat {{ $assignmentsList->count() - 5 }} tugas lainnya →
                    </a>
                </div>
            @endif
        @endif
    </div>

    {{-- ── Modul Mahasiswa Terdaftar (Peserta Kelas) ── --}}
    @can('manageEnrollment', $course)
    <div id="mahasiswa-terdaftar" style="margin-top: 1.5rem;">
        <livewire:courses.enrollment-manager :course="$course" />
    </div>
    @endcan

    {{-- ── Tombol Kembali ── --}}
    <div style="margin-top: 2rem;">
        <a href="{{ $courseIndexRoute }}" class="back-link">
            <span class="back-arrow">←</span>
            Kembali ke Daftar Mata Kuliah
        </a>
    </div>

</x-layout>
