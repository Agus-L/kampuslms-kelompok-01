{{--
    Rekap Nilai Kelas (Gradebook/Buku Nilai) — Poin 7 Milestone M3
    Menampilkan tabel matriks seluruh mahasiswa vs seluruh tugas
    di satu mata kuliah yang diampu dosen.

    Variabel dari controller:
    - $course      : Model Course (mata kuliah)
    - $assignments : Collection Assignment (semua tugas di MK ini)
    - $students    : Collection User (mahasiswa terdaftar, dengan relasi submissions.grade)
    - $stats       : Object statistik kelas (avg, max, min, graded_count, total_students)
--}}
<x-layout :title="'Rekap Nilai — ' . $course->name . ' — Kampus LMS'">
    <div class="page-container">
        {{-- Breadcrumb --}}
        <nav class="breadcrumb">
            <a href="{{ route('dosen.courses.index') }}" class="breadcrumb-link">Mata Kuliah</a>
            <span class="breadcrumb-separator">›</span>
            <a href="{{ route('dosen.courses.show', $course) }}" class="breadcrumb-link">{{ $course->name }}</a>
            <span class="breadcrumb-separator">›</span>
            <span class="breadcrumb-current">Rekap Nilai</span>
        </nav>

        {{-- Flash message --}}
        @if (session('success'))
            <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #10b981; padding: 0.85rem 1.25rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                ✓ {{ session('success') }}
            </div>
        @endif

        {{-- Header --}}
        <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 1rem; padding: 1.75rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <span style="font-size: 0.8rem; font-weight: 700; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 0.25rem 0.6rem; border-radius: 0.25rem;">
                        {{ $course->code }} — {{ $course->name }}
                    </span>
                    <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main, #f8fafc); margin: 0.5rem 0 0.25rem 0;">
                        📊 Rekap Nilai Kelas
                    </h1>
                    <p style="font-size: 0.9rem; color: var(--text-muted, #94a3b8); margin: 0;">
                        Buku nilai lengkap seluruh mahasiswa dan penugasan pada mata kuliah ini.
                    </p>
                </div>
                <div>
                    <a href="{{ route('dosen.courses.show', $course) }}" class="btn btn-secondary">
                        ← Kembali ke Detail MK
                    </a>
                </div>
            </div>
        </div>

        {{-- Statistik Kelas --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            {{-- Rata-rata Kelas --}}
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">📈</div>
                <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Rata-rata Kelas</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: #818cf8; margin-top: 0.25rem;">
                    {{ $stats->avg !== null ? number_format($stats->avg, 2) : '—' }}
                </div>
            </div>

            {{-- Nilai Tertinggi --}}
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">🏆</div>
                <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Nilai Tertinggi</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: #10b981; margin-top: 0.25rem;">
                    {{ $stats->max !== null ? number_format($stats->max, 2) : '—' }}
                </div>
            </div>

            {{-- Nilai Terendah --}}
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">📉</div>
                <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Nilai Terendah</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: #f87171; margin-top: 0.25rem;">
                    {{ $stats->min !== null ? number_format($stats->min, 2) : '—' }}
                </div>
            </div>

            {{-- Total Penilaian --}}
            <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">👥</div>
                <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Mahasiswa</div>
                <div style="font-size: 1.6rem; font-weight: 800; color: var(--text-main, #f8fafc); margin-top: 0.25rem;">
                    {{ $stats->total_students }}
                </div>
            </div>
        </div>

        {{-- Tabel Matriks Nilai --}}
        <div style="background: var(--card-bg, #1e293b); border: 1px solid var(--card-border, #334155); border-radius: 1rem; padding: 1.5rem; overflow: hidden;">
            <h2 style="font-size: 1.2rem; font-weight: 700; color: var(--text-main, #f8fafc); margin: 0 0 1.25rem 0;">
                📋 Tabel Matriks Nilai
                <span style="font-size: 0.82rem; font-weight: 600; padding: 0.15rem 0.55rem; background: rgba(99, 102, 241, 0.15); color: #818cf8; border-radius: 9999px; margin-left: 0.5rem;">
                    {{ $students->count() }} Mahasiswa · {{ $assignments->count() }} Tugas
                </span>
            </h2>

            @if($assignments->isEmpty())
                <div style="text-align: center; padding: 2.5rem 1rem; background: var(--bg, #0f172a); border: 1px dashed var(--card-border, #334155); border-radius: 0.75rem; color: var(--text-muted, #94a3b8);">
                    <div style="font-size: 2rem; margin-bottom: 0.35rem;">📋</div>
                    <p style="margin: 0; font-size: 0.95rem;">Belum ada penugasan yang dibuat untuk mata kuliah ini.</p>
                    <a href="{{ route('dosen.courses.assignments.index', $course) }}" style="display: inline-block; margin-top: 0.75rem; color: var(--primary-light, #818cf8); font-weight: 600; font-size: 0.88rem;">
                        Buat tugas pertama →
                    </a>
                </div>
            @elseif($students->isEmpty())
                <div style="text-align: center; padding: 2.5rem 1rem; background: var(--bg, #0f172a); border: 1px dashed var(--card-border, #334155); border-radius: 0.75rem; color: var(--text-muted, #94a3b8);">
                    <div style="font-size: 2rem; margin-bottom: 0.35rem;">👥</div>
                    <p style="margin: 0; font-size: 0.95rem;">Belum ada mahasiswa yang terdaftar di mata kuliah ini.</p>
                </div>
            @else
                <div style="overflow-x: auto; border-radius: 0.5rem;">
                    <table style="width: 100%; border-collapse: collapse; min-width: {{ 320 + ($assignments->count() * 130) }}px;">
                        <thead>
                            <tr style="background: rgba(99, 102, 241, 0.08);">
                                <th style="padding: 0.85rem 1rem; text-align: center; font-size: 0.78rem; font-weight: 700; color: var(--text-muted, #94a3b8); text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid var(--card-border, #334155); white-space: nowrap; width: 40px;">
                                    No
                                </th>
                                <th style="padding: 0.85rem 1rem; text-align: left; font-size: 0.78rem; font-weight: 700; color: var(--text-muted, #94a3b8); text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid var(--card-border, #334155); min-width: 180px;">
                                    Mahasiswa
                                </th>
                                @foreach($assignments as $assignment)
                                    <th style="padding: 0.85rem 0.75rem; text-align: center; font-size: 0.78rem; font-weight: 700; color: var(--text-muted, #94a3b8); border-bottom: 2px solid var(--card-border, #334155); min-width: 110px;">
                                        <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 120px; margin: 0 auto;" title="{{ $assignment->title }}">
                                            {{ Str::limit($assignment->title, 18) }}
                                        </div>
                                        <div style="font-size: 0.7rem; font-weight: 500; color: var(--text-muted, #64748b); margin-top: 0.15rem;">
                                            Maks: {{ $assignment->max_score }}
                                        </div>
                                    </th>
                                @endforeach
                                <th style="padding: 0.85rem 1rem; text-align: center; font-size: 0.78rem; font-weight: 700; color: #818cf8; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid var(--card-border, #334155); white-space: nowrap; background: rgba(99, 102, 241, 0.05);">
                                    Rata-rata
                                </th>
                                <th style="padding: 0.85rem 1rem; text-align: center; font-size: 0.78rem; font-weight: 700; color: var(--text-muted, #94a3b8); text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid var(--card-border, #334155); white-space: nowrap;">
                                    Transkrip
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                                @php
                                    $totalScore  = 0;
                                    $gradedCount = 0;
                                @endphp
                                <tr style="border-bottom: 1px solid var(--card-border, #334155); transition: background 0.15s;"
                                    onmouseover="this.style.background='rgba(99, 102, 241, 0.04)'"
                                    onmouseout="this.style.background='transparent'">
                                    {{-- Nomor --}}
                                    <td style="padding: 0.85rem 1rem; text-align: center; font-size: 0.85rem; color: var(--text-muted, #94a3b8); font-weight: 600;">
                                        {{ $index + 1 }}
                                    </td>

                                    {{-- Nama & Email --}}
                                    <td style="padding: 0.85rem 1rem;">
                                        <div style="font-size: 0.9rem; font-weight: 700; color: var(--text-main, #f8fafc);">
                                            {{ $student->name }}
                                        </div>
                                        <div style="font-size: 0.78rem; color: var(--text-muted, #94a3b8); margin-top: 0.15rem;">
                                            {{ $student->nim_nip ?? $student->email }}
                                        </div>
                                    </td>

                                    {{-- Nilai per Tugas --}}
                                    @foreach($assignments as $assignment)
                                        @php
                                            $sub = $student->submissions->firstWhere('assignment_id', $assignment->id);
                                        @endphp
                                        <td style="padding: 0.85rem 0.75rem; text-align: center;">
                                            @if(!$sub)
                                                {{-- Belum mengumpulkan --}}
                                                <span style="font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 9999px; background: rgba(100, 116, 139, 0.15); color: #64748b; font-weight: 600;">
                                                    —
                                                </span>
                                            @elseif(!$sub->grade)
                                                {{-- Sudah mengumpulkan, tapi belum dinilai --}}
                                                <span style="font-size: 0.72rem; padding: 0.2rem 0.5rem; border-radius: 9999px; background: rgba(234, 179, 8, 0.15); color: #eab308; font-weight: 600; white-space: nowrap;">
                                                    Belum Dinilai
                                                </span>
                                            @else
                                                {{-- Sudah dinilai --}}
                                                @php
                                                    $totalScore  += $sub->grade->score;
                                                    $gradedCount++;
                                                    $percentage = $assignment->max_score > 0 ? ($sub->grade->score / $assignment->max_score) * 100 : 0;
                                                @endphp
                                                <span style="font-size: 0.9rem; font-weight: 800; color: {{ $percentage >= 75 ? '#10b981' : ($percentage >= 50 ? '#eab308' : '#f87171') }};">
                                                    {{ number_format($sub->grade->score, 0) }}
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach

                                    {{-- Kolom Rata-rata --}}
                                    <td style="padding: 0.85rem 1rem; text-align: center; background: rgba(99, 102, 241, 0.05);">
                                        @if($gradedCount > 0)
                                            @php $avg = round($totalScore / $gradedCount, 2); @endphp
                                            <span style="font-size: 1rem; font-weight: 800; color: #818cf8;">
                                                {{ number_format($avg, 2) }}
                                            </span>
                                        @else
                                            <span style="font-size: 0.85rem; color: var(--text-muted, #64748b);">—</span>
                                        @endif
                                    </td>

                                    {{-- Kolom Transkrip (Minggu 12) --}}
                                    <td style="padding: 0.85rem 1rem; text-align: center; white-space: nowrap;">
                                        <a href="{{ route('dosen.courses.students.transcript.show', [$course, $student]) }}" class="btn" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; font-weight: 600; background: var(--card-bg); border: 1px solid var(--card-border); color: var(--text-main); text-decoration: none; border-radius: 0.375rem; display: inline-flex; align-items: center; gap: 0.3rem;" title="Lihat Transkrip Nilai & Rekap Presensi Tugas Mahasiswa">
                                            📄 Transkrip
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Legenda --}}
                <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--card-border, #334155);">
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: var(--text-muted, #94a3b8);">
                        <span style="display: inline-block; width: 12px; height: 12px; border-radius: 9999px; background: #10b981;"></span>
                        ≥ 75% (Baik)
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: var(--text-muted, #94a3b8);">
                        <span style="display: inline-block; width: 12px; height: 12px; border-radius: 9999px; background: #eab308;"></span>
                        50–74% (Cukup) / Belum Dinilai
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: var(--text-muted, #94a3b8);">
                        <span style="display: inline-block; width: 12px; height: 12px; border-radius: 9999px; background: #f87171;"></span>
                        &lt; 50% (Kurang)
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: var(--text-muted, #94a3b8);">
                        <span style="display: inline-block; width: 12px; height: 12px; border-radius: 9999px; background: #64748b;"></span>
                        — (Belum Mengumpulkan)
                    </div>
                </div>
            @endif
        </div>

        {{-- Tombol Kembali --}}
        <div style="margin-top: 2rem;">
            <a href="{{ route('dosen.courses.show', $course) }}" class="back-link">
                <span class="back-arrow">←</span>
                Kembali ke Detail Mata Kuliah
            </a>
        </div>
    </div>
</x-layout>
