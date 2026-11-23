<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * TranscriptService — Layanan Komputasi Nilai & Presensi Tugas Mahasiswa.
 *
 * Menerapkan prinsip Single Responsibility Principle (SRP):
 * Seluruh komputasi akademik (konversi nilai angka ke huruf, perhitungan IPK,
 * dan rekapitulasi kehadiran pengumpulan tugas) dipusatkan di service ini.
 *
 * Digunakan bersama oleh:
 * - Antarmuka Web Mahasiswa, Dosen, dan Admin
 * - Generator Berkas Ekspor PDF (Tahap 4)
 */
class TranscriptService
{
    /**
     * Mengambil dan menghitung data transkrip kumulatif seluruh mata kuliah mahasiswa
     * beserta rekapitulasi kehadiran tugas keseluruhan.
     *
     * Dioptimalkan dengan Eager Loading bertingkat untuk mencegah N+1 Query.
     */
    public function getStudentCumulativeData(User $student): array
    {
        // 1. Ambil seluruh mata kuliah yang diambil mahasiswa beserta tugas dan submission miliknya
        $courses = $student->courses()
            ->with([
                'lecturer:id,name,nim_nip',
                'assignments' => function ($q) {
                    $q->select(['id', 'course_id', 'title', 'max_score', 'due_at', 'status'])
                      ->where('status', 'published')
                      ->orderBy('due_at', 'asc');
                },
                'assignments.submissions' => function ($q) use ($student) {
                    $q->select(['id', 'assignment_id', 'user_id', 'submitted_at', 'is_late'])
                      ->where('user_id', $student->id)
                      ->with(['grade:id,submission_id,score,feedback,graded_at']);
                },
            ])
            ->orderBy('code', 'asc')
            ->get();

        $courseResults = [];
        $totalCreditsTaken = 0;
        $totalCreditsPassed = 0;
        $totalQualityPoints = 0.0;

        $globalTotalAssignments = 0;
        $globalOnTime = 0;
        $globalLate = 0;
        $globalMissing = 0;
        $globalPending = 0;

        foreach ($courses as $course) {
            $assignments = $course->assignments;
            $submissions = $assignments->flatMap(fn ($a) => $a->submissions);

            // Hitung nilai akhir mata kuliah
            $courseGrade = $this->calculateCourseGrade($assignments, $submissions);
            // Hitung presensi tugas mata kuliah
            $courseAttendance = $this->calculateTaskAttendance($assignments, $submissions);

            $sks = (int) $course->sks;
            $totalCreditsTaken += $sks;

            if ($courseGrade['is_passed']) {
                $totalCreditsPassed += $sks;
            }

            $qualityPoints = round($sks * $courseGrade['grade_point'], 2);
            $totalQualityPoints += $qualityPoints;

            // Akumulasi presensi tugas global
            $globalTotalAssignments += $courseAttendance['total_mandatory'];
            $globalOnTime           += $courseAttendance['on_time_count'];
            $globalLate             += $courseAttendance['late_count'];
            $globalMissing          += $courseAttendance['missing_count'];
            $globalPending          += $courseAttendance['pending_count'];

            $courseResults[] = [
                'id'              => $course->id,
                'code'            => $course->code,
                'name'            => $course->name,
                'sks'             => $sks,
                'lecturer_name'   => $course->lecturer->name ?? 'Belum Ditentukan',
                'final_score'     => $courseGrade['final_score'],
                'letter_grade'    => $courseGrade['letter'],
                'grade_point'     => $courseGrade['grade_point'],
                'quality_points'  => $qualityPoints,
                'is_passed'       => $courseGrade['is_passed'],
                'attendance'      => $courseAttendance,
            ];
        }

        // Hitung Indeks Prestasi Kumulatif (IPK)
        // IPK = Total Angka Mutu / Total SKS Diambil (standar BAN-PT/akademik)
        $gpa = $totalCreditsTaken > 0
            ? round($totalQualityPoints / $totalCreditsTaken, 2)
            : 0.00;

        // Persentase kehadiran tugas kumulatif
        $overallAttendanceRate = $globalTotalAssignments > 0
            ? round((($globalOnTime + $globalLate) / $globalTotalAssignments) * 100, 1)
            : 100.0;

        return [
            'student' => [
                'id'      => $student->id,
                'name'    => $student->name,
                'nim_nip' => $student->nim_nip,
                'email'   => $student->email,
            ],
            'courses' => $courseResults,
            'summary' => [
                'total_courses'           => count($courseResults),
                'total_credits_taken'     => $totalCreditsTaken,
                'total_credits_passed'    => $totalCreditsPassed,
                'total_quality_points'    => round($totalQualityPoints, 2),
                'gpa'                     => $gpa,
                'predicate'               => $this->getPredicate($gpa),
                // Statistik Presensi Global
                'total_assignments'       => $globalTotalAssignments,
                'on_time_count'           => $globalOnTime,
                'late_count'              => $globalLate,
                'missing_count'           => $globalMissing,
                'pending_count'           => $globalPending,
                'overall_attendance_rate' => $overallAttendanceRate,
                'attendance_status'       => $this->getAttendanceEvaluation($overallAttendanceRate),
            ],
        ];
    }

