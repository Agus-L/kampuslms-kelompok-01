<?php

namespace Tests\Unit;

use App\Models\Assignment;
use App\Models\Submission;
use App\Services\TranscriptService;
use Carbon\Carbon;
use Tests\TestCase;

class TranscriptServiceTest extends TestCase
{
    protected TranscriptService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TranscriptService();
    }

    /**
     * Uji konversi nilai angka ke huruf mutu, bobot indeks, dan status kelulusan.
     */
    public function test_score_to_grade_conversion(): void
    {
        // Nilai A (>= 85)
        $gradeA = $this->service->scoreToGrade(92.5);
        $this->assertEquals('A', $gradeA['letter']);
        $this->assertEquals(4.00, $gradeA['grade_point']);
        $this->assertTrue($gradeA['is_passed']);

        // Nilai B+ (75 - 79.9)
        $gradeBPlus = $this->service->scoreToGrade(78.0);
        $this->assertEquals('B+', $gradeBPlus['letter']);
        $this->assertEquals(3.50, $gradeBPlus['grade_point']);
        $this->assertTrue($gradeBPlus['is_passed']);

        // Nilai C (55 - 59.9) -> Batas Lulus
        $gradeC = $this->service->scoreToGrade(56.0);
        $this->assertEquals('C', $gradeC['letter']);
        $this->assertEquals(2.00, $gradeC['grade_point']);
        $this->assertTrue($gradeC['is_passed']);

        // Nilai D (40 - 54.9) -> Tidak Lulus
        $gradeD = $this->service->scoreToGrade(48.0);
        $this->assertEquals('D', $gradeD['letter']);
        $this->assertEquals(1.00, $gradeD['grade_point']);
        $this->assertFalse($gradeD['is_passed']);

        // Nilai E (< 40) -> Gagal
        $gradeE = $this->service->scoreToGrade(32.0);
        $this->assertEquals('E', $gradeE['letter']);
        $this->assertEquals(0.00, $gradeE['grade_point']);
        $this->assertFalse($gradeE['is_passed']);

        // Nilai Null -> Belum Ada Penilaian
        $gradeNull = $this->service->scoreToGrade(null);
        $this->assertEquals('—', $gradeNull['letter']);
        $this->assertFalse($gradeNull['is_passed']);
    }

    /**
     * Uji penentuan predikat akademik berdasarkan IPK.
     */
    public function test_academic_predicate_determination(): void
    {
        $this->assertEquals('Dengan Pujian (Cum Laude)', $this->service->getPredicate(3.85));
        $this->assertEquals('Sangat Memuaskan', $this->service->getPredicate(3.25));
        $this->assertEquals('Memuaskan', $this->service->getPredicate(2.80));
        $this->assertEquals('Cukup', $this->service->getPredicate(2.20));
        $this->assertEquals('Kurang Memuaskan', $this->service->getPredicate(1.80));
    }

    /**
     * Uji perhitungan presensi dan ketepatan waktu pengumpulan tugas.
     */
    public function test_calculate_task_attendance_metrics(): void
    {
        // 4 tugas (3 sudah lewat deadline, 1 masih aktif)
        $a1 = new Assignment(['max_score' => 100, 'due_at' => Carbon::now()->subDays(5)]);
        $a1->id = 1;

        $a2 = new Assignment(['max_score' => 100, 'due_at' => Carbon::now()->subDays(3)]);
        $a2->id = 2;

        $a3 = new Assignment(['max_score' => 100, 'due_at' => Carbon::now()->subDays(1)]);
        $a3->id = 3;

        $a4 = new Assignment(['max_score' => 100, 'due_at' => Carbon::now()->addDays(2)]);
        $a4->id = 4;

        $assignments = collect([$a1, $a2, $a3, $a4]);

        // Mock submission: a1 tepat waktu, a2 terlambat, a3 alpa/tidak mengumpulkan, a4 belum dikerjakan
        $sub1 = new Submission(['assignment_id' => 1, 'is_late' => false]);
        $sub2 = new Submission(['assignment_id' => 2, 'is_late' => true]);

        $submissions = collect([$sub1, $sub2]);

        $attendance = $this->service->calculateTaskAttendance($assignments, $submissions);

        // Verifikasi hasil
        $this->assertEquals(4, $attendance['total_assignments']);
        $this->assertEquals(3, $attendance['total_mandatory']); // 3 tugas wajib (sub1, sub2, sub3)
        $this->assertEquals(1, $attendance['on_time_count']);
        $this->assertEquals(1, $attendance['late_count']);
        $this->assertEquals(1, $attendance['missing_count']);
        $this->assertEquals(1, $attendance['pending_count']);

        // Persentase kehadiran: (1 tepat waktu + 1 terlambat) / 3 tugas wajib = 2/3 = 66.7%
        $this->assertEquals(66.7, $attendance['attendance_rate']);
        // Tidak memenuhi syarat kehadiran (karena < 75%)
        $this->assertFalse($attendance['is_eligible']);
        $this->assertEquals('Peringatan (Di Bawah 75%)', $attendance['status_label']);
    }
}
