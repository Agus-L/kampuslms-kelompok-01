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
                        @if(auth()->user()->role === 'dosen' && isset($stats))
                            <span>•</span>
                            <span>Total Dikumpulkan: <strong>{{ $stats->submitted }} / {{ $stats->total }} Mahasiswa</strong></span>
                        @endif
                    </div>
                </div>

                <div>
                    <a href="{{ auth()->user()->role === 'dosen' ? route('dosen.assignments.show', $assignment) : route('mahasiswa.assignments.show', $assignment) }}" class="btn btn-secondary">
                        ← Kembali ke Detail Tugas
                    </a>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════
             TAMPILAN DOSEN — Poin 5: Daftar Pengumpulan Lengkap
             ══════════════════════════════════════════════════════════════ --}}
        @if(auth()->user()->role === 'dosen' && isset($studentList) && isset($stats))

            {{-- Statistik Ringkas --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                    <div style="font-size: 2rem; font-weight: 800; color: #818cf8;">{{ $stats->total }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted, #94a3b8); font-weight: 600; margin-top: 0.25rem;">👥 Total Peserta</div>
                </div>
                <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                    <div style="font-size: 2rem; font-weight: 800; color: #10b981;">{{ $stats->submitted }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted, #94a3b8); font-weight: 600; margin-top: 0.25rem;">✅ Sudah Kumpul</div>
                </div>
                <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                    <div style="font-size: 2rem; font-weight: 800; color: #f59e0b;">{{ $stats->unsubmitted }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted, #94a3b8); font-weight: 600; margin-top: 0.25rem;">⏳ Belum Kumpul</div>
                </div>
                <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                    <div style="font-size: 2rem; font-weight: 800; color: #f87171;">{{ $stats->late }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted, #94a3b8); font-weight: 600; margin-top: 0.25rem;">⏰ Terlambat</div>
                </div>
            </div>

            {{-- Filter Tabs --}}
            <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
                @php
                    $filters = [
                        'all'         => ['label' => 'Semua',         'icon' => '📋', 'count' => $stats->total],
                        'submitted'   => ['label' => 'Sudah Kumpul',  'icon' => '✅', 'count' => $stats->submitted],
                        'unsubmitted' => ['label' => 'Belum Kumpul',  'icon' => '⏳', 'count' => $stats->unsubmitted],
                        'late'        => ['label' => 'Terlambat',     'icon' => '⏰', 'count' => $stats->late],
                    ];
                @endphp
                @foreach($filters as $key => $filter)
                    <a href="{{ route('dosen.assignments.submissions.index', [$assignment, 'status' => $key]) }}"
                       style="
                           padding: 0.5rem 1rem; border-radius: 9999px; font-size: 0.82rem; font-weight: 600;
                           text-decoration: none; transition: all 0.2s ease;
                           {{ $filterStatus === $key
                               ? 'background: linear-gradient(135deg, #6366f1, #3b82f6); color: #fff; border: 1px solid transparent;'
                               : 'background: var(--card-bg, #1e293b); color: var(--text-muted, #94a3b8); border: 1px solid var(--card-border, #334155);' }}
                       ">
                        {{ $filter['icon'] }} {{ $filter['label'] }} ({{ $filter['count'] }})
                    </a>
                @endforeach
            </div>

            {{-- Daftar Mahasiswa --}}
            @if ($studentList->isEmpty())
                <div style="text-align: center; padding: 3.5rem 1rem; background: var(--card-bg, #1e293b); border: 1px dashed var(--card-border, #334155); border-radius: 1rem; color: var(--text-muted, #94a3b8);">
                    <div style="font-size: 3rem; margin-bottom: 0.75rem;">📭</div>
                    <h3 style="color: var(--text-main, #f8fafc); font-weight: 700; margin-bottom: 0.35rem;">Tidak Ada Data</h3>
                    <p style="margin: 0;">Tidak ada mahasiswa yang cocok dengan filter "{{ $filters[$filterStatus]['label'] ?? 'Semua' }}".</p>
                </div>
            @else
                <div style="display: grid; gap: 1rem;">
                    @foreach ($studentList as $entry)
                        @php
                            $student    = $entry->student;
                            $submission = $entry->submission;
                            $status     = $entry->status;
                            $borderLeftColor = match($status) {
                                'submitted' => '#10b981',
                                'late'      => '#f87171',
                                default     => '#f59e0b',
                            };
                        @endphp
                        <div style="
                            background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155);
                            border-left: 4px solid {{ $borderLeftColor }}; border-radius: 0.75rem; padding: 1.25rem;
                            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;
                        ">
                            {{-- Info Mahasiswa --}}
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div style="
                                    width: 44px; height: 44px; border-radius: 50%;
                                    background: linear-gradient(135deg, {{ $status === 'unsubmitted' ? '#f59e0b, #ef4444' : '#6366f1, #3b82f6' }});
                                    display: flex; align-items: center; justify-content: center;
                                    font-weight: 700; color: #ffffff; font-size: 1.1rem;
                                ">
                                    {{ strtoupper(substr($student->name ?? 'M', 0, 1)) }}
                                </div>
                                <div>
                                    <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-main, #f8fafc); margin: 0 0 0.2rem 0;">
                                        {{ $student->name ?? 'Mahasiswa' }}
                                    </h3>
                                    <div style="font-size: 0.8rem; color: var(--text-muted, #94a3b8); display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
                                        <span>📧 {{ $student->email ?? '-' }}</span>
                                        @if($submission)
                                            <span>•</span>
                                            <span>🕒 Dikumpulkan: {{ $submission->submitted_at ? $submission->submitted_at->format('d M Y, H:i') . ' WITA' : '-' }}</span>
                                            @if($submission->is_late)
                                                <span style="font-size: 0.72rem; padding: 0.15rem 0.5rem; border-radius: 9999px; background: rgba(248, 113, 113, 0.15); color: #f87171; font-weight: 700;">⏰ Terlambat</span>
                                            @else
                                                <span style="font-size: 0.72rem; padding: 0.15rem 0.5rem; border-radius: 9999px; background: rgba(16, 185, 129, 0.15); color: #10b981; font-weight: 700;">✓ Tepat Waktu</span>
                                            @endif
                                        @else
                                            <span>•</span>
                                            <span style="font-size: 0.72rem; padding: 0.15rem 0.5rem; border-radius: 9999px; background: rgba(245, 158, 11, 0.15); color: #f59e0b; font-weight: 700;">⚠️ Belum Mengumpulkan</span>
                                        @endif
                                    </div>
                                    @if($submission)
                                        <div style="margin-top: 0.35rem; font-size: 0.8rem; color: #818cf8;">
                                            📎 <a href="{{ route('dosen.submissions.download', $submission) }}" style="color: #38bdf8; text-decoration: underline; font-weight: 600;" title="Unduh berkas submission">
                                                {{ $submission->original_name }}
                                            </a>
                                            ({{ $submission->humanReadableSize() }})
                                        </div>
                                    @else
                                        <div style="margin-top: 0.35rem; font-size: 0.8rem; color: var(--text-muted, #64748b);">
                                            @if(now()->gt($assignment->due_at))
                                                ⛔ Deadline sudah terlewati
                                                @if(!$assignment->allow_late)
                                                    — pengumpulan terlambat <strong>tidak diizinkan</strong>
                                                @else
                                                    — pengumpulan terlambat masih diizinkan
                                                @endif
                                            @else
                                                🕐 Sisa waktu: {{ now()->diffForHumans($assignment->due_at, ['parts' => 2, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]) }} lagi
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Aksi Dosen --}}
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                @if($submission)
                                    <div style="text-align: right;">
                                        @if($submission->grade)
                                            <div style="font-size: 1.1rem; font-weight: 800; color: #10b981;">
                                                {{ $submission->grade->score }} <span style="font-size: 0.8rem; color: var(--text-muted);">/ {{ $assignment->max_score }}</span>
                                            </div>
                                            <span style="font-size: 0.72rem; padding: 0.15rem 0.5rem; border-radius: 9999px; background: rgba(16, 185, 129, 0.15); color: #10b981; font-weight: 600;">
                                                Sudah Dinilai
                                            </span>
                                        @else
                                            <div style="font-size: 0.9rem; font-weight: 600; color: #f59e0b;">Belum Dinilai</div>
                                            <span style="font-size: 0.72rem; padding: 0.15rem 0.5rem; border-radius: 9999px; background: rgba(245, 158, 11, 0.15); color: #f59e0b; font-weight: 600;">
                                                Perlu Diperiksa
                                            </span>
                                        @endif
                                    </div>
                                    <a href="{{ route('dosen.submissions.download', $submission) }}" class="btn btn-secondary" style="padding: 0.45rem 0.9rem; font-size: 0.85rem;">
                                        📥 Unduh
                                    </a>
                                    <a href="{{ route('dosen.submissions.show', $submission) }}" class="btn btn-primary" style="padding: 0.45rem 0.9rem; font-size: 0.85rem;">
                                        {{ $submission->grade ? 'Perbarui Nilai' : 'Beri Nilai' }} →
                                    </a>
                                @else
                                    <div style="text-align: right;">
                                        <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted, #64748b);">—</div>
                                        <span style="font-size: 0.72rem; color: var(--text-muted, #64748b);">Belum ada berkas</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination Links — Tampilan Dosen (Build #5 Minggu 11) --}}
                @if (method_exists($studentList, 'hasPages') && $studentList->hasPages())
                    <div style="margin-top: 1.5rem;">
                        {{ $studentList->links() }}
                    </div>
                @endif
            @endif

        {{-- ══════════════════════════════════════════════════════════════
             TAMPILAN MAHASISWA — Tetap menggunakan data $submissions
             dari Mahasiswa\SubmissionController (KODE TEMAN, TIDAK DIUBAH)
             ══════════════════════════════════════════════════════════════ --}}
        @else
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
                                        📎 <a href="{{ route('mahasiswa.submissions.download', $submission) }}" style="color: #38bdf8; text-decoration: underline; font-weight: 600;" title="Unduh berkas submission">
                                            {{ $submission->original_name }}
                                        </a>
                                        ({{ $submission->humanReadableSize() }})
                                    </div>
                                </div>
                            </div>

                            <div style="display: flex; align-items: center; gap: 0.75rem;">
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
        @endif
    </div>
</x-layout>
