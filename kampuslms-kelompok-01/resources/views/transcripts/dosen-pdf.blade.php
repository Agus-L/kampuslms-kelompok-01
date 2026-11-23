<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transkrip Pengampu — {{ $lecturer['name'] ?? 'Dosen' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 7mm 12mm 8mm 12mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt;
            line-height: 1.25;
            color: #000000;
            margin: 0;
            padding: 0;
        }

        /* Kop Dokumen */
        .kop-table { width: 100%; border-collapse: collapse; margin-bottom: 2px; }
        .kop-table td { vertical-align: middle; }
        .kop-logo { width: 60px; text-align: center; }
        .kop-text { text-align: center; padding-right: 60px; }
        .kop-kemen { font-size: 10pt; font-weight: bold; letter-spacing: 0.5px; }
        .kop-inst  { font-size: 12pt; font-weight: bold; letter-spacing: 0.5px; margin: 1px 0; }
        .kop-kampus  { font-size: 8pt; }
        .kop-kontak  { font-size: 7.5pt; }
        .kop-line { border-bottom: 2px solid #000000; margin-bottom: 5px; }

        /* Judul */
        .doc-title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .doc-subtitle {
            text-align: center;
            font-size: 8pt;
            margin-bottom: 5px;
        }

        /* Identitas Dosen */
        .id-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; font-size: 7.8pt; }
        .id-table td { padding: 1px 2px; vertical-align: top; }

        /* Judul bagian mata kuliah */
        .mk-title {
            font-size: 8.5pt;
            font-weight: bold;
            background-color: #dbeafe;
            padding: 3px 5px;
            margin-top: 7px;
            margin-bottom: 2px;
            border-left: 3px solid #1d4ed8;
        }

        /* Statistik kelas */
        .mk-stats {
            font-size: 7.5pt;
            margin-bottom: 2px;
            color: #374151;
        }

        /* Tabel mahasiswa */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            margin-bottom: 4px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000000;
            padding: 2px 4px;
            vertical-align: middle;
        }
        .data-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
        }
        .data-table tfoot td {
            background-color: #f8fafc;
            font-weight: bold;
        }

        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .text-left   { text-align: left; }
        .font-bold   { font-weight: bold; }
        .lulus-yes   { color: #15803d; }
        .lulus-no    { color: #b91c1c; }

        /* Footer */
        .footer-table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 7.8pt; }
        .footer-table td { vertical-align: top; }
        .sign-box { text-align: center; width: 250px; float: right; }
    </style>
</head>
<body>

@php
    $indoMonths = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $currentDate = now()->format('j') . ' ' . $indoMonths[(int)now()->format('n')] . ' ' . now()->format('Y');
@endphp

{{-- KOP RESMI --}}
<table class="kop-table">
    <tr>
        <td class="kop-logo">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 110" width="50" height="56">
                <path d="M 50,25 C 42,10 25,8 15,22 C 5,36 12,58 24,70 C 32,78 42,82 50,85 C 58,82 68,78 76,70 C 88,58 95,36 85,22 C 75,8 58,10 50,25 Z" fill="none" stroke="#0072bc" stroke-width="5" />
                <path d="M 50,35 C 44,22 32,20 25,30 C 18,40 22,55 30,65 C 38,72 45,75 50,78 C 55,75 62,72 70,65 C 78,55 82,40 75,30 C 68,20 56,22 50,35 Z" fill="none" stroke="#0072bc" stroke-width="4" />
                <path d="M 50,15 C 46,25 43,35 45,45 C 47,52 50,56 50,60 C 50,56 53,52 55,45 C 57,35 54,25 50,15 Z" fill="#fdb813" />
                <path d="M 50,25 C 48,32 46,38 48,44 C 49,48 50,51 50,54 C 50,51 51,48 52,44 C 54,38 52,32 50,25 Z" fill="#f39200" />
                <path d="M 20,86 C 30,96 70,96 80,86" fill="none" stroke="#0072bc" stroke-width="4" stroke-linecap="round" />
                <path d="M 28,94 C 36,102 64,102 72,94" fill="none" stroke="#0072bc" stroke-width="4" stroke-linecap="round" />
                <path d="M 36,101 C 42,107 58,107 64,101" fill="none" stroke="#0072bc" stroke-width="3.5" stroke-linecap="round" />
            </svg>
        </td>
        <td class="kop-text">
            <div class="kop-kemen">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</div>
            <div class="kop-inst">INSTITUT TEKNOLOGI KALIMANTAN</div>
            <div class="kop-kampus">Kampus ITK Karang Joang, Balikpapan 76127</div>
            <div class="kop-kontak">Telepon (0542) 8530801 Faksimile (0542) 8530800 Surat elektronik : humas@itk.ac.id laman : www.itk.ac.id</div>
        </td>
    </tr>
</table>
<div class="kop-line"></div>

{{-- JUDUL --}}
<div class="doc-title">LAPORAN TRANSKRIP NILAI PENGAMPU MATA KULIAH</div>
<div class="doc-subtitle">Rekapitulasi Nilai Seluruh Mahasiswa pada Seluruh Mata Kuliah yang Diampu</div>

{{-- IDENTITAS DOSEN --}}
<table class="id-table">
    <tr>
        <td style="width:14%;">Nama Dosen</td>
        <td style="width:1%;">:</td>
        <td style="width:35%;"><strong>{{ $lecturer['name'] ?? '-' }}</strong></td>
        <td style="width:18%;">NIP / NIDN</td>
        <td style="width:1%;">:</td>
        <td>{{ $lecturer['nim_nip'] ?? '-' }}</td>
    </tr>
    <tr>
        <td>Total MK Diampu</td>
        <td>:</td>
        <td>{{ $total_courses }} Mata Kuliah</td>
        <td>Total Mahasiswa</td>
        <td>:</td>
        <td>{{ collect($courses)->sum('total_students') }} Mahasiswa</td>
    </tr>
    <tr>
        <td>Tahun Akademik</td>
        <td>:</td>
        <td>{{ $academic_period ?? config('academic.current_period', '2025/2026 Genap') }}</td>
        <td>Tanggal Cetak</td>
        <td>:</td>
        <td>{{ $currentDate }}</td>
    </tr>
</table>

{{-- TABEL PER MATA KULIAH --}}
@forelse($courses as $courseIdx => $course)
    <div class="mk-title">
        {{ $courseIdx + 1 }}. {{ $course['code'] }} — {{ $course['name'] }} ({{ $course['sks'] }} SKS)
    </div>
    <div class="mk-stats">
        Jumlah Mahasiswa: <strong>{{ $course['total_students'] }}</strong> &nbsp;|&nbsp;
        Rata-rata Nilai: <strong>{{ $course['avg_score'] !== null ? number_format($course['avg_score'], 2) : '—' }} ({{ $course['avg_grade'] }})</strong> &nbsp;|&nbsp;
        Lulus: <strong>{{ $course['passed_count'] }}</strong> dari {{ $course['total_students'] }} mahasiswa ({{ $course['pass_rate'] }}%)
    </div>

    @if(count($course['students']) > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:4%;">No</th>
                    <th style="width:14%;">NIM</th>
                    <th style="width:34%;">Nama Mahasiswa</th>
                    <th style="width:11%;">Nilai Akhir</th>
                    <th style="width:7%;">Huruf</th>
                    <th style="width:10%;">Status</th>
                    <th style="width:7%;">Hadir</th>
                    <th style="width:6%;">Tepat</th>
                    <th style="width:7%;">Terlambat</th>
                </tr>
            </thead>
            <tbody>
                @foreach($course['students'] as $i => $student)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td class="text-center">{{ $student['nim_nip'] }}</td>
                        <td>{{ $student['name'] }}</td>
                        <td class="text-center font-bold">
                            {{ $student['final_score'] !== null ? number_format($student['final_score'], 2) : '—' }}
                        </td>
                        <td class="text-center font-bold {{ $student['is_passed'] ? 'lulus-yes' : 'lulus-no' }}">
                            {{ $student['letter_grade'] }}
                        </td>
                        <td class="text-center {{ $student['is_passed'] ? 'lulus-yes' : 'lulus-no' }}">
                            {{ $student['final_score'] === null ? 'Belum Dinilai' : ($student['is_passed'] ? 'Lulus' : 'Tidak Lulus') }}
                        </td>
                        <td class="text-center">{{ $student['attendance_rate'] }}%</td>
                        <td class="text-center">{{ $student['on_time_count'] }}</td>
                        <td class="text-center">{{ $student['late_count'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right" style="padding-right: 6px;">Rata-rata Kelas :</td>
                    <td class="text-center">{{ $course['avg_score'] !== null ? number_format($course['avg_score'], 2) : '—' }}</td>
                    <td class="text-center">{{ $course['avg_grade'] }}</td>
                    <td class="text-center">{{ $course['passed_count'] }}/{{ $course['total_students'] }} Lulus</td>
                    <td colspan="3" class="text-center">{{ $course['pass_rate'] }}% Kelulusan</td>
                </tr>
            </tfoot>
        </table>
    @else
        <div style="font-size: 7.5pt; color: #6b7280; padding: 3px 5px; font-style: italic;">
            Belum ada mahasiswa terdaftar.
        </div>
    @endif
@empty
    <div style="text-align: center; padding: 10px; font-style: italic; color: #6b7280;">
        Tidak ada mata kuliah yang diampu.
    </div>
@endforelse

{{-- FOOTER & TANDA TANGAN --}}
<table class="footer-table">
    <tr>
        <td style="width: 55%; padding-top: 10px; font-style: italic; color: #333333;">
            Laporan ini diterbitkan secara resmi oleh sistem KampusLMS untuk keperluan dokumentasi akademik.
        </td>
        <td style="width: 45%;">
            <div class="sign-box">
                <div>Balikpapan, {{ $currentDate }}</div>
                <div>Dosen Pengampu,</div>
                <div style="height: 38px;"></div>
                <div style="font-weight: bold; text-decoration: underline;">{{ $lecturer['name'] ?? 'Dosen Pengampu' }}</div>
                <div style="font-size: 7.5pt;">NIP/NIDN. {{ $lecturer['nim_nip'] ?? '-' }}</div>
            </div>
        </td>
    </tr>
</table>

</body>
</html>

