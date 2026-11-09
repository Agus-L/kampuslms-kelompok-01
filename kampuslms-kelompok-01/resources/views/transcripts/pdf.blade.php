<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Kemajuan Studi/Transkrip Mata Kuliah — {{ $student['name'] ?? 'Mahasiswa' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 7mm 12mm 8mm 12mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt;
            line-height: 1.25;
            color: #000000;
            margin: 0;
            padding: 0;
        }

        /* Kop Dokumen Resmi */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .kop-table td {
            vertical-align: middle;
        }
        .kop-logo {
            width: 60px;
            text-align: center;
        }
        .kop-text {
            text-align: center;
            padding-right: 60px;
        }
        .kop-kemen {
            font-size: 10pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .kop-inst {
            font-size: 12pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin: 1px 0;
        }
        .kop-kampus {
            font-size: 8pt;
        }
        .kop-kontak {
            font-size: 7.5pt;
        }
        .kop-line {
            border-bottom: 2px solid #000000;
            margin-bottom: 5px;
        }

        /* Judul Dokumen */
        .doc-title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        /* Kotak Identitas */
        .id-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            font-size: 7.8pt;
        }
        .id-table td {
            padding: 1px 2px;
            vertical-align: top;
        }

        /* Judul Bagian */
        .section-title {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 4px;
            margin-bottom: 3px;
            color: #000000;
        }

        /* Tabel Data Transkrip */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            margin-bottom: 5px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000000;
            padding: 2.5px 4px;
            vertical-align: middle;
        }
        .data-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-left   { text-align: left; }
        .text-right  { text-align: right; }
        .font-bold   { font-weight: bold; }

        /* Footer & Tanda Tangan */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            font-size: 7.8pt;
        }
        .footer-table td {
            vertical-align: top;
        }
        .sign-box {
            text-align: center;
            width: 250px;
            float: right;
        }
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

        $isCumulative = isset($courses) && is_array($courses);

        if ($isCumulative) {
            $courseList = $courses;
            $totalSksPassed = $summary['total_credits_passed'] ?? 0;
            $ipsVal = number_format($summary['gpa'] ?? 0, 2);
            $ipkVal = number_format($summary['gpa'] ?? 0, 2);
            $overallRate = (float)($summary['overall_attendance_rate'] ?? 100);
            $overallStatus = $summary['attendance_status'] ?? 'Memenuhi Syarat';
        } else {
            // Skenario Dosen: 1 mata kuliah yang diampu
            $courseList = [
                [
                    'code' => $course['code'] ?? '-',
                    'name' => $course['name'] ?? '-',
                    'sks' => $course['sks'] ?? 0,
                    'semester' => '2025/Gn',
                    'final_score' => $grade['final_score'] ?? null,
                    'letter_grade' => $grade['letter'] ?? '—',
                    'attendance' => $attendance ?? [],
                ]
            ];
            $totalSksPassed = ($grade['is_passed'] ?? false) ? ($course['sks'] ?? 0) : 0;
            $ipsVal = $grade['final_score'] !== null ? number_format($grade['grade_point'], 2) : '—';
            $ipkVal = $grade['final_score'] !== null ? number_format($grade['grade_point'], 2) : '—';
            $overallRate = (float)($attendance['attendance_rate'] ?? 100);
            $overallStatus = $attendance['status_label'] ?? 'Memenuhi Syarat';
        }
    @endphp

    {{-- ═════════════════════════════════════════════════════════════════
         KOP RESMI DOKUMEN (PERSIS SEPERTI GAMBAR ITK)
    ═════════════════════════════════════════════════════════════════ --}}
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 110" width="50" height="56">
                    <!-- Blue outer wings -->
                    <path d="M 50,25 C 42,10 25,8 15,22 C 5,36 12,58 24,70 C 32,78 42,82 50,85 C 58,82 68,78 76,70 C 88,58 95,36 85,22 C 75,8 58,10 50,25 Z" fill="none" stroke="#0072bc" stroke-width="5" />
                    <path d="M 50,35 C 44,22 32,20 25,30 C 18,40 22,55 30,65 C 38,72 45,75 50,78 C 55,75 62,72 70,65 C 78,55 82,40 75,30 C 68,20 56,22 50,35 Z" fill="none" stroke="#0072bc" stroke-width="4" />
                    <!-- Yellow flame center -->
                    <path d="M 50,15 C 46,25 43,35 45,45 C 47,52 50,56 50,60 C 50,56 53,52 55,45 C 57,35 54,25 50,15 Z" fill="#fdb813" />
                    <path d="M 50,25 C 48,32 46,38 48,44 C 49,48 50,51 50,54 C 50,51 51,48 52,44 C 54,38 52,32 50,25 Z" fill="#f39200" />
                    <!-- Blue base pedestal -->
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

    {{-- JUDUL RESMI --}}
    <div class="doc-title">
        DAFTAR KEMAJUAN STUDI/TRANSKRIP MATA KULIAH
    </div>

    {{-- IDENTITAS MAHASISWA (2 KOLOM SEJAJAR) --}}
    <table class="id-table">
        <tr>
            <td style="width: 10%;">Nama</td>
            <td style="width: 1.5%;">:</td>
            <td style="width: 38.5%;">{{ $student['name'] ?? '-' }}</td>

            <td style="width: 22%;">SKS Diambil / SKS Lulus</td>
            <td style="width: 1.5%;">:</td>
            <td style="width: 26.5%;">
                @if($isCumulative)
                    {{ $summary['total_credits_taken'] ?? 0 }} SKS / {{ $summary['total_credits_passed'] ?? 0 }} SKS
                @else
                    {{ $course['sks'] ?? 0 }} SKS / {{ ($grade['is_passed'] ?? false) ? ($course['sks'] ?? 0) : 0 }} SKS
                @endif
            </td>
        </tr>
        <tr>
            <td>NIM</td>
            <td>:</td>
            <td>{{ $student['nim_nip'] ?? '-' }}</td>

            <td>SKS Tempuh Sekarang</td>
            <td>:</td>
            <td>
                @if($isCumulative)
                    {{ $summary['total_credits_taken'] ?? 0 }} SKS
                @else
                    {{ $course['sks'] ?? 0 }} SKS
                @endif
            </td>
        </tr>
        <tr>
            <td>Program Studi</td>
            <td>:</td>
            <td>{{ $student['prodi'] ?? 'Sistem Informasi' }}</td>

            <td>Status Perkuliahan</td>
            <td>:</td>
            <td>Aktif</td>
        </tr>
        @if(!$isCumulative && isset($course))
        <tr>
            <td>Mata Kuliah</td>
            <td>:</td>
            <td><strong>{{ $course['code'] }} — {{ $course['name'] }}</strong></td>

            <td>Dosen Pengampu</td>
            <td>:</td>
            <td>{{ $course['lecturer_name'] ?? '-' }}</td>
        </tr>
        @else
        <tr>
            <td colspan="3"></td>
            <td>Semester</td>
            <td>:</td>
            <td>4</td>
        </tr>
        @endif
    </table>

    {{-- ═════════════════════════════════════════════════════════════════
         BAGIAN I: TABEL PRESTASI NILAI AKADEMIK
         (Tanpa Bobot Indeks & Angka Mutu; Di bawahnya ada Total SKS, IPS, IPK)
    ═════════════════════════════════════════════════════════════════ --}}
    <div class="section-title">I. Prestasi Nilai Akademik</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 12%;">Kode MK</th>
                <th style="width: 46%;">Nama Mata Kuliah</th>
                <th style="width: 8%;">SKS</th>
                <th style="width: 12%;">Semester</th>
                <th style="width: 10%;">Nilai Angka</th>
                <th style="width: 8%;">Nilai</th>
            </tr>
        </thead>
        <tbody>
            @forelse($courseList as $idx => $c)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center font-bold">{{ $c['code'] }}</td>
                    <td>{{ $c['name'] }}</td>
                    <td class="text-center">{{ $c['sks'] }}</td>
                    <td class="text-center">{{ $c['semester'] ?? '2024/Gs' }}</td>
                    <td class="text-center">
                        {{ $c['final_score'] !== null ? number_format($c['final_score'], 2) : '—' }}
                    </td>
                    <td class="text-center font-bold">{{ $c['letter_grade'] ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 6px;">Belum ada mata kuliah yang dinilai.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            {{-- Bagian Total SKS, IPS, IPK di bawah tabel persis seperti contoh gambar ITK --}}
            <tr>
                <td colspan="5" class="text-right font-bold" style="background-color: #f8fafc; padding-right: 8px;">Total SKS Lulus :</td>
                <td colspan="2" class="text-center font-bold" style="background-color: #f8fafc;">{{ $totalSksPassed }} SKS</td>
            </tr>
            <tr>
                <td colspan="5" class="text-right font-bold" style="background-color: #f8fafc; padding-right: 8px;">IPS Genap 2025/2026 :</td>
                <td colspan="2" class="text-center font-bold" style="background-color: #f8fafc;">{{ $ipsVal }}</td>
            </tr>
            <tr>
                <td colspan="5" class="text-right font-bold" style="background-color: #f8fafc; padding-right: 8px;">IPK Genap 2025/2026 :</td>
                <td colspan="2" class="text-center font-bold" style="background-color: #f8fafc;">{{ $ipkVal }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- ═════════════════════════════════════════════════════════════════
         BAGIAN II: TABEL REKAPITULASI PRESENSI TUGAS
         (Tetap ada di isi PDF sesuai permintaan)
    ═════════════════════════════════════════════════════════════════ --}}
    <div class="section-title">II. Rekapitulasi Presensi & Kepatuhan Pengumpulan Tugas</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 12%;">Kode MK</th>
                <th style="width: 40%;">Nama Mata Kuliah</th>
                <th style="width: 8%;">Wajib</th>
                <th style="width: 8%;">Tepat Waktu</th>
                <th style="width: 8%;">Terlambat</th>
                <th style="width: 8%;">Alpa/Belum</th>
                <th style="width: 12%;">% Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            @if($isCumulative)
                @forelse($courses as $idx => $c)
                    @php
                        $att = $c['attendance'] ?? [];
                        $rate = (float)($att['attendance_rate'] ?? 100);
                    @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="text-center font-bold">{{ $c['code'] }}</td>
                        <td>{{ $c['name'] }}</td>
                        <td class="text-center">{{ $att['total_mandatory'] ?? 0 }}</td>
                        <td class="text-center">{{ $att['on_time_count'] ?? 0 }}</td>
                        <td class="text-center">{{ $att['late_count'] ?? 0 }}</td>
                        <td class="text-center">{{ $att['missing_count'] ?? 0 }}</td>
                        <td class="text-center font-bold">{{ $rate }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 6px;">Belum ada data presensi tugas.</td>
                    </tr>
                @endforelse
            @else
                @php
                    $rate = (float)($attendance['attendance_rate'] ?? 100);
                @endphp
                <tr>
                    <td class="text-center">1</td>
                    <td class="text-center font-bold">{{ $course['code'] }}</td>
                    <td>{{ $course['name'] }}</td>
                    <td class="text-center">{{ $attendance['total_mandatory'] ?? 0 }}</td>
                    <td class="text-center">{{ $attendance['on_time_count'] ?? 0 }}</td>
                    <td class="text-center">{{ $attendance['late_count'] ?? 0 }}</td>
                    <td class="text-center">{{ $attendance['missing_count'] ?? 0 }}</td>
                    <td class="text-center font-bold">{{ $rate }}%</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td colspan="3" class="text-right" style="padding-right: 8px;">RATA-RATA PRESENSI TUGAS :</td>
                <td colspan="4" class="text-center">{{ $overallStatus }}</td>
                <td class="text-center font-bold">{{ $overallRate }}%</td>
            </tr>
        </tfoot>
    </table>

    {{-- ═════════════════════════════════════════════════════════════════
         BAGIAN FOOTER & PENGESAHAN (PERSIS GAMBAR ITK)
    ═════════════════════════════════════════════════════════════════ --}}
    <table class="footer-table">
        <tr>
            <td style="width: 55%; padding-top: 10px; font-style: italic; color: #333333;">
                Daftar Kemajuan Studi/Transkrip Mata Kuliah ini dibuat untuk dipergunakan sebagaimana mestinya
            </td>
            <td style="width: 45%;">
                <div class="sign-box">
                    <div>Balikpapan, {{ $currentDate }}</div>
                    @if(!$isCumulative && isset($course))
                        <div>Dosen Pengampu Mata Kuliah,</div>
                        <div style="height: 38px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;">{{ $course['lecturer_name'] ?? 'Dosen Pengampu' }}</div>
                        <div style="font-size: 7.5pt;">NIP/NIDN. {{ $course['lecturer_nip'] ?? '19800101 200501 1 002' }}</div>
                    @else
                        <div>Wakil Dekan Bidang Akademik dan Kemahasiswaan</div>
                        <div style="height: 38px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;">Prof. Dr. Ir. Akademisi Utama, M.Sc.</div>
                        <div style="font-size: 7.5pt;">NIP. 19780512 200312 1 001</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
