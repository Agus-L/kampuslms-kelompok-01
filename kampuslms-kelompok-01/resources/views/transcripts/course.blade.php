<x-layout :title="'Transkrip Mahasiswa — ' . $course->name . ' — Kampus LMS'">
    <div class="page-container">
        {{-- Header & Navigasi --}}
        <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 1rem; padding: 1.75rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <span style="font-size: 0.8rem; font-weight: 700; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 0.25rem 0.6rem; border-radius: 0.25rem;">
                        {{ $course->code }} — {{ $course->name }}
                    </span>
                    <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main, #f8fafc); margin: 0.5rem 0 0.25rem 0;">
                        🎓 Rincian Transkrip & Presensi Tugas Mahasiswa
                    </h1>
                    <p style="font-size: 0.9rem; color: var(--text-muted, #94a3b8); margin: 0;">
                        Mahasiswa: {{ $student->name }} ({{ $student->nim_nip }})
                    </p>
                </div>
                <div>
                    <a href="{{ route('dosen.courses.students.transcript.export.pdf', [$course, $student]) }}" class="btn btn-primary" target="_blank">
                        📥 Unduh Transkrip PDF
                    </a>
                </div>
            </div>
        </div>

        {{-- Statistik Presensi & Nilai --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase;">Nilai Akhir</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: #38bdf8; margin-top: 0.25rem;">
                    {{ $data['grade']['final_score'] !== null ? number_format($data['grade']['final_score'], 2) : '—' }}
                </div>
                <div style="font-size: 0.8rem; font-weight: 700; color: #818cf8; margin-top: 0.2rem;">Huruf Mutu: {{ $data['grade']['letter'] }}</div>
            </div>
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase;">Kehadiran Tugas</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: {{ $data['attendance']['attendance_rate'] >= 75 ? '#4ade80' : '#ef4444' }}; margin-top: 0.25rem;">
                    {{ $data['attendance']['attendance_rate'] }}%
                </div>
                <div style="font-size: 0.75rem; color: var(--text-muted, #94a3b8); margin-top: 0.2rem;">{{ $data['attendance']['status_label'] }}</div>
            </div>
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase;">Tepat Waktu</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: #4ade80; margin-top: 0.25rem;">{{ $data['attendance']['on_time_count'] }}</div>
            </div>
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase;">Terlambat / Alpa</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: #f59e0b; margin-top: 0.25rem;">
                    {{ $data['attendance']['late_count'] }} / {{ $data['attendance']['missing_count'] }}
                </div>
            </div>
        </div>

        {{-- Tabel Rincian Tugas --}}
        <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 1rem; padding: 1.5rem; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main, #f8fafc); margin-bottom: 1rem;">
                📋 Rincian Pengumpulan & Penilaian Tugas
            </h2>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--card-border, #334155); color: var(--text-muted, #94a3b8); font-size: 0.85rem;">
                            <th style="padding: 0.75rem;">Judul Tugas</th>
                            <th style="padding: 0.75rem; text-align: center;">Batas Waktu</th>
                            <th style="padding: 0.75rem; text-align: center;">Status Presensi</th>
                            <th style="padding: 0.75rem; text-align: center;">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['assignments'] as $assign)
                            <tr style="border-bottom: 1px solid var(--card-border, #334155); font-size: 0.9rem;">
                                <td style="padding: 0.75rem; color: var(--text-main, #f8fafc);">{{ $assign['title'] }}</td>
                                <td style="padding: 0.75rem; text-align: center; color: var(--text-muted, #94a3b8);">
                                    {{ $assign['due_at'] ? \Carbon\Carbon::parse($assign['due_at'])->format('d M Y H:i') : '—' }}
                                </td>
                                <td style="padding: 0.75rem; text-align: center;">
                                    <span style="font-size: 0.8rem; font-weight: 600; padding: 0.2rem 0.5rem; border-radius: 0.25rem;
                                        @if($assign['status_key'] === 'on_time') background: rgba(74, 222, 128, 0.15); color: #4ade80;
                                        @elseif($assign['status_key'] === 'late') background: rgba(245, 158, 11, 0.15); color: #f59e0b;
                                        @elseif($assign['status_key'] === 'missing') background: rgba(239, 68, 68, 0.15); color: #ef4444;
                                        @else background: rgba(148, 163, 184, 0.15); color: #94a3b8;
                                        @endif">
                                        {{ $assign['status_label'] }}
                                    </span>
                                </td>
                                <td style="padding: 0.75rem; text-align: center; font-weight: 700;">
                                    {{ $assign['score'] !== null ? number_format($assign['score'], 2) : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="padding: 1.5rem; text-align: center; color: var(--text-muted, #94a3b8);">Belum ada tugas di mata kuliah ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layout>

