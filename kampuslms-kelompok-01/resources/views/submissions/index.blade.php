<x-layout :title="'Daftar Pengumpulan — ' . $assignment->title">
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
            <a href="{{ auth()->user()->role === 'dosen' ? route('dosen.assignments.show', $assignment) : route('mahasiswa.assignments.show', $assignment) }}" class="breadcrumb-link">
                {{ $assignment->title }}
            </a>
            <span class="breadcrumb-separator">›</span>
            <span class="breadcrumb-current">Pengumpulan Mahasiswa</span>
        </nav>

        {{-- Header --}}
        <div class="page-header" style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 1rem; padding: 1.75rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <span style="font-size: 0.8rem; font-weight: 700; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 0.25rem 0.6rem; border-radius: 0.25rem;">
                        {{ $assignment->course->code }} — {{ $assignment->course->name }}
                    </span>
                    <h1 class="page-title" style="margin-top: 0.5rem; margin-bottom: 0.25rem;">
                        📥 Rekap Pengumpulan Tugas
                    </h1>
                    <p class="page-subtitle" style="margin: 0; font-size: 1.1rem; font-weight: 600; color: var(--text-main);">
                        {{ $assignment->title }}
                    </p>
                    <div style="display: flex; gap: 1rem; font-size: 0.85rem; color: var(--text-muted, #94a3b8); margin-top: 0.5rem; flex-wrap: wrap;">
                        <span>⏰ Deadline: <strong style="color: #f87171;">{{ $assignment->due_at->format('d M Y, H:i') }} WITA</strong></span>
                        <span>•</span>
                        <span>🎯 Skor Maksimum: <strong>{{ $assignment->max_score }}</strong></span>
                        <span>•</span>
                        <span>Total Dikumpulkan: <strong>{{ $submissions->count() }} Mahasiswa</strong></span>
                    </div>
                </div>

                <div>
                    <a href="{{ auth()->user()->role === 'dosen' ? route('dosen.assignments.show', $assignment) : route('mahasiswa.assignments.show', $assignment) }}" class="btn btn-secondary">
                        ← Kembali ke Detail Tugas
                    </a>
                </div>
            </div>
        </div>

        {{-- Daftar Pengumpulan --}}
        @if ($submissions->isEmpty())
            <div style="text-align: center; padding: 3.5rem 1rem; background: var(--card-bg, #1e293b); border: 1px dashed var(--card-border, #334155); border-radius: 1rem; color: var(--text-muted, #94a3b8);">
                <div style="font-size: 3rem; margin-bottom: 0.75rem;">📭</div>
                <h3 style="color: var(--text-main, #f8fafc); font-weight: 700; margin-bottom: 0.35rem;">Belum Ada Pengumpulan</h3>
                <p style="margin: 0;">Belum ada mahasiswa yang mengumpulkan berkas tugas untuk penugasan ini.</p>
            </div>
        @else
            <div style="display: grid; gap: 1rem;">
                @foreach ($submissions as $submission)
                    <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #3b82f6); display: flex; align-items: center; justify-content: center; font-weight: 700; color: #ffffff; font-size: 1.1rem;">
                                {{ strtoupper(substr($submission->student->name ?? 'M', 0, 1)) }}
                            </div>
                            <div>
                                <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-main, #f8fafc); margin: 0 0 0.2rem 0;">
                                    {{ $submission->student->name ?? 'Mahasiswa' }}
                                </h3>
                                <div style="font-size: 0.8rem; color: var(--text-muted, #94a3b8); display: flex; gap: 0.75rem; flex-wrap: wrap;">
                                    <span>📧 {{ $submission->student->email ?? '-' }}</span>
                                    <span>•</span>
                                    <span>🕒 Dikumpulkan: {{ $submission->submitted_at ? $submission->submitted_at->format('d M Y, H:i') . ' WITA' : '-' }}</span>
                                    @if($submission->is_late)
                                        <span style="color: #f87171; font-weight: 600;">(Terlambat)</span>
                                    @else
                                        <span style="color: #10b981; font-weight: 600;">(Tepat Waktu)</span>
                                    @endif
                                </div>
                                <div style="margin-top: 0.35rem; font-size: 0.8rem; color: #818cf8;">
                                    📎 {{ $submission->original_name }} ({{ number_format($submission->file_size / 1024, 1) }} KB)
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="text-align: right;">
                                @if($submission->grade)
                                    <div style="font-size: 1.1rem; font-weight: 800; color: #10b981;">
                                        {{ $submission->grade->score }} <span style="font-size: 0.8rem; color: var(--text-muted);">/ {{ $assignment->max_score }}</span>
                                    </div>
                                    <span style="font-size: 0.72rem; padding: 0.15rem 0.5rem; border-radius: 9999px; background: rgba(16, 185, 129, 0.15); color: #10b981; font-weight: 600;">
                                        Sudah Dinilai
                                    </span>
                                @else
                                    <div style="font-size: 0.9rem; font-weight: 600; color: #f59e0b;">
                                        Belum Dinilai
                                    </div>
                                    <span style="font-size: 0.72rem; padding: 0.15rem 0.5rem; border-radius: 9999px; background: rgba(245, 158, 11, 0.15); color: #f59e0b; font-weight: 600;">
                                        Perlu Diperiksa
                                    </span>
                                @endif
                            </div>

                            @if(auth()->user()->role === 'dosen')
                                <a href="{{ route('dosen.submissions.show', $submission) }}" class="btn btn-primary" style="padding: 0.45rem 0.9rem; font-size: 0.85rem;">
                                    {{ $submission->grade ? 'Perbarui Nilai' : 'Beri Nilai' }} →
                                </a>
                            @else
                                <a href="{{ route('mahasiswa.submissions.show', $submission) }}" class="btn btn-secondary" style="padding: 0.45rem 0.9rem; font-size: 0.85rem;">
                                    Lihat Detail →
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if(method_exists($submissions, 'hasPages') && $submissions->hasPages())
                <div style="margin-top: 1.5rem;">
                    {{ $submissions->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layout>