    /**
     * Mengambil data transkrip terpadu seluruh mata kuliah yang diampu dosen,
     * beserta seluruh mahasiswa yang terdaftar dan nilai masing-masing.
     *
     * Digunakan untuk fitur "Laporan Transkrip Pengampu" — 1 PDF mencakup
     * semua MK yang diampu dan semua mahasiswa di setiap MK tersebut.
     *
     * Dioptimalkan dengan Eager Loading bertingkat untuk mencegah N+1 Query.
     */
    public function getLecturerCoursesData(User $lecturer): array
    {
        // Ambil semua mata kuliah milik dosen ini, beserta mahasiswa dan tugas-tugasnya
        $courses = $lecturer->taughtCourses()
            ->with([
                'students:id,name,nim_nip', //bug IDOR dari kel 9 (1)
                'assignments' => function ($q) {
                    $q->select(['id', 'course_id', 'title', 'max_score', 'due_at', 'status'])
                      ->where('status', 'published')
                      ->orderBy('due_at', 'asc');
                },
                'assignments.submissions' => function ($q) {
                    $q->select(['id', 'assignment_id', 'user_id', 'submitted_at', 'is_late'])
                      ->with(['grade:id,submission_id,score,feedback,graded_at']);
                },
            ])
            ->orderBy('code', 'asc')
            ->get(); 

        $courseResults = [];

        foreach ($courses as $course) {
            $assignments    = $course->assignments;
            $allSubmissions = $assignments->flatMap(fn ($a) => $a->submissions);
            $students       = $course->students->sortBy('nim_nip')->values();

            $studentResults = [];
            $totalFinalScore = 0.0;
            $gradedStudentCount = 0;
            $passedCount = 0;

            foreach ($students as $student) {
                // Filter submission hanya milik mahasiswa ini
                $studentSubmissions = $allSubmissions->where('user_id', $student->id)->values();

                $grade      = $this->calculateCourseGrade($assignments, $studentSubmissions);
                $attendance = $this->calculateTaskAttendance($assignments, $studentSubmissions);

                if ($grade['final_score'] !== null) {
                    $totalFinalScore += $grade['final_score'];
                    $gradedStudentCount++;
                }

                if ($grade['is_passed']) {
                    $passedCount++;
                }

                $studentResults[] = [
                    'id'              => $student->id,
                    'name'            => $student->name,
                    'nim_nip'         => $student->nim_nip, // Bug IDOR dari kelompok 9 (1)
                    'final_score'     => $grade['final_score'],
                    'letter_grade'    => $grade['letter'],
                    'grade_point'     => $grade['grade_point'],
                    'is_passed'       => $grade['is_passed'],
                    'attendance_rate' => $attendance['attendance_rate'],
                    'on_time_count'   => $attendance['on_time_count'],
                    'late_count'      => $attendance['late_count'],
                    'missing_count'   => $attendance['missing_count'],
                    'total_mandatory' => $attendance['total_mandatory'],
                ];
            }

            $avgScore = $gradedStudentCount > 0
                ? round($totalFinalScore / $gradedStudentCount, 2)
                : null;

            $totalStudents = $students->count();
            $passRate = $totalStudents > 0
                ? round(($passedCount / $totalStudents) * 100, 1)
                : 0.0;

            $courseResults[] = [
                'id'            => $course->id,
                'code'          => $course->code,
                'name'          => $course->name,
                'sks'           => (int) $course->sks,
                'total_students' => $totalStudents,
                'avg_score'     => $avgScore,
                'avg_grade'     => $avgScore !== null ? $this->scoreToGrade($avgScore)['letter'] : '—',
                'pass_rate'     => $passRate,
                'passed_count'  => $passedCount,
                'students'      => $studentResults,
            ];
        }
        $totalAllStudents = collect($courseResults)->sum('total_students');
        
        return [
            'lecturer' => [
                'id'      => $lecturer->id,
                'name'    => $lecturer->name,
                'nim_nip' => $lecturer->nim_nip,
                'email'   => $lecturer->email,
            ],
            'courses'              => $courseResults,
            'total_courses'        => count($courseResults),
            'total_students_count' => $totalAllStudents,
        ];
    }

