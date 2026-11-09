<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transkrip Prestasi Akademik & Presensi Tugas — {{ $student['name'] ?? 'Mahasiswa' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9.5pt;
            line-height: 1.35;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }

        /* ─────────────────────────────────────────────────────────────
           KOP RESMI DOKUMEN AKADEMIK
        ───────────────────────────────────────────────────────────── */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #0f172a;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .kop-table td {
            vertical-align: middle;
        }
        .kop-logo {
            width: 65px;
            text-align: center;
        }
        .kop-logo-box {
            display: inline-block;
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: bold;
            font-size: 13pt;
            padding: 10px 8px;
            border-radius: 4px;
            letter-spacing: 1px;
        }
        .kop-text {
            text-align: center;
            padding-left: 10px;
            padding-right: 20px;
        }
        .kop-universitas {
            font-size: 13pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #0f172a;
        }
        .kop-fakultas {
            font-size: 10.5pt;
            font-weight: 700;
            color: #1e293b;
            margin-top: 1px;
            text-transform: uppercase;
        }
        .kop-alamat {
            font-size: 7.8pt;
            color: #475569;
            margin-top: 2px;
        }

        /* Judul Dokumen */
        .doc-title {
            text-align: center;
            font-size: 11.5pt;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
            margin-top: 8px;
            margin-bottom: 2px;
            letter-spacing: 0.5px;
        }
        .doc-subtitle {
            text-align: center;
            font-size: 8pt;
            color: #64748b;
            margin-bottom: 12px;
        }

        /* ─────────────────────────────────────────────────────────────
           IDENTITAS MAHASISWA & MATA KULIAH
        ───────────────────────────────────────────────────────────── */
        .identity-box {
            width: 100%;
            margin-bottom: 10px;
            font-size: 8.5pt;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            padding: 6px 8px;
        }
        .identity-table {
            width: 100%;
            border-collapse: collapse;
        }
        .identity-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .id-label {
            width: 18%;
            color: #475569;
            font-weight: 700;
        }
        .id-sep {
            width: 2%;
            text-align: center;
            color: #64748b;
        }
        .id-val {
            width: 30%;
            color: #0f172a;
            font-weight: 600;
        }

        /* ─────────────────────────────────────────────────────────────
           SECTION HEADER
        ───────────────────────────────────────────────────────────── */
        .section-header {
            font-size: 9pt;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-bottom: 1.5px solid #94a3b8;
            padding-bottom: 2px;
            margin-top: 10px;
            margin-bottom: 6px;
        }

        /* ─────────────────────────────────────────────────────────────
           TABEL DATA (NILAI & PRESENSI)
        ───────────────────────────────────────────────────────────── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 10px;
        }
        .data-table th, .data-table td {
            border: 1px solid #94a3b8;
            padding: 4px 5px;
            vertical-align: middle;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: 800;
            text-align: center;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-left   { text-align: left; }
        .text-right  { text-align: right; }
        .font-bold   { font-weight: 700; }

        /* ─────────────────────────────────────────────────────────────
           BADGE STATUS PRESENSI & EVALUASI
        ───────────────────────────────────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 7pt;
            font-weight: 700;
            border-radius: 3px;
            text-align: center;
        }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-danger  { background-color: #fee2e2; color: #991b1b; }
        .badge-info    { background-color: #e0e7ff; color: #3730a3; }

        /* ─────────────────────────────────────────────────────────────
           KOTAK RINGKASAN PRESTASI (IPK & PRESENSI)
        ───────────────────────────────────────────────────────────── */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 8pt;
        }
        .summary-card {
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            border-radius: 4px;
            padding: 6px 8px;
            text-align: center;
        }
        .summary-card-title {
            font-size: 7pt;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
        }
        .summary-card-value {
            font-size: 11pt;
            font-weight: 800;
            color: #0f172a;
            margin-top: 2px;
        }

        /* ─────────────────────────────────────────────────────────────
           BLOK PENGESAHAN & TANDA TANGAN (SIGNATURE BLOCK)
        ───────────────────────────────────────────────────────────── */
        .sign-container {
            width: 100%;
            margin-top: 14px;
            page-break-inside: avoid;
        }
        .sign-table {
            width: 100%;
            border-collapse: collapse;
        }
        .sign-table td {
            vertical-align: top;
            font-size: 8pt;
        }
        .qr-box {
            display: inline-block;
            border: 1px dashed #64748b;
            background-color: #f8fafc;
            width: 72px;
            height: 72px;
            text-align: center;
            vertical-align: middle;
            font-size: 6.5pt;
            color: #475569;
            padding: 4px;
            border-radius: 4px;
        }

        /* Catatan Kaki */
        .footer-note {
            font-size: 7pt;
            color: #64748b;
            border-top: 1px solid #cbd5e1;
            padding-top: 3px;
            margin-top: 10px;
            text-align: center;
            font-style: italic;
        }
    </style>
