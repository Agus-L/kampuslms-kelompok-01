<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PolicyAuthorizationTest extends TestCase
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

        // Siapkan akun untuk 3 peran
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->dosen1 = User::factory()->create(['role' => 'dosen']);
        $this->dosen2 = User::factory()->create(['role' => 'dosen']);
        $this->mhs1 = User::factory()->create(['role' => 'mahasiswa']);
        $this->mhs2 = User::factory()->create(['role' => 'mahasiswa']);

        // Course 1 diampu oleh dosen 1, diikuti oleh mhs 1
        $this->course1 = Course::factory()->create([
            'lecturer_id' => $this->dosen1->id,
            'status' => 'active',
        ]);
        $this->course1->students()->attach($this->mhs1->id);

        // Course 2 diampu oleh dosen 2, diikuti oleh mhs 2
        $this->course2 = Course::factory()->create([
            'lecturer_id' => $this->dosen2->id,
            'status' => 'active',
        ]);
        $this->course2->students()->attach($this->mhs2->id);
    }

    /* =========================================================================
     * 1. CoursePolicy Tests
     * ========================================================================= */
    public function test_course_policy_permissions(): void
    {
        // viewAny: hanya admin
        $this->assertTrue($this->admin->can('viewAny', Course::class));
        $this->assertFalse($this->dosen1->can('viewAny', Course::class));
        $this->assertFalse($this->mhs1->can('viewAny', Course::class));

        // view: admin boleh semua
        $this->assertTrue($this->admin->can('view', $this->course1));
        // dosen hanya MK miliknya
        $this->assertTrue($this->dosen1->can('view', $this->course1));
        $this->assertFalse($this->dosen2->can('view', $this->course1));
        // mhs hanya MK yang ia ikuti
        $this->assertTrue($this->mhs1->can('view', $this->course1));
        $this->assertFalse($this->mhs2->can('view', $this->course1));

        // create, update, delete: hanya admin
        $this->assertTrue($this->admin->can('create', Course::class));
        $this->assertFalse($this->dosen1->can('create', Course::class));
        $this->assertFalse($this->mhs1->can('create', Course::class));

        $this->assertTrue($this->admin->can('update', $this->course1));
        $this->assertFalse($this->dosen1->can('update', $this->course1));

        $this->assertTrue($this->admin->can('delete', $this->course1));
        $this->assertFalse($this->dosen1->can('delete', $this->course1));

        // manageEnrollment: admin & dosen pengampu
        $this->assertTrue($this->admin->can('manageEnrollment', $this->course1));
        $this->assertTrue($this->dosen1->can('manageEnrollment', $this->course1));
        $this->assertFalse($this->dosen2->can('manageEnrollment', $this->course1));
        $this->assertFalse($this->mhs1->can('manageEnrollment', $this->course1));
    }

    /* =========================================================================
     * 2. MaterialPolicy Tests
     * ========================================================================= */
    public function test_material_policy_permissions(): void
    {
        $material1 = Material::factory()->create([
            'course_id' => $this->course1->id,
            'uploaded_by' => $this->dosen1->id,
        ]);

        // viewAny
        $this->assertTrue($this->admin->can('viewAny', Material::class));
        $this->assertTrue($this->dosen1->can('viewAny', Material::class));
        $this->assertTrue($this->mhs1->can('viewAny', Material::class));

        // view & download
        $this->assertTrue($this->admin->can('view', $material1));
        $this->assertTrue($this->dosen1->can('view', $material1));
        $this->assertFalse($this->dosen2->can('view', $material1)); // dosen lain ditolak
        $this->assertTrue($this->mhs1->can('view', $material1));
        $this->assertFalse($this->mhs2->can('view', $material1)); // mhs luar kelas ditolak

        $this->assertTrue($this->admin->can('download', $material1));
        $this->assertTrue($this->dosen1->can('download', $material1));
        $this->assertFalse($this->dosen2->can('download', $material1));
        $this->assertTrue($this->mhs1->can('download', $material1));
        $this->assertFalse($this->mhs2->can('download', $material1));

        // create
        $this->assertTrue($this->admin->can('create', [Material::class, $this->course1]));
        $this->assertTrue($this->dosen1->can('create', [Material::class, $this->course1]));
        $this->assertFalse($this->dosen2->can('create', [Material::class, $this->course1]));
        $this->assertFalse($this->mhs1->can('create', [Material::class, $this->course1]));

        // update & delete
        $this->assertTrue($this->admin->can('update', $material1));
        $this->assertTrue($this->dosen1->can('update', $material1));
        $this->assertFalse($this->dosen2->can('update', $material1));
        $this->assertFalse($this->mhs1->can('update', $material1));

        $this->assertTrue($this->admin->can('delete', $material1));
        $this->assertTrue($this->dosen1->can('delete', $material1));
        $this->assertFalse($this->dosen2->can('delete', $material1));
        $this->assertFalse($this->mhs1->can('delete', $material1));
    }

    /* =========================================================================
     * 3. AssignmentPolicy Tests
     * ========================================================================= */
    public function test_assignment_policy_permissions(): void
    {
        $assign1 = Assignment::factory()->create([
            'course_id' => $this->course1->id,
        ]);

        // viewAny
        $this->assertTrue($this->admin->can('viewAny', Assignment::class));
        $this->assertTrue($this->dosen1->can('viewAny', Assignment::class));
        $this->assertTrue($this->mhs1->can('viewAny', Assignment::class));

        // view
        $this->assertTrue($this->admin->can('view', $assign1));
        $this->assertTrue($this->dosen1->can('view', $assign1));
        $this->assertFalse($this->dosen2->can('view', $assign1));
        $this->assertTrue($this->mhs1->can('view', $assign1));
        $this->assertFalse($this->mhs2->can('view', $assign1));

        // create
        $this->assertTrue($this->admin->can('create', [Assignment::class, $this->course1]));
        $this->assertTrue($this->dosen1->can('create', [Assignment::class, $this->course1]));
        $this->assertFalse($this->dosen2->can('create', [Assignment::class, $this->course1]));
        $this->assertFalse($this->mhs1->can('create', [Assignment::class, $this->course1]));

        // update & delete
        $this->assertTrue($this->admin->can('update', $assign1));
        $this->assertTrue($this->dosen1->can('update', $assign1));
        $this->assertFalse($this->dosen2->can('update', $assign1));
        $this->assertFalse($this->mhs1->can('update', $assign1));

        $this->assertTrue($this->admin->can('delete', $assign1));
        $this->assertTrue($this->dosen1->can('delete', $assign1));
        $this->assertFalse($this->dosen2->can('delete', $assign1));
        $this->assertFalse($this->mhs1->can('delete', $assign1));
    }

    /* =========================================================================
     * 4. SubmissionPolicy Tests (Anti-IDOR, Revision Flow & Permanent Audit Trail)
     * ========================================================================= */
    public function test_submission_policy_permissions(): void
    {
        $assign1 = Assignment::factory()->create([
            'course_id' => $this->course1->id,
        ]);

        // create: mhs1 terdaftar dan belum submit
        $this->assertTrue($this->mhs1->can('create', [Submission::class, $assign1]));
        $this->assertFalse($this->mhs2->can('create', [Submission::class, $assign1])); // bukan peserta
        $this->assertFalse($this->dosen1->can('create', [Submission::class, $assign1]));
        $this->assertFalse($this->admin->can('create', [Submission::class, $assign1]));

        // mhs1 submit tugas
        $sub1 = Submission::factory()->create([
            'assignment_id' => $assign1->id,
            'user_id' => $this->mhs1->id,
        ]);

        // setelah submit, create lagi harus return false (1x kirim)
        $this->assertFalse($this->mhs1->can('create', [Submission::class, $assign1]));

        // view: admin boleh audit, dosen pengampu boleh, mahasiswa pemilik boleh (anti-IDOR)
        $this->assertTrue($this->admin->can('view', $sub1));
        $this->assertTrue($this->dosen1->can('view', $sub1));
        $this->assertFalse($this->dosen2->can('view', $sub1)); // dosen lain ditolak
        $this->assertTrue($this->mhs1->can('view', $sub1));
        $this->assertFalse($this->mhs2->can('view', $sub1)); // anti-IDOR mhs lain ditolak

        // requestRevision: mhs1 boleh ajukan revisi jika belum dinilai
        $this->assertTrue($this->mhs1->can('requestRevision', $sub1));
        $this->assertFalse($this->mhs2->can('requestRevision', $sub1));

        // allowRevision: dosen1 boleh setujui revisi
        $this->assertTrue($this->dosen1->can('allowRevision', $sub1));
        $this->assertFalse($this->dosen2->can('allowRevision', $sub1));
        $this->assertFalse($this->admin->can('allowRevision', $sub1));

        // delete: MUTLAK DITOLAK UNTUK SEMUA PERAN (Academic audit trail)
        $this->assertFalse($this->admin->can('delete', $sub1));
        $this->assertFalse($this->dosen1->can('delete', $sub1));
        $this->assertFalse($this->dosen2->can('delete', $sub1));
        $this->assertFalse($this->mhs1->can('delete', $sub1));
    }

    /* =========================================================================
     * 5. GradePolicy Tests
     * ========================================================================= */
    public function test_grade_policy_permissions(): void
    {
        $assign1 = Assignment::factory()->create([
            'course_id' => $this->course1->id,
        ]);
        $sub1 = Submission::factory()->create([
            'assignment_id' => $assign1->id,
            'user_id' => $this->mhs1->id,
        ]);

        // create grade (memberi nilai): hanya dosen pengampu
        $this->assertTrue($this->dosen1->can('create', [Grade::class, $sub1]));
        $this->assertFalse($this->dosen2->can('create', [Grade::class, $sub1]));
        $this->assertFalse($this->admin->can('create', [Grade::class, $sub1]));
        $this->assertFalse($this->mhs1->can('create', [Grade::class, $sub1]));

        $grade1 = Grade::factory()->create([
            'submission_id' => $sub1->id,
            'graded_by' => $this->dosen1->id,
            'score' => 90.00,
        ]);

        // view grade
        $this->assertTrue($this->admin->can('view', $grade1));
        $this->assertTrue($this->dosen1->can('view', $grade1));
        $this->assertFalse($this->dosen2->can('view', $grade1));
        $this->assertTrue($this->mhs1->can('view', $grade1)); // pemilik submission
        $this->assertFalse($this->mhs2->can('view', $grade1)); // anti-IDOR mhs lain

        // update grade (koreksi skor): hanya dosen pengampu
        $this->assertTrue($this->dosen1->can('update', $grade1));
        $this->assertFalse($this->dosen2->can('update', $grade1));
        $this->assertFalse($this->admin->can('update', $grade1));
        $this->assertFalse($this->mhs1->can('update', $grade1));

        // delete grade (reset nilai): hanya dosen pengampu
        $this->assertTrue($this->dosen1->can('delete', $grade1));
        $this->assertFalse($this->dosen2->can('delete', $grade1));
        $this->assertFalse($this->admin->can('delete', $grade1));
        $this->assertFalse($this->mhs1->can('delete', $grade1));
    }
}