    /**
     * Mengambil rincian nilai dan kehadiran tugas mahasiswa khusus pada SATU mata kuliah
     * yang diampu oleh Dosen Pengampu.
     */
    public function getStudentCourseData(Course $course, User $student): array
    {
        // Pastikan relasi dosen termuat
        $course->loadMissing('lecturer:id,name,nim_nip');

        // Ambil seluruh tugas di mata kuliah ini beserta submission mahasiswa bersangkutan
        $assignments = $course->assignments()
            ->select(['id', 'course_id', 'title', 'max_score', 'due_at', 'status'])
            ->where('status', 'published')
            ->with(['submissions' => function ($q) use ($student) {
                $q->select(['id', 'assignment_id', 'user_id', 'submitted_at', 'is_late', 'original_name'])
                  ->where('user_id', $student->id)
                  ->with(['grade:id,submission_id,score,feedback,graded_at']);
            }])
            ->orderBy('due_at', 'asc')
            ->get();

        $submissions = $assignments->flatMap(fn ($a) => $a->submissions);

        $courseGrade = $this->calculateCourseGrade($assignments, $submissions);
        $courseAttendance = $this->calculateTaskAttendance($assignments, $submissions);

        // Petakan detail per tugas
        $assignmentDetails = [];
        $now = now();

        foreach ($assignments as $assignment) {
            $sub = $assignment->submissions->first();
            $grade = $sub?->grade;

            // Klasifikasi status presensi tugas individual
            if ($sub) {
                $statusKey = $sub->is_late ? 'late' : 'on_time';
                $statusLabel = $sub->is_late ? 'Terlambat' : 'Tepat Waktu';
            } elseif ($assignment->due_at && $assignment->due_at->isPast()) {
                $statusKey = 'missing';
                $statusLabel = 'Tidak Mengumpulkan';
            } else {
                $statusKey = 'pending';
                $statusLabel = 'Sedang Berjalan';
            }

            $assignmentDetails[] = [
                'id'                => $assignment->id,
                'title'             => $assignment->title,
                'max_score'         => $assignment->max_score,
                'due_at'            => $assignment->due_at,
                'status_key'        => $statusKey,
                'status_label'      => $statusLabel,
                'is_submitted'      => (bool) $sub,
                'submitted_at'      => $sub?->submitted_at,
                'is_late'           => (bool) ($sub?->is_late),
                'score'             => $grade?->score !== null ? (float) $grade->score : null,
                'feedback'          => $grade?->feedback,
                'graded_at'         => $grade?->graded_at,
            ];
        }

        $sks = (int) $course->sks;
        $qualityPoints = round($sks * $courseGrade['grade_point'], 2);

        return [
            'student' => [
                'id'      => $student->id,
                'name'    => $student->name,
                'nim_nip' => $student->nim_nip,
                'email'   => $student->email,
            ],
            'course' => [
                'id'            => $course->id,
                'code'          => $course->code,
                'name'          => $course->name,
                'sks'           => $sks,
                'lecturer_name' => $course->lecturer->name ?? 'Belum Ditentukan',
            ],
            'grade' => array_merge($courseGrade, [
                'sks'            => $sks,
                'quality_points' => $qualityPoints,
            ]),
            'attendance'   => $courseAttendance,
            'assignments'  => $assignmentDetails,
        ];
    }

    /**
     * Menghitung nilai akhir angka (0-100), huruf mutu, bobot indeks, dan status kelulusan.
     */
    public function calculateCourseGrade(Collection $assignments, Collection $submissions): array
    {
        if ($assignments->isEmpty()) {
            return array_merge($this->scoreToGrade(null), [
                'final_score' => null,
            ]);
        }

        $totalEarned = 0.0;
        $totalMax    = 0.0;
        $gradedCount = 0;

        foreach ($assignments as $assignment) {
            $sub = $submissions->firstWhere('assignment_id', $assignment->id);
            $grade = $sub?->grade;

            $maxScore = (float) ($assignment->max_score ?: 100);
            $totalMax += $maxScore;

            if ($grade && $grade->score !== null) {
                $totalEarned += (float) $grade->score;
                $gradedCount++;
            } elseif ($assignment->due_at && $assignment->due_at->isPast()) {
                // Jika sudah melewati deadline dan mahasiswa tidak mengumpulkan/belum dinilai,
                // tugas ini berbobot 0 terhadap nilai akhir
                $totalEarned += 0.0;
            }
        }

        if ($totalMax <= 0 || $gradedCount === 0) {
            return array_merge($this->scoreToGrade(null), [
                'final_score' => null,
            ]);
        }

        // Normalisasi skor ke skala 0 - 100
        $finalScore = round(($totalEarned / $totalMax) * 100, 2);

        return array_merge($this->scoreToGrade($finalScore), [
            'final_score' => $finalScore,
        ]);
    }

