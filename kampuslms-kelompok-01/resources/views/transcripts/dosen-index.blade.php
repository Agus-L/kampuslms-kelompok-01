x-layout :title="'Laporan Transkrip Pengampu — Kampus LMS'">
    <div class="page-container">
        {{-- Breadcrumb --}}
        <nav class="breadcrumb" style="margin-bottom: 1rem;">
            <a href="{{ route('dosen.courses.index') }}" class="breadcrumb-link">Mata Kuliah</a>
            <span class="breadcrumb-separator">›</span>
            <span class="breadcrumb-current">Laporan Transkrip Pengampu</span>
        </nav>

        {{-- Header --}}
        <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 1rem; padding: 1.75rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <span style="font-size: 0.8rem; font-weight: 700; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 0.25rem 0.6rem; border-radius: 0.25rem;">
                        Laporan Transkrip Terpadu Pengampu
                    </span>
                    <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main, #f8fafc); margin: 0.5rem 0 0.25rem 0;">
                        📋 Transkrip Semua Mata Kuliah yang Diampu
                    </h1>
                    <p style="font-size: 0.9rem; color: var(--text-muted, #94a3b8); margin: 0;">
                        {{ $lecturer['name'] }} ({{ $lecturer['nim_nip'] }}) — {{ $lecturer['email'] }}
                    </p>
                </div>
                <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                    <a href="{{ route('dosen.transcript.export.pdf', ['preview' => 1]) }}"
                       class="btn" target="_blank"
                       style="background: var(--card-bg); border: 1px solid var(--card-border); color: var(--text-main); padding: 0.6rem 1.1rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                        👁️ Pratinjau PDF
                    </a>
                    <a href="{{ route('dosen.transcript.export.pdf') }}"
                       class="btn btn-primary"
                       style="padding: 0.6rem 1.25rem; font-weight: 700; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.35rem;">
                        📥 Unduh Laporan PDF
                    </a>
                </div>
            </div>
        </div>

        {{-- Ringkasan Total --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase;">Total MK Diampu</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: #818cf8; margin-top: 0.25rem;">{{ $total_courses }}</div>
            </div>
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase;">Total Mahasiswa</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: #38bdf8; margin-top: 0.25rem;">
                    {{ $total_students_count }} 
                </div>
            </div>
        </div>

        @forelse($courses as $course)
            {{-- Kartu per Mata Kuliah --}}
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 1rem; padding: 1.5rem; margin-bottom: 1.5rem;">

                {{-- Header MK --}}
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem;">
                    <div>
                        <span style="font-size: 0.75rem; font-weight: 700; color: #38bdf8; background: rgba(56,189,248,0.1); padding: 0.2rem 0.55rem; border-radius: 0.25rem;">
                            {{ $course['code'] }} • {{ $course['sks'] }} SKS
                        </span>
                        <h2 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main, #f8fafc); margin: 0.4rem 0 0 0;">
                            {{ $course['name'] }}
                        </h2>
                    </div>
                    {{-- Statistik kelas ringkas --}}
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <div style="text-align: center;">
                            <div style="font-size: 0.7rem; color: var(--text-muted, #94a3b8); text-transform: uppercase;">Mahasiswa</div>
                            <div style="font-size: 1.1rem; font-weight: 800; color: #38bdf8;">{{ $course['total_students'] }}</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 0.7rem; color: var(--text-muted, #94a3b8); text-transform: uppercase;">Rata-rata Nilai</div>
                            <div style="font-size: 1.1rem; font-weight: 800; color: #f59e0b;">
                                {{ $course['avg_score'] !== null ? number_format($course['avg_score'], 1) : '—' }}
                                ({{ $course['avg_grade'] }})
                            </div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 0.7rem; color: var(--text-muted, #94a3b8); text-transform: uppercase;">Kelulusan</div>
                            <div style="font-size: 1.1rem; font-weight: 800; color: {{ $course['pass_rate'] >= 75 ? '#4ade80' : '#ef4444' }};">
                                {{ $course['pass_rate'] }}%
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabel Mahasiswa --}}
                @if(count($course['students']) > 0)
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                            <thead>
                                <tr style="background: rgba(99,102,241,0.1); border-bottom: 1px solid var(--card-border, #334155);">
                                    <th style="padding: 0.6rem 0.8rem; text-align: left; color: var(--text-muted, #94a3b8); font-weight: 700; font-size: 0.75rem; text-transform: uppercase;">No</th>
                                    <th style="padding: 0.6rem 0.8rem; text-align: left; color: var(--text-muted, #94a3b8); font-weight: 700; font-size: 0.75rem; text-transform: uppercase;">NIM</th>
                                    <th style="padding: 0.6rem 0.8rem; text-align: left; color: var(--text-muted, #94a3b8); font-weight: 700; font-size: 0.75rem; text-transform: uppercase;">Nama Mahasiswa</th>
                                    <th style="padding: 0.6rem 0.8rem; text-align: center; color: var(--text-muted, #94a3b8); font-weight: 700; font-size: 0.75rem; text-transform: uppercase;">Nilai Akhir</th>
                                    <th style="padding: 0.6rem 0.8rem; text-align: center; color: var(--text-muted, #94a3b8); font-weight: 700; font-size: 0.75rem; text-transform: uppercase;">Huruf</th>
                                    <th style="padding: 0.6rem 0.8rem; text-align: center; color: var(--text-muted, #94a3b8); font-weight: 700; font-size: 0.75rem; text-transform: uppercase;">Status</th>
                                    <th style="padding: 0.6rem 0.8rem; text-align: center; color: var(--text-muted, #94a3b8); font-weight: 700; font-size: 0.75rem; text-transform: uppercase;">Kehadiran Tugas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($course['students'] as $i => $student)
                                    <tr style="border-bottom: 1px solid rgba(51,65,85,0.5); {{ $loop->even ? 'background: rgba(30,41,59,0.4);' : '' }}">
                                        <td style="padding: 0.6rem 0.8rem; color: var(--text-muted, #94a3b8);">{{ $i + 1 }}</td>
                                        <td style="padding: 0.6rem 0.8rem; color: var(--text-muted, #94a3b8); font-family: monospace; font-size: 0.8rem;">{{ $student['nim_nip'] }}</td>
                                        <td style="padding: 0.6rem 0.8rem; color: var(--text-main, #f8fafc); font-weight: 600;">{{ $student['name'] }}</td>
                                        <td style="padding: 0.6rem 0.8rem; text-align: center; color: #f59e0b; font-weight: 700;">
                                            {{ $student['final_score'] !== null ? number_format($student['final_score'], 2) : '—' }}
                                        </td>
                                        <td style="padding: 0.6rem 0.8rem; text-align: center;">
                                            <span style="font-weight: 800; font-size: 1rem; color: {{ $student['is_passed'] ? '#4ade80' : '#ef4444' }};">
                                                {{ $student['letter_grade'] }}
                                            </span>
                                        </td>
                                        <td style="padding: 0.6rem 0.8rem; text-align: center;">
                                            @if($student['final_score'] === null)
                                                <span style="color: #94a3b8; font-size: 0.75rem;">Belum Dinilai</span>
                                            @elseif($student['is_passed'])
                                                <span style="background: rgba(74,222,128,0.15); color: #4ade80; padding: 0.2rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 700;">Lulus</span>
                                            @else
                                                <span style="background: rgba(239,68,68,0.15); color: #ef4444; padding: 0.2rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 700;">Tidak Lulus</span>
                                            @endif
                                        </td>
                                        <td style="padding: 0.6rem 0.8rem; text-align: center;">
                                            <span style="font-weight: 700; color: {{ $student['attendance_rate'] >= 75 ? '#4ade80' : '#ef4444' }};">
                                                {{ $student['attendance_rate'] }}%
                                            </span>
                                            <div style="font-size: 0.7rem; color: var(--text-muted, #94a3b8);">
                                                {{ $student['on_time_count'] }}✓ {{ $student['late_count'] }}⏰ {{ $student['missing_count'] }}✗
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            {{-- Baris rangkuman kelas --}}
                            <tfoot>
                                <tr style="background: rgba(99,102,241,0.08); border-top: 2px solid var(--card-border, #334155);">
                                    <td colspan="3" style="padding: 0.6rem 0.8rem; color: var(--text-muted, #94a3b8); font-weight: 700; font-size: 0.8rem; text-align: right;">Ringkasan Kelas:</td>
                                    <td style="padding: 0.6rem 0.8rem; text-align: center; font-weight: 800; color: #f59e0b;">
                                        {{ $course['avg_score'] !== null ? number_format($course['avg_score'], 2) : '—' }}
                                    </td>
                                    <td style="padding: 0.6rem 0.8rem; text-align: center; font-weight: 800; color: #f59e0b;">{{ $course['avg_grade'] }}</td>
                                    <td style="padding: 0.6rem 0.8rem; text-align: center; font-size: 0.8rem; color: var(--text-muted, #94a3b8);">
                                        Lulus: {{ $course['passed_count'] }}/{{ $course['total_students'] }}
                                    </td>
                                    <td style="padding: 0.6rem 0.8rem; text-align: center; font-weight: 700; color: {{ $course['pass_rate'] >= 75 ? '#4ade80' : '#ef4444' }};">
                                        {{ $course['pass_rate'] }}%
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div style="text-align: center; padding: 2rem; color: var(--text-muted, #94a3b8);">
                        Belum ada mahasiswa terdaftar di mata kuliah ini.
                    </div>
                @endif
            </div>
        @empty
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 1rem; padding: 3rem; text-align: center; color: var(--text-muted, #94a3b8);">
                Anda belum mengampu mata kuliah apa pun.
            </div>
        @endforelse
    </div>
</x-layout>

