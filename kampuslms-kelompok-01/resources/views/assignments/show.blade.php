<x-layout :title="'Detail Tugas — ' . $assignment->title">
    <div class="page-container">
        {{-- Breadcrumb --}}
        <nav class="breadcrumb">
            @if(auth()->user()->role === 'dosen')
                <a href="{{ route('dosen.courses.index') }}" class="breadcrumb-link">Mata Kuliah</a>
                <span class="breadcrumb-separator">›</span>
                <a href="{{ route('dosen.courses.assignments.index', $assignment->course) }}" class="breadcrumb-link">Tugas</a>
            @else
                <a href="{{ route('mahasiswa.courses.index') }}" class="breadcrumb-link">Mata Kuliah</a>
                <span class="breadcrumb-separator">›</span>
                <a href="{{ route('mahasiswa.courses.assignments.index', $assignment->course) }}" class="breadcrumb-link">Tugas</a>
            @endif
            <span class="breadcrumb-separator">›</span>
            <span class="breadcrumb-current">{{ $assignment->title }}</span>
        </nav>

        {{-- Detail Header --}}
        <div class="detail-header" style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 1rem; padding: 2rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div>
                    <span style="font-size: 0.8rem; font-weight: 700; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 0.25rem 0.6rem; border-radius: 0.25rem;">
                        {{ $assignment->course->code }} — {{ $assignment->course->name }}
                    </span>
                    <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main, #f8fafc); margin: 0.5rem 0 0.25rem 0;">
                        {{ $assignment->title }}
                    </h1>
                    <div style="display: flex; gap: 1rem; font-size: 0.85rem; color: var(--text-muted, #94a3b8); margin-top: 0.5rem;">
                        <span>⏰ Deadline: <strong style="color: #f87171;">{{ $assignment->due_at->format('d F Y, H:i') }} WITA</strong></span>
                        <span>•</span>
                        <span>🎯 Skor Maks: <strong>{{ $assignment->max_score }}</strong></span>
                        <span>•</span>
                        <span>Status: <strong>{{ ucfirst($assignment->status) }}</strong></span>
                    </div>
                </div>

                @can('update', $assignment)
                <div style="display: flex; gap: 0.5rem;">
                    <a href="{{ route('dosen.assignments.edit', $assignment) }}" class="btn btn-edit">
                        ✏️ Edit Tugas
                    </a>
                </div>
                @endcan
            </div>

            {{-- Instruksi Tugas --}}
            <div style="padding-top: 1rem; border-top: 1px solid var(--card-border, #334155); margin-top: 1rem;">
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-main, #f8fafc); margin-bottom: 0.5rem;">Instruksi Pengerjaan</h3>
                <div style="color: var(--text-muted, #94a3b8); line-height: 1.6; white-space: pre-line;">
                    {{ $assignment->instructions ?: 'Tidak ada instruksi khusus untuk penugasan ini.' }}
                </div>
            </div>

            {{-- Status Pengumpulan Mahasiswa (Jika Mahasiswa) --}}
            @if(auth()->user()->role === 'mahasiswa')
                <div style="margin-top: 1.5rem; padding: 1.25rem; background: rgba(255, 255, 255, 0.03); border-radius: 0.75rem; border: 1px solid var(--card-border, #334155);">
                    <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-main, #f8fafc); margin: 0 0 0.5rem 0;">Status Pengumpulan Anda:</h4>
                    @php
                        $mySubmission = $assignment->submissions->first();
                    @endphp
                    @if($mySubmission)
                        <div style="color: #10b981; font-weight: 600; margin-bottom: 0.35rem;">
                            ✓ Sudah Dikumpulkan pada {{ $mySubmission->submitted_at ? $mySubmission->submitted_at->format('d M Y, H:i') : '-' }}
                        </div>
                        @if($mySubmission->grade)
                            <div style="color: var(--primary-light, #818cf8); font-size: 0.9rem;">
                                Nilai: <strong>{{ $mySubmission->grade->score }} / {{ $assignment->max_score }}</strong>
                                @if($mySubmission->grade->feedback)
                                    <div style="font-size: 0.85rem; color: var(--text-muted, #94a3b8); margin-top: 0.25rem;">
                                        Catatan Dosen: "{{ $mySubmission->grade->feedback }}"
                                    </div>
                                @endif
                            </div>
                        @else
                            <div style="font-size: 0.85rem; color: #f59e0b;">⏳ Menunggu penilaian dari dosen pengampu.</div>
                        @endif
                    @else
                        <div style="color: #f87171; font-weight: 600; margin-bottom: 0.5rem;">
                            ⚠️ Belum Mengumpulkan Tugas
                        </div>
                        <a href="{{ route('mahasiswa.assignments.submissions.create', $assignment) }}" class="btn btn-primary" style="display: inline-block; font-size: 0.85rem; padding: 0.45rem 1rem;">
                            📤 Kumpulkan Tugas Sekarang
                        </a>
                    @endif
                </div>
            @endif

            {{-- Rekap Pengumpulan Dosen (Jika Dosen) --}}
            @if(auth()->user()->role === 'dosen')
                <div style="margin-top: 1.5rem; padding: 1.25rem; background: rgba(255, 255, 255, 0.03); border-radius: 0.75rem; border: 1px solid var(--card-border, #334155); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: 700; color: var(--text-main, #f8fafc);">Daftar Pengumpulan Mahasiswa</div>
                        <div style="font-size: 0.82rem; color: var(--text-muted, #94a3b8);">
                            Total pengumpulan: {{ $assignment->submissions->count() }} mahasiswa
                        </div>
                    </div>
                    <a href="{{ route('dosen.assignments.submissions.index', $assignment) }}" class="btn btn-primary" style="font-size: 0.85rem; padding: 0.45rem 1rem;">
                        📋 Periksa Submission →
                    </a>
                </div>
            @endif
        </div>

        {{-- Back Link --}}
        <div>
            @if(auth()->user()->role === 'dosen')
                <a href="{{ route('dosen.courses.assignments.index', $assignment->course) }}" class="back-link">
                    ← Kembali ke Daftar Tugas
                </a>
            @else
                <a href="{{ route('mahasiswa.courses.assignments.index', $assignment->course) }}" class="back-link">
                    ← Kembali ke Daftar Tugas
                </a>
            @endif
        </div>
    </div>
</x-layout>