    /**
     * Menghitung metrik kehadiran pengumpulan tugas (Presensi Tugas).
     */
    public function calculateTaskAttendance(Collection $assignments, Collection $submissions): array
    {
        $onTimeCount = 0;
        $lateCount   = 0;
        $missingCount = 0;
        $pendingCount = 0;

        foreach ($assignments as $assignment) {
            $sub = $submissions->firstWhere('assignment_id', $assignment->id);

            if ($sub) {
                if ($sub->is_late) {
                    $lateCount++;
                } else {
                    $onTimeCount++;
                }
            } elseif ($assignment->due_at && $assignment->due_at->isPast()) {
                $missingCount++;
            } else {
                $pendingCount++;
            }
        }

        // Tugas wajib adalah tugas yang sudah melewati tenggat waktu atau sudah dikerjakan
        $totalMandatory = $onTimeCount + $lateCount + $missingCount;

        // Persentase kehadiran = (Tepat Waktu + Terlambat) / Total Tugas Wajib * 100%
        $attendanceRate = $totalMandatory > 0
            ? round((($onTimeCount + $lateCount) / $totalMandatory) * 100, 1)
            : 100.0;

        // Persentase ketepatan waktu murni = Tepat Waktu / Total Tugas Wajib * 100%
        $punctualityRate = $totalMandatory > 0
            ? round(($onTimeCount / $totalMandatory) * 100, 1)
            : 100.0;

        return [
            'total_assignments' => $assignments->count(),
            'total_mandatory'   => $totalMandatory,
            'on_time_count'     => $onTimeCount,
            'late_count'        => $lateCount,
            'missing_count'     => $missingCount,
            'pending_count'     => $pendingCount,
            'attendance_rate'   => $attendanceRate,
            'punctuality_rate'  => $punctualityRate,
            'status_label'      => $this->getAttendanceEvaluation($attendanceRate),
            'is_eligible'       => $attendanceRate >= 75.0, // Syarat minimal kehadiran 75%
        ];
    }

    /**
     * Konversi nilai angka (0 - 100) ke huruf mutu, bobot indeks 4.00, dan status kelulusan.
     */
    public function scoreToGrade(?float $score): array
    {
        if ($score === null) {
            return [
                'letter'      => '—',
                'grade_point' => 0.00,
                'is_passed'   => false,
                'description' => 'Belum Ada Penilaian',
            ];
        }

        return match (true) {
            $score >= 85.0 => [
                'letter'      => 'A',
                'grade_point' => 4.00,
                'is_passed'   => true,
                'description' => 'Sangat Baik',
            ],
            $score >= 80.0 => [
                'letter'      => 'A-',
                'grade_point' => 3.75,
                'is_passed'   => true,
                'description' => 'Sangat Baik',
            ],
            $score >= 75.0 => [
                'letter'      => 'B+',
                'grade_point' => 3.50,
                'is_passed'   => true,
                'description' => 'Baik',
            ],
            $score >= 70.0 => [
                'letter'      => 'B',
                'grade_point' => 3.00,
                'is_passed'   => true,
                'description' => 'Baik',
            ],
            $score >= 65.0 => [
                'letter'      => 'B-',
                'grade_point' => 2.75,
                'is_passed'   => true,
                'description' => 'Cukup Baik',
            ],
            $score >= 60.0 => [
                'letter'      => 'C+',
                'grade_point' => 2.50,
                'is_passed'   => true,
                'description' => 'Cukup',
            ],
            $score >= 55.0 => [
                'letter'      => 'C',
                'grade_point' => 2.00,
                'is_passed'   => true,
                'description' => 'Cukup',
            ],
            $score >= 40.0 => [
                'letter'      => 'D',
                'grade_point' => 1.00,
                'is_passed'   => false,
                'description' => 'Kurang (Tidak Lulus)',
            ],
            default => [
                'letter'      => 'E',
                'grade_point' => 0.00,
                'is_passed'   => false,
                'description' => 'Gagal',
            ],
        };
    }

    /**
     * Menentukan predikat akademik berdasarkan Indeks Prestasi Kumulatif (IPK).
     */
    public function getPredicate(float $gpa): string
    {
        return match (true) {
            $gpa >= 3.51 => 'Dengan Pujian (Cum Laude)',
            $gpa >= 3.01 => 'Sangat Memuaskan',
            $gpa >= 2.76 => 'Memuaskan',
            $gpa >= 2.00 => 'Cukup',
            default      => 'Kurang Memuaskan',
        };
    }

    /**
     * Menentukan status evaluasi persentase presensi tugas.
     */
    public function getAttendanceEvaluation(float $rate): string
    {
        return match (true) {
            $rate >= 85.0 => 'Sangat Baik',
            $rate >= 75.0 => 'Baik (Memenuhi Syarat)',
            default       => 'Peringatan (Di Bawah 75%)',
        };
    }
}

