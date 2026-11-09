<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class TranscriptPolicyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $dosen1;
    private User $dosen2;
    private User $mhs1;
    private User $mhs2;
    private Course $course1;
    private Course $course2;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Siapkan pengguna untuk tiap peran
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->dosen1 = User::factory()->create(['role' => 'dosen']);
        $this->dosen2 = User::factory()->create(['role' => 'dosen']);
        $this->mhs1 = User::factory()->create(['role' => 'mahasiswa']);
        $this->mhs2 = User::factory()->create(['role' => 'mahasiswa']);

        // 2. Course 1 diampu oleh dosen 1, diikuti oleh mhs 1
        $this->course1 = Course::factory()->create([
            'lecturer_id' => $this->dosen1->id,
            'status' => 'active',
        ]);
        $this->course1->students()->attach($this->mhs1->id);

        // 3. Course 2 diampu oleh dosen 2, diikuti oleh mhs 2
        $this->course2 = Course::factory()->create([
            'lecturer_id' => $this->dosen2->id,
            'status' => 'active',
        ]);
        $this->course2->students()->attach($this->mhs2->id);
    }

    /* =========================================================================
     * 1. Hak Akses Dosen Pengampu & Boundary Protection (Course Transcript)
     * ========================================================================= */

    /**
     * Dosen pengampu dapat melihat dan mengekspor transkrip mahasiswa pada mata kuliah miliknya.
     */
    public function test_dosen_pengampu_can_access_student_transcript_in_own_course(): void
    {
        $this->actingAs($this->dosen1);

        $this->assertTrue(Gate::allows('viewStudentCourseTranscript', [$this->course1, $this->mhs1]));
        $this->assertTrue(Gate::allows('exportStudentCourseTranscript', [$this->course1, $this->mhs1]));
        $this->assertTrue($this->dosen1->can('viewStudentCourseTranscript', [$this->course1, $this->mhs1]));
        $this->assertTrue($this->dosen1->can('viewTranscript', [$this->course1, $this->mhs1]));
    }

    /**
     * Dosen pengampu dilarang keras mengakses transkrip mata kuliah milik dosen lain (Boundary Protection).
     */
    public function test_dosen_cannot_access_student_transcript_in_other_lecturers_course(): void
    {
        // Dosen 2 mencoba mengakses transkrip mahasiswa di mata kuliah milik Dosen 1
        $this->actingAs($this->dosen2);

        $this->assertTrue(Gate::denies('viewStudentCourseTranscript', [$this->course1, $this->mhs1]));
        $this->assertTrue(Gate::denies('exportStudentCourseTranscript', [$this->course1, $this->mhs1]));
        $this->assertFalse($this->dosen2->can('viewStudentCourseTranscript', [$this->course1, $this->mhs1]));
        $this->assertFalse($this->dosen2->can('viewTranscript', [$this->course1, $this->mhs1]));

        // Sebaliknya: Dosen 1 mencoba mengakses transkrip di mata kuliah milik Dosen 2
        $this->actingAs($this->dosen1);

        $this->assertTrue(Gate::denies('viewStudentCourseTranscript', [$this->course2, $this->mhs2]));
        $this->assertTrue(Gate::denies('exportStudentCourseTranscript', [$this->course2, $this->mhs2]));
    }

    /**
     * Dosen pengampu tidak dapat mengakses transkrip mahasiswa yang tidak terdaftar di mata kuliahnya.
     */
    public function test_dosen_cannot_access_transcript_of_unenrolled_student(): void
    {
        $this->actingAs($this->dosen1);

        // Mahasiswa 2 tidak terdaftar di Course 1 milik Dosen 1
        $this->assertTrue(Gate::denies('viewStudentCourseTranscript', [$this->course1, $this->mhs2]));
        $this->assertTrue(Gate::denies('exportStudentCourseTranscript', [$this->course1, $this->mhs2]));
    }

    /**
     * Dosen dilarang mengakses transkrip kumulatif mahasiswa umum (hanya berwenang per mata kuliah yang diampu).
     */
    public function test_dosen_cannot_access_cumulative_transcript(): void
    {
        $this->actingAs($this->dosen1);

        $this->assertTrue(Gate::denies('viewCumulativeTranscript', [$this->mhs1]));
        $this->assertTrue(Gate::denies('exportCumulativeTranscript', [$this->mhs1]));
        $this->assertFalse($this->dosen1->can('viewCumulativeTranscript', [$this->mhs1]));
    }

    /* =========================================================================
     * 2. Hak Akses Mahasiswa & Anti-IDOR (Self-Service)
     * ========================================================================= */

    /**
     * Mahasiswa dapat melihat dan mengekspor transkrip miliknya sendiri pada mata kuliah yang diikutinya.
     */
    public function test_mahasiswa_can_access_own_course_transcript(): void
    {
        $this->actingAs($this->mhs1);

        $this->assertTrue(Gate::allows('viewStudentCourseTranscript', [$this->course1, $this->mhs1]));
        $this->assertTrue(Gate::allows('exportStudentCourseTranscript', [$this->course1, $this->mhs1]));
        $this->assertTrue($this->mhs1->can('viewStudentCourseTranscript', [$this->course1, $this->mhs1]));
    }

    /**
     * Mahasiswa dilarang mengintip transkrip mata kuliah mahasiswa lain (Pencegahan IDOR).
     */
    public function test_mahasiswa_cannot_access_other_student_course_transcript_idor(): void
    {
        $this->actingAs($this->mhs1);

        // Mahasiswa 1 mencoba melihat transkrip Mahasiswa 2 di Course 2
        $this->assertTrue(Gate::denies('viewStudentCourseTranscript', [$this->course2, $this->mhs2]));
        $this->assertTrue(Gate::denies('exportStudentCourseTranscript', [$this->course2, $this->mhs2]));
    }

    /**
     * Mahasiswa dapat melihat dan mengekspor transkrip kumulatif miliknya sendiri.
     */
    public function test_mahasiswa_can_access_own_cumulative_transcript(): void
    {
        $this->actingAs($this->mhs1);

        $this->assertTrue(Gate::allows('viewCumulativeTranscript', [$this->mhs1]));
        $this->assertTrue(Gate::allows('exportCumulativeTranscript', [$this->mhs1]));
        $this->assertTrue($this->mhs1->can('viewCumulativeTranscript', [$this->mhs1]));
    }

    /**
     * Mahasiswa dilarang mengintip transkrip kumulatif mahasiswa lain (Pencegahan IDOR Kumulatif).
     */
    public function test_mahasiswa_cannot_access_other_student_cumulative_transcript_idor(): void
    {
        $this->actingAs($this->mhs1);

        // Mahasiswa 1 mencoba membuka transkrip kumulatif milik Mahasiswa 2
        $this->assertTrue(Gate::denies('viewCumulativeTranscript', [$this->mhs2]));
        $this->assertTrue(Gate::denies('exportCumulativeTranscript', [$this->mhs2]));
        $this->assertFalse($this->mhs1->can('viewCumulativeTranscript', [$this->mhs2]));
    }

    /* =========================================================================
     * 3. Hak Akses Administrator (Global Audit)
     * ========================================================================= */

    /**
     * Administrator dapat melihat dan mengekspor transkrip mahasiswa mana pun di semua mata kuliah.
     */
    public function test_admin_can_access_any_course_transcript(): void
    {
        $this->actingAs($this->admin);

        $this->assertTrue(Gate::allows('viewStudentCourseTranscript', [$this->course1, $this->mhs1]));
        $this->assertTrue(Gate::allows('exportStudentCourseTranscript', [$this->course1, $this->mhs1]));
        $this->assertTrue(Gate::allows('viewStudentCourseTranscript', [$this->course2, $this->mhs2]));
        $this->assertTrue(Gate::allows('exportStudentCourseTranscript', [$this->course2, $this->mhs2]));
    }

    /**
     * Administrator dapat melihat dan mengekspor transkrip kumulatif mahasiswa mana pun.
     */
    public function test_admin_can_access_any_cumulative_transcript(): void
    {
        $this->actingAs($this->admin);

        $this->assertTrue(Gate::allows('viewCumulativeTranscript', [$this->mhs1]));
        $this->assertTrue(Gate::allows('exportCumulativeTranscript', [$this->mhs1]));
        $this->assertTrue(Gate::allows('viewCumulativeTranscript', [$this->mhs2]));
        $this->assertTrue(Gate::allows('exportCumulativeTranscript', [$this->mhs2]));
    }
}

