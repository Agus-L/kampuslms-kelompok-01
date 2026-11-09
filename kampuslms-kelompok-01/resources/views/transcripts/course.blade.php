<x-layout :title="'Transkrip Mahasiswa — ' . $course->name . ' — Kampus LMS'">
    <div class="page-container">
        {{-- Breadcrumb Navigasi --}}
        <nav class="breadcrumb" style="margin-bottom: 1rem;">
            <a href="{{ route('dosen.courses.index') }}" class="breadcrumb-link">Mata Kuliah</a>
            <span class="breadcrumb-separator">›</span>
            <a href="{{ route('dosen.courses.show', $course) }}" class="breadcrumb-link">{{ $course->name }}</a>
            <span class="breadcrumb-separator">›</span>
            <a href="{{ route('dosen.courses.grades.rekap', $course) }}" class="breadcrumb-link">Rekap Nilai</a>
            <span class="breadcrumb-separator">›</span>
            <span class="breadcrumb-current">Transkrip {{ $student->name }}</span>
        </nav>

        {{-- Header & Navigasi --}}
        <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 1rem; padding: 1.75rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <span style="font-size: 0.8rem; font-weight: 700; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 0.25rem 0.6rem; border-radius: 0.25rem;">
                        Transkrip Mata Kuliah • {{ $course->code }}
                    </span>
                    <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main, #f8fafc); margin: 0.5rem 0 0.25rem 0;">
                        📄 Transkrip Nilai & Rekapitulasi Presensi Tugas
                    </h1>
                    <p style="font-size: 0.9rem; color: var(--text-muted, #94a3b8); margin: 0;">
                        {{ $student->name }} ({{ $student->nim_nip }}) — {{ $student->email }}
                    </p>
                </div>
                <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                    <a href="{{ route('dosen.courses.grades.rekap', $course) }}" class="btn" style="background: var(--card-bg); border: 1px solid var(--card-border); color: var(--text-main); padding: 0.6rem 1rem; font-weight: 600; text-decoration: none;">
                        ← Kembali ke Rekap Nilai
                    </a>
                    <a href="{{ route('dosen.courses.students.transcript.export.pdf', [$course, $student, 'preview' => 1]) }}" class="btn" target="_blank" style="background: var(--card-bg); border: 1px solid var(--card-border); color: var(--text-main); padding: 0.6rem 1.1rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;" title="Buka pratinjau PDF di peramban">
                        👁️ Pratinjau PDF
                    </a>
                    <a href="{{ route('dosen.courses.students.transcript.export.pdf', [$course, $student]) }}" class="btn btn-primary" style="padding: 0.6rem 1.25rem; font-weight: 700; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.35rem;" title="Unduh berkas PDF ke penyimpanan komputer lokal">
                        📥 Unduh PDF
                    </a>
                </div>
            </div>
        </div>

        {{-- Statistik Ringkasan --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase;">Total SKS Diambil</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: #38bdf8; margin-top: 0.25rem;">{{ $course->sks }} SKS</div>
            </div>
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase;">Total SKS Lulus</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: #4ade80; margin-top: 0.25rem;">{{ ($data['grade']['is_passed'] ?? false) ? $course->sks : 0 }} SKS</div>
            </div>
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase;">Nilai & Mutu</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: #f59e0b; margin-top: 0.25rem;">{{ $data['grade']['letter'] }} ({{ number_format($data['grade']['grade_point'], 2) }})</div>
                <div style="font-size: 0.75rem; color: var(--text-muted, #94a3b8); margin-top: 0.2rem;">Skor: {{ $data['grade']['final_score'] !== null ? number_format($data['grade']['final_score'], 2) : '—' }}</div>
            </div>
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase;">Kehadiran Tugas</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: {{ $data['attendance']['attendance_rate'] >= 75 ? '#4ade80' : '#ef4444' }}; margin-top: 0.25rem;">
                    {{ $data['attendance']['attendance_rate'] }}%
                </div>
                <div style="font-size: 0.75rem; color: var(--text-muted, #94a3b8); margin-top: 0.2rem;">{{ $data['attendance']['status_label'] }}</div>
            </div>
        </div>

        {{-- Tabel Prestasi Akademik --}}
        <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 1rem; padding: 1.5rem; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main, #f8fafc); margin-bottom: 1rem;">
                📚 Prestasi Akademik Mata Kuliah
            </h2>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--card-border, #334155); color: var(--text-muted, #94a3b8); font-size: 0.85rem;">
                            <th style="padding: 0.75rem;">Kode MK</th>
                            <th style="padding: 0.75rem;">Nama Mata Kuliah</th>
                            <th style="padding: 0.75rem; text-align: center;">SKS</th>
                            <th style="padding: 0.75rem; text-align: center;">Nilai Angka</th>
                            <th style="padding: 0.75rem; text-align: center;">Huruf</th>
                            <th style="padding: 0.75rem; text-align: center;">Bobot Indeks</th>
                            <th style="padding: 0.75rem; text-align: center;">Angka Mutu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom: 1px solid var(--card-border, #334155); font-size: 0.9rem;">
                            <td style="padding: 0.75rem; font-weight: 600; color: #818cf8;">{{ $course->code }}</td>
                            <td style="padding: 0.75rem; color: var(--text-main, #f8fafc);">{{ $course->name }}</td>
                            <td style="padding: 0.75rem; text-align: center;">{{ $course->sks }}</td>
                            <td style="padding: 0.75rem; text-align: center;">{{ $data['grade']['final_score'] !== null ? number_format($data['grade']['final_score'], 2) : '—' }}</td>
                            <td style="padding: 0.75rem; text-align: center; font-weight: 700;">{{ $data['grade']['letter'] }}</td>
                            <td style="padding: 0.75rem; text-align: center;">{{ number_format($data['grade']['grade_point'], 2) }}</td>
                            <td style="padding: 0.75rem; text-align: center; font-weight: 600;">{{ number_format($data['grade']['quality_points'] ?? ($course->sks * $data['grade']['grade_point']), 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layout>