</head>
<body>

    @php
        // Konfigurasi Bulan Bahasa Indonesia
        $indoMonths = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $currentDate = now()->format('j') . ' ' . $indoMonths[(int)now()->format('n')] . ' ' . now()->format('Y');

        // Deteksi Tipe Transkrip: Kumulatif (Mahasiswa/Admin) vs Spesifik Mata Kuliah (Dosen)
        $isCumulative = isset($courses) && is_array($courses);
    @endphp

    {{-- ═════════════════════════════════════════════════════════════════
         BAGIAN I: KOP RESMI & IDENTITAS DOKUMEN
    ═════════════════════════════════════════════════════════════════ --}}
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                <div class="kop-logo-box">LMS</div>
            </td>
            <td class="kop-text">
                <div class="kop-universitas">KAMPUS INSTITUT TEKNOLOGI & ILMU KOMPUTER</div>
                <div class="kop-fakultas">FAKULTAS TEKNOLOGI INFORMASI DAN INDUSTRI</div>
                <div class="kop-alamat">
                    Sistem Pembelajaran Digital Terintegrasi (KampusLMS) &bull; Kampus Utama Jl. Soekarno-Hatta Km. 15<br>
                    Laman Resmi: https://lms.kampus.ac.id &bull; Surel: akademik@kampus.ac.id
                </div>
            </td>
        </tr>
    </table>

    <div class="doc-title">
        @if($isCumulative)
            TRANSKRIP PRESTASI AKADEMIK & REKAPITULASI PRESENSI TUGAS
        @else
            LAPORAN PRESTASI AKADEMIK & REKAP PRESENSI TUGAS MATA KULIAH
        @endif
    </div>
    <div class="doc-subtitle">
        Dokumen Evaluasi Akademik Resmi Terpadu &bull; Diterbitkan melalui Sistem Pembelajaran Digital KampusLMS
    </div>

    {{-- Biodata Mahasiswa --}}
    <div class="identity-box">
        <table class="identity-table">
            <tr>
                <td class="id-label">Nama Lengkap</td>
                <td class="id-sep">:</td>
                <td class="id-val">{{ $student['name'] ?? '-' }}</td>

                <td class="id-label">Tanggal Cetak</td>
                <td class="id-sep">:</td>
                <td class="id-val">{{ $currentDate }}</td>
            </tr>
            <tr>
                <td class="id-label">Nomor Induk (NIM)</td>
                <td class="id-sep">:</td>
                <td class="id-val">{{ $student['nim_nip'] ?? '-' }}</td>

                <td class="id-label">Program Studi</td>
                <td class="id-sep">:</td>
                <td class="id-val">Teknik Informatika</td>
            </tr>
            <tr>
                <td class="id-label">Surel Mahasiswa</td>
                <td class="id-sep">:</td>
                <td class="id-val">{{ $student['email'] ?? '-' }}</td>

                <td class="id-label">Tahun Akademik</td>
                <td class="id-sep">:</td>
                <td class="id-val">{{ now()->format('Y') }}/{{ (int)now()->format('Y') + 1 }} Ganjil</td>
            </tr>
            @if(!$isCumulative && isset($course))
            <tr>
                <td class="id-label">Mata Kuliah</td>
                <td class="id-sep">:</td>
                <td class="id-val"><strong>{{ $course['code'] }} — {{ $course['name'] }}</strong> ({{ $course['sks'] }} SKS)</td>

                <td class="id-label">Dosen Pengampu</td>
                <td class="id-sep">:</td>
                <td class="id-val">{{ $course['lecturer_name'] ?? '-' }}</td>
            </tr>
            @endif
        </table>
    </div>

    @if($isCumulative)
        {{-- ═════════════════════════════════════════════════════════════════
             SKENARIO A: TRANSKRIP KUMULATIF SELURUH MATA KULIAH (MAHASISWA & ADMIN)
        ═════════════════════════════════════════════════════════════════ --}}

        {{-- BAGIAN II: TABEL PRESTASI NILAI AKADEMIK --}}
        <div class="section-header">I. Prestasi Nilai Akademik Kumulatif</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 12%;">Kode MK</th>
                    <th style="width: 33%;">Nama Mata Kuliah</th>
                    <th style="width: 7%;">SKS</th>
                    <th style="width: 12%;">Nilai Akhir</th>
                    <th style="width: 10%;">Huruf Mutu</th>
                    <th style="width: 10%;">Bobot Indeks</th>
                    <th style="width: 11%;">Angka Mutu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($courses as $idx => $c)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="text-center font-bold">{{ $c['code'] }}</td>
                        <td>{{ $c['name'] }}</td>
                        <td class="text-center">{{ $c['sks'] }}</td>
                        <td class="text-center">
                            {{ $c['final_score'] !== null ? number_format($c['final_score'], 2) : '—' }}
                        </td>
                        <td class="text-center font-bold">
                            {{ $c['letter_grade'] }}
                        </td>
                        <td class="text-center">{{ number_format($c['grade_point'], 2) }}</td>
                        <td class="text-center font-bold">{{ number_format($c['quality_points'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 10px; color: #64748b;">
                            Belum ada mata kuliah yang terdaftar atau dinilai.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($courses) > 0)
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: 800;">
                    <td colspan="3" class="text-right" style="padding-right: 8px;">TOTAL KUMULATIF:</td>
                    <td class="text-center">{{ $summary['total_credits_taken'] ?? 0 }}</td>
                    <td colspan="3" class="text-right" style="padding-right: 8px;">TOTAL ANGKA MUTU:</td>
                    <td class="text-center">{{ number_format($summary['total_quality_points'] ?? 0, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>

        {{-- BAGIAN III: RINGKASAN PRESTASI AKADEMIK --}}
        <table class="summary-table">
            <tr>
                <td style="width: 20%;">
                    <div class="summary-card">
                        <div class="summary-card-title">SKS Diambil</div>
                        <div class="summary-card-value" style="color: #0284c7;">{{ $summary['total_credits_taken'] ?? 0 }} SKS</div>
                    </div>
                </td>
                <td style="width: 20%;">
                    <div class="summary-card">
                        <div class="summary-card-title">SKS Lulus</div>
                        <div class="summary-card-value" style="color: #16a34a;">{{ $summary['total_credits_passed'] ?? 0 }} SKS</div>
                    </div>
                </td>
                <td style="width: 20%;">
                    <div class="summary-card">
                        <div class="summary-card-title">Total Mutu</div>
                        <div class="summary-card-value" style="color: #7c3aed;">{{ number_format($summary['total_quality_points'] ?? 0, 2) }}</div>
                    </div>
                </td>
                <td style="width: 20%;">
                    <div class="summary-card">
                        <div class="summary-card-title">IPK Kumulatif</div>
                        <div class="summary-card-value" style="color: #ea580c;">{{ number_format($summary['gpa'] ?? 0, 2) }}</div>
                    </div>
                </td>
                <td style="width: 20%;">
                    <div class="summary-card">
                        <div class="summary-card-title">Predikat</div>
                        <div class="summary-card-value" style="font-size: 8.5pt; color: #0f172a; margin-top: 4px;">{{ $summary['predicate'] ?? '—' }}</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- BAGIAN IV: TABEL REKAPITULASI PRESENSI TUGAS --}}
        <div class="section-header">II. Rekapitulasi Presensi & Kepatuhan Pengumpulan Tugas</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Kode MK</th>
                    <th style="width: 32%;">Mata Kuliah</th>
                    <th style="width: 11%;">Tugas Wajib</th>
                    <th style="width: 11%;">Tepat Waktu</th>
                    <th style="width: 10%;">Terlambat</th>
                    <th style="width: 10%;">Alpa/Belum</th>
                    <th style="width: 14%;">% Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                @forelse($courses as $c)
                    @php
                        $att = $c['attendance'];
                        $rate = (float) $att['attendance_rate'];
                        $badgeClass = $rate >= 85 ? 'badge-success' : ($rate >= 75 ? 'badge-warning' : 'badge-danger');
                    @endphp
                    <tr>
                        <td class="text-center font-bold">{{ $c['code'] }}</td>
                        <td>{{ $c['name'] }}</td>
                        <td class="text-center font-bold">{{ $att['total_mandatory'] }}</td>
                        <td class="text-center" style="color: #166534;">{{ $att['on_time_count'] }}</td>
                        <td class="text-center" style="color: #92400e;">{{ $att['late_count'] }}</td>
                        <td class="text-center" style="color: #991b1b;">{{ $att['missing_count'] }}</td>
                        <td class="text-center">
                            <span class="badge {{ $badgeClass }}">
                                {{ $rate }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 8px; color: #64748b;">
                            Tidak ada data presensi tugas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($courses) > 0)
            <tfoot>
                @php
                    $overallRate = (float)($summary['overall_attendance_rate'] ?? 100);
                    $overallBadge = $overallRate >= 85 ? 'badge-success' : ($overallRate >= 75 ? 'badge-warning' : 'badge-danger');
                @endphp
                <tr style="background-color: #f1f5f9; font-weight: 800;">
                    <td colspan="2" class="text-right" style="padding-right: 8px;">RATA-RATA PRESENSI TUGAS KAMPUS:</td>
                    <td class="text-center">{{ $summary['total_assignments'] ?? 0 }}</td>
                    <td class="text-center" style="color: #166534;">{{ $summary['on_time_count'] ?? 0 }}</td>
                    <td class="text-center" style="color: #92400e;">{{ $summary['late_count'] ?? 0 }}</td>
                    <td class="text-center" style="color: #991b1b;">{{ $summary['missing_count'] ?? 0 }}</td>
                    <td class="text-center">
                        <span class="badge {{ $overallBadge }}">
                            {{ $overallRate }}%
                        </span>
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>

        @if(($summary['overall_attendance_rate'] ?? 100) < 75)
            <div style="background-color: #fef2f2; border-left: 3px solid #ef4444; padding: 5px 8px; font-size: 7.5pt; color: #991b1b; margin-bottom: 8px;">
                <strong>PERINGATAN AKADEMIK:</strong> Persentase kehadiran pengumpulan tugas kumulatif berada di bawah ambang batas kelayakan minimum (&lt; 75%).
            </div>
        @endif

    @else
        {{-- ═════════════════════════════════════════════════════════════════
             SKENARIO B: TRANSKRIP KHUSUS SATU MATA KULIAH (DOSEN PENGAMPU)
        ═════════════════════════════════════════════════════════════════ --}}

        {{-- RINGKASAN NILAI MATA KULIAH --}}
        <div class="section-header">I. Capaian Nilai & Status Kelulusan Mata Kuliah</div>
        <table class="summary-table">
            <tr>
                <td style="width: 25%;">
                    <div class="summary-card">
                        <div class="summary-card-title">Nilai Akhir (0-100)</div>
                        <div class="summary-card-value" style="color: #0284c7;">
                            {{ $grade['final_score'] !== null ? number_format($grade['final_score'], 2) : '—' }}
                        </div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="summary-card">
                        <div class="summary-card-title">Huruf Mutu / Bobot</div>
                        <div class="summary-card-value" style="color: #7c3aed;">
                            {{ $grade['letter'] ?? '—' }} ({{ number_format($grade['grade_point'] ?? 0, 2) }})
                        </div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="summary-card">
                        <div class="summary-card-title">Kehadiran Tugas</div>
                        @php
                            $rate = (float)($attendance['attendance_rate'] ?? 100);
                            $badgeClass = $rate >= 85 ? 'badge-success' : ($rate >= 75 ? 'badge-warning' : 'badge-danger');
                        @endphp
                        <div class="summary-card-value">
                            <span class="badge {{ $badgeClass }}" style="font-size: 9pt;">
                                {{ $rate }}%
                            </span>
                        </div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="summary-card">
                        <div class="summary-card-title">Status Akademik</div>
                        <div class="summary-card-value" style="font-size: 9pt; color: {{ ($grade['is_passed'] ?? false) ? '#166534' : '#991b1b' }}; margin-top: 3px;">
                            {{ ($grade['is_passed'] ?? false) ? 'LULUS (MEMENUHI SYARAT)' : 'BELUM MEMENUHI' }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- RINCIAN PENILAIAN & PRESENSI SELURUH TUGAS --}}
        <div class="section-header">II. Rincian Pengumpulan & Penilaian Tugas Perkuliahan</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 33%;">Judul Penugasan</th>
                    <th style="width: 17%;">Tenggat Waktu</th>
                    <th style="width: 15%;">Waktu Kumpul</th>
                    <th style="width: 15%;">Status Presensi</th>
                    <th style="width: 15%;">Nilai (Maks)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $idx => $asg)
                    @php
                        $stClass = 'badge-danger';
                        if ($asg['status_key'] === 'on_time') $stClass = 'badge-success';
                        elseif ($asg['status_key'] === 'late') $stClass = 'badge-warning';
                        elseif ($asg['status_key'] === 'pending') $stClass = 'badge-info';
                    @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $asg['title'] }}</strong>
                            @if(!empty($asg['feedback']))
                                <div style="font-size: 7pt; color: #64748b; font-style: italic; margin-top: 2px;">
                                    Catatan Dosen: "{{ $asg['feedback'] }}"
                                </div>
                            @endif
                        </td>
                        <td class="text-center">
                            {{ $asg['due_at'] ? \Carbon\Carbon::parse($asg['due_at'])->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td class="text-center">
                            {{ $asg['submitted_at'] ? \Carbon\Carbon::parse($asg['submitted_at'])->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $stClass }}">
                                {{ $asg['status_label'] }}
                            </span>
                        </td>
                        <td class="text-center font-bold">
                            {{ $asg['score'] !== null ? number_format($asg['score'], 1) : '—' }}
                            <span style="font-size: 7pt; font-weight: normal; color: #64748b;">/ {{ $asg['max_score'] }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 10px; color: #64748b;">
                            Belum ada penugasan yang dipublikasikan pada mata kuliah ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    {{-- ═════════════════════════════════════════════════════════════════
         BAGIAN V: KOLOM VALIDASI, KEABSAHAN & TANDA TANGAN
    ═════════════════════════════════════════════════════════════════ --}}
    <div class="sign-container">
        <table class="sign-table">
            <tr>
                <td style="width: 25%; text-align: center; vertical-align: middle;">
                    <div class="qr-box">
                        <div style="font-weight: bold; margin-top: 4px;">VERIFIKASI LMS</div>
                        <div style="font-size: 16pt; margin: 4px 0;">&#x2611;</div>
                        <div style="font-size: 6pt;">ID Mahasiswa: #{{ $student['id'] ?? '0' }}</div>
                        <div style="font-size: 5.5pt; color: #15803d; font-weight: bold;">DOKUMEN VALID</div>
                    </div>
                </td>
                <td style="width: 35%;">
                    <div style="font-size: 7.5pt; color: #475569; padding-right: 15px;">
                        <strong>Catatan Keabsahan:</strong><br>
                        Dokumen transkrip dan rekap presensi tugas ini diterbitkan secara elektronik oleh Sistem KampusLMS. Data yang tertera di atas sah dan bersumber langsung dari pangkalan data akademik universitas.
                    </div>
                </td>
                <td style="width: 40%; text-align: center;">
                    <div>Balikpapan, {{ $currentDate }}</div>
                    @if(!$isCumulative && isset($course))
                        <div style="font-weight: 700; margin-top: 2px;">Dosen Pengampu Mata Kuliah,</div>
                        <div style="height: 48px;"></div>
                        <div style="font-weight: 800; text-decoration: underline;">{{ $course['lecturer_name'] ?? 'Dosen Pengampu' }}</div>
                        <div style="font-size: 7.5pt; color: #475569;">NIP/NIDN. Dosen Terdaftar</div>
                    @else
                        <div style="font-weight: 700; margin-top: 2px;">Ketua Program Studi Informatika,</div>
                        <div style="height: 48px;"></div>
                        <div style="font-weight: 800; text-decoration: underline;">Dr. Eng. Ir. Akademisi Utama, S.T., M.T.</div>
                        <div style="font-size: 7.5pt; color: #475569;">NIP. 19850101 201012 1 001</div>
                    @endif
                </td>
            </tr>
        </table>

        <div class="footer-note">
            Halaman 1 dari 1 &bull; Transkrip Akademik Resmi & Rekapitulasi Presensi Tugas Mahasiswa &bull; KampusLMS v2.4 (Build Minggu 12)
        </div>
    </div>

</body>
</html>

