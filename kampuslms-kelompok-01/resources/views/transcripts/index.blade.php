<x-layout :title="'Transkrip Prestasi Akademik — Kampus LMS'">
    <div class="page-container">
        {{-- Breadcrumb Navigasi --}}
        <nav class="breadcrumb" style="margin-bottom: 1rem;">
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.users.index') }}" class="breadcrumb-link">Pengguna</a>
                <span class="breadcrumb-separator">›</span>
                <span class="breadcrumb-current">Transkrip {{ $student->name }}</span>
            @else
                <a href="{{ route('mahasiswa.courses.index') }}" class="breadcrumb-link">Mata Kuliah</a>
                <span class="breadcrumb-separator">›</span>
                <span class="breadcrumb-current">Transkrip Akademik</span>
            @endif
        </nav>

        {{-- Header & Navigasi --}}
        <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 1rem; padding: 1.75rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <span style="font-size: 0.8rem; font-weight: 700; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 0.25rem 0.6rem; border-radius: 0.25rem;">
                        Transkrip Akademik Kumulatif
                    </span>
                    <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main, #f8fafc); margin: 0.5rem 0 0.25rem 0;">
                        📄 Transkrip Prestasi Akademik
                    </h1>
                    <p style="font-size: 0.9rem; color: var(--text-muted, #94a3b8); margin: 0;">
                        {{ $student->name }} ({{ $student->nim_nip }}) — {{ $student->email }}
                    </p>
                </div>
                <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.users.index') }}" class="btn" style="background: var(--card-bg); border: 1px solid var(--card-border); color: var(--text-main); padding: 0.6rem 1rem; font-weight: 600; text-decoration: none;">
                            ← Kembali ke Pengguna
                        </a>
                        <a href="{{ route('admin.users.transcript.export.pdf', [$student, 'preview' => 1]) }}" class="btn" target="_blank" style="background: var(--card-bg); border: 1px solid var(--card-border); color: var(--text-main); padding: 0.6rem 1.1rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;" title="Buka pratinjau PDF di peramban">
                            👁️ Pratinjau PDF
                        </a>
                        <a href="{{ route('admin.users.transcript.export.pdf', $student) }}" class="btn btn-primary" style="padding: 0.6rem 1.25rem; font-weight: 700; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.35rem;" title="Unduh berkas PDF ke penyimpanan komputer lokal">
                            📥 Unduh PDF
                        </a>
                    @elseif(auth()->user()->role === 'mahasiswa')
                        <a href="{{ route('mahasiswa.courses.index') }}" class="btn" style="background: var(--card-bg); border: 1px solid var(--card-border); color: var(--text-main); padding: 0.6rem 1rem; font-weight: 600; text-decoration: none;">
                            ← Kembali ke Mata Kuliah
                        </a>
                        <a href="{{ route('mahasiswa.transcript.export.pdf', ['preview' => 1]) }}" class="btn" target="_blank" style="background: var(--card-bg); border: 1px solid var(--card-border); color: var(--text-main); padding: 0.6rem 1.1rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;" title="Buka pratinjau PDF di peramban">
                            👁️ Pratinjau PDF
                        </a>
                        <a href="{{ route('mahasiswa.transcript.export.pdf') }}" class="btn btn-primary" style="padding: 0.6rem 1.25rem; font-weight: 700; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.35rem;" title="Unduh berkas PDF ke penyimpanan komputer lokal">
                            📥 Unduh PDF
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Statistik Ringkasan Kumulatif --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase;">Total SKS Diambil</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: #38bdf8; margin-top: 0.25rem;">{{ $data['summary']['total_credits_taken'] }} SKS</div>
            </div>
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase;">Total SKS Lulus</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: #4ade80; margin-top: 0.25rem;">{{ $data['summary']['total_credits_passed'] }} SKS</div>
            </div>
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase;">IPK Kumulatif</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: #f59e0b; margin-top: 0.25rem;">{{ number_format($data['summary']['gpa'], 2) }}</div>
                <div style="font-size: 0.75rem; color: var(--text-muted, #94a3b8); margin-top: 0.2rem;">{{ $data['summary']['predicate'] }}</div>
            </div>
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase;">Kehadiran Tugas</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: {{ $data['summary']['overall_attendance_rate'] >= 75 ? '#4ade80' : '#ef4444' }}; margin-top: 0.25rem;">
                    {{ $data['summary']['overall_attendance_rate'] }}%
                </div>
                <div style="font-size: 0.75rem; color: var(--text-muted, #94a3b8); margin-top: 0.2rem;">{{ $data['summary']['attendance_status'] }}</div>
            </div>
        </div>

        {{-- Tabel Nilai Akademik --}}
        <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 1rem; padding: 1.5rem; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main, #f8fafc); margin-bottom: 1rem;">
                📚 Prestasi Akademik per Mata Kuliah
            </h2>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--card-border, #334155); color: var(--text-muted, #94a3b8); font-size: 0.85rem;">
                            <th style="padding: 0.75rem; width: 5%; text-align: center;">No</th>
                            <th style="padding: 0.75rem; width: 15%;">Kode MK</th>
                            <th style="padding: 0.75rem;">Nama Mata Kuliah</th>
                            <th style="padding: 0.75rem; text-align: center; width: 10%;">SKS</th>
                            <th style="padding: 0.75rem; text-align: center; width: 15%;">Nilai Angka</th>
                            <th style="padding: 0.75rem; text-align: center; width: 12%;">Huruf Mutu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['courses'] as $idx => $course)
                            <tr style="border-bottom: 1px solid var(--card-border, #334155); font-size: 0.9rem;">
                                <td style="padding: 0.75rem; text-align: center; color: var(--text-muted, #94a3b8);">{{ $idx + 1 }}</td>
                                <td style="padding: 0.75rem; font-weight: 600; color: #818cf8;">{{ $course['code'] }}</td>
                                <td style="padding: 0.75rem; color: var(--text-main, #f8fafc);">{{ $course['name'] }}</td>
                                <td style="padding: 0.75rem; text-align: center;">{{ $course['sks'] }}</td>
                                <td style="padding: 0.75rem; text-align: center;">{{ $course['final_score'] !== null ? number_format($course['final_score'], 2) : '—' }}</td>
                                <td style="padding: 0.75rem; text-align: center; font-weight: 700;">{{ $course['letter_grade'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 1.5rem; text-align: center; color: var(--text-muted, #94a3b8);">Belum ada data mata kuliah.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layout>

