{{--
    Rekap Nilai Mahasiswa (Rapor Belajar) — Poin 7 Milestone M3
    Menampilkan daftar seluruh tugas di satu mata kuliah beserta
    status pengumpulan, nilai, dan feedback dosen.

    Variabel dari controller:
    - $course      : Model Course (mata kuliah)
    - $assignments : Collection Assignment (tugas published, dengan relasi submissions.grade)
    - $stats       : Object statistik pribadi (avg, total_assignments, graded_count, total_score, total_max_score)
--}}
<x-layout :title="'Nilai Saya — ' . $course->name . ' — Kampus LMS'">
    <div class="page-container">
        {{-- Breadcrumb --}}
        <nav class="breadcrumb">
            <a href="{{ route('mahasiswa.courses.index') }}" class="breadcrumb-link">Mata Kuliah</a>
            <span class="breadcrumb-separator">›</span>
            <a href="{{ route('mahasiswa.courses.show', $course) }}" class="breadcrumb-link">{{ $course->name }}</a>
            <span class="breadcrumb-separator">›</span>
            <span class="breadcrumb-current">Nilai Saya</span>
        </nav>

        {{-- Header --}}
        <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 1rem; padding: 1.75rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <span style="font-size: 0.8rem; font-weight: 700; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 0.25rem 0.6rem; border-radius: 0.25rem;">
                        {{ $course->code }} — {{ $course->name }}
                    </span>
                    <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main, #f8fafc); margin: 0.5rem 0 0.25rem 0;">
                        📊 Nilai Saya
                    </h1>
                    <p style="font-size: 0.9rem; color: var(--text-muted, #94a3b8); margin: 0;">
                        Rapor capaian belajar Anda di mata kuliah ini.
                    </p>
                </div>
                <div>
                    <a href="{{ route('mahasiswa.courses.show', $course) }}" class="btn btn-secondary">
                        ← Kembali ke Detail MK
                    </a>
                </div>
            </div>
        </div>

        {{-- Statistik Capaian Pribadi --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            {{-- Rata-rata Nilai --}}
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">🎯</div>
                <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Rata-rata Nilai</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: #818cf8; margin-top: 0.25rem;">
                    {{ $stats->avg !== null ? number_format($stats->avg, 2) : '—' }}
                </div>
            </div>

            {{-- Total Skor / Skor Maks --}}
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">📝</div>
                <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Total Perolehan</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: #10b981; margin-top: 0.25rem;">
                    @if($stats->graded_count > 0)
                        {{ number_format($stats->total_score, 0) }}<span style="font-size: 0.9rem; color: var(--text-muted, #94a3b8);">/{{ number_format($stats->total_max_score, 0) }}</span>
                    @else
                        —
                    @endif
                </div>
            </div>

            {{-- Tugas Dinilai --}}
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">✅</div>
                <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Tugas Dinilai</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: var(--text-main, #f8fafc); margin-top: 0.25rem;">
                    {{ $stats->graded_count }}<span style="font-size: 0.9rem; color: var(--text-muted, #94a3b8);">/{{ $stats->total_assignments }}</span>
                </div>
            </div>

            {{-- Persentase Keseluruhan --}}
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">📈</div>
                <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Persentase</div>
                @php
                    $percentage = $stats->total_max_score > 0 ? round(($stats->total_score / $stats->total_max_score) * 100, 1) : null;
                @endphp
                <div style="font-size: 1.6rem; font-weight: 800; color: {{ $percentage !== null ? ($percentage >= 75 ? '#10b981' : ($percentage >= 50 ? '#eab308' : '#f87171')) : 'var(--text-muted)' }}; margin-top: 0.25rem;">
                    {{ $percentage !== null ? $percentage . '%' : '—' }}
                </div>
            </div>
        </div>

        {{-- Tabel Rapor Tugas --}}
        <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 1rem; padding: 1.5rem; overflow: hidden;">
            <h2 style="font-size: 1.2rem; font-weight: 700; color: var(--text-main, #f8fafc); margin: 0 0 1.25rem 0;">
                📋 Rincian Nilai per Tugas
                <span style="font-size: 0.82rem; font-weight: 600; padding: 0.15rem 0.55rem; background: rgba(99, 102, 241, 0.15); color: #818cf8; border-radius: 9999px; margin-left: 0.5rem;">
                    {{ $assignments->count() }} Tugas
                </span>
            </h2>

            @if($assignments->isEmpty())
                <div style="text-align: center; padding: 2.5rem 1rem; background: var(--bg, #0f172a); border: 1px dashed var(--card-border, #334155); border-radius: 0.75rem; color: var(--text-muted, #94a3b8);">
                    <div style="font-size: 2rem; margin-bottom: 0.35rem;">📋</div>
                    <p style="margin: 0; font-size: 0.95rem;">Belum ada penugasan yang dipublikasikan di mata kuliah ini.</p>
                </div>
            @else
                <div style="overflow-x: auto; border-radius: 0.5rem;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: rgba(99, 102, 241, 0.08);">
                                <th style="padding: 0.85rem 1rem; text-align: center; font-size: 0.78rem; font-weight: 700; color: var(--text-muted, #94a3b8); text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid var(--card-border, #334155); width: 40px;">
                                    No
                                </th>
                                <th style="padding: 0.85rem 1rem; text-align: left; font-size: 0.78rem; font-weight: 700; color: var(--text-muted, #94a3b8); text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid var(--card-border, #334155);">
                                    Judul Tugas
                                </th>
                                <th style="padding: 0.85rem 1rem; text-align: center; font-size: 0.78rem; font-weight: 700; color: var(--text-muted, #94a3b8); text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid var(--card-border, #334155); white-space: nowrap;">
                                    Batas Waktu
                                </th>
                                <th style="padding: 0.85rem 1rem; text-align: center; font-size: 0.78rem; font-weight: 700; color: var(--text-muted, #94a3b8); text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid var(--card-border, #334155); white-space: nowrap;">
                                    Status
                                </th>
                                <th style="padding: 0.85rem 1rem; text-align: center; font-size: 0.78rem; font-weight: 700; color: var(--text-muted, #94a3b8); text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid var(--card-border, #334155); white-space: nowrap;">
                                    Nilai
                                </th>
                                <th style="padding: 0.85rem 1rem; text-align: left; font-size: 0.78rem; font-weight: 700; color: var(--text-muted, #94a3b8); text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid var(--card-border, #334155);">
                                    Catatan Dosen
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignments as $index => $assignment)
                                @php
                                    $sub   = $assignment->submissions->first(); // Sudah di-filter per user di controller
                                    $grade = $sub?->grade;
                                @endphp
                                <tr style="border-bottom: 1px solid var(--card-border, #334155); transition: background 0.15s;"
                                    onmouseover="this.style.background='rgba(99, 102, 241, 0.04)'"
                                    onmouseout="this.style.background='transparent'">
                                    {{-- Nomor --}}
                                    <td style="padding: 0.85rem 1rem; text-align: center; font-size: 0.85rem; color: var(--text-muted, #94a3b8); font-weight: 600;">
                                        {{ $index + 1 }}
                                    </td>

                                    {{-- Judul Tugas --}}
                                    <td style="padding: 0.85rem 1rem;">
                                        <a href="{{ route('mahasiswa.assignments.show', $assignment) }}"
                                           style="font-size: 0.9rem; font-weight: 700; color: var(--text-main, #f8fafc); text-decoration: none; transition: color 0.2s;"
                                           onmouseover="this.style.color='#818cf8'"
                                           onmouseout="this.style.color='var(--text-main, #f8fafc)'">
                                            {{ $assignment->title }}
                                        </a>
                                        <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); margin-top: 0.15rem;">
                                            Skor Maks: {{ $assignment->max_score }}
                                        </div>
                                    </td>

                                    {{-- Batas Waktu --}}
                                    <td style="padding: 0.85rem 1rem; text-align: center; font-size: 0.82rem; color: var(--text-muted, #94a3b8); white-space: nowrap;">
                                        {{ $assignment->due_at ? $assignment->due_at->format('d M Y, H:i') : 'Tanpa batas' }}
                                    </td>

                                    {{-- Status Pengumpulan --}}
                                    <td style="padding: 0.85rem 1rem; text-align: center;">
                                        @if(!$sub)
                                            <span style="font-size: 0.75rem; padding: 0.25rem 0.6rem; border-radius: 9999px; background: rgba(100, 116, 139, 0.15); color: #64748b; font-weight: 600; white-space: nowrap;">
                                                Belum Mengumpulkan
                                            </span>
                                        @elseif($sub->is_late)
                                            <span style="font-size: 0.75rem; padding: 0.25rem 0.6rem; border-radius: 9999px; background: rgba(248, 113, 113, 0.15); color: #f87171; font-weight: 600; white-space: nowrap;">
                                                ⚠️ Terlambat
                                            </span>
                                        @else
                                            <span style="font-size: 0.75rem; padding: 0.25rem 0.6rem; border-radius: 9999px; background: rgba(16, 185, 129, 0.15); color: #10b981; font-weight: 600; white-space: nowrap;">
                                                ✓ Tepat Waktu
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Nilai --}}
                                    <td style="padding: 0.85rem 1rem; text-align: center;">
                                        @if(!$sub)
                                            <span style="font-size: 0.85rem; color: var(--text-muted, #64748b);">—</span>
                                        @elseif(!$grade)
                                            <span style="font-size: 0.75rem; padding: 0.25rem 0.6rem; border-radius: 9999px; background: rgba(234, 179, 8, 0.15); color: #eab308; font-weight: 600; white-space: nowrap;">
                                                Menunggu Penilaian
                                            </span>
                                        @else
                                            @php
                                                $pct = $assignment->max_score > 0 ? ($grade->score / $assignment->max_score) * 100 : 0;
                                            @endphp
                                            <span style="font-size: 1rem; font-weight: 800; color: {{ $pct >= 75 ? '#10b981' : ($pct >= 50 ? '#eab308' : '#f87171') }};">
                                                {{ number_format($grade->score, 0) }}
                                            </span>
                                            <span style="font-size: 0.78rem; color: var(--text-muted, #94a3b8);">
                                                / {{ $assignment->max_score }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Catatan / Feedback Dosen --}}
                                    <td style="padding: 0.85rem 1rem; font-size: 0.85rem; color: var(--text-muted, #94a3b8); max-width: 280px;">
                                        @if($grade && $grade->feedback)
                                            <div style="background: rgba(99, 102, 241, 0.06); border-left: 3px solid #818cf8; padding: 0.5rem 0.75rem; border-radius: 0 0.35rem 0.35rem 0; color: var(--text-main, #e2e8f0); font-size: 0.82rem; line-height: 1.4;">
                                                "{{ Str::limit($grade->feedback, 120) }}"
                                            </div>
                                        @else
                                            <span style="color: var(--text-muted, #64748b); font-style: italic;">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Tombol Kembali --}}
        <div style="margin-top: 2rem;">
            <a href="{{ route('mahasiswa.courses.show', $course) }}" class="back-link">
                <span class="back-arrow">←</span>
                Kembali ke Detail Mata Kuliah
            </a>
        </div>
    </div>
</x-layout>
