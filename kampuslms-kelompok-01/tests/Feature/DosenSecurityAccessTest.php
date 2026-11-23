<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * DosenSecurityAccessTest — HTTP Feature Test proteksi IDOR seluruh rute Dosen.
 *
 * File ini MELENGKAPI (bukan menduplikasi) test yang sudah ada:
 * - CoreWorkflowFeatureTest: sudah menguji POST materials (#3) & POST grades (#10)
 * - MaterialUploadDownloadTest: sudah menguji GET download material (#4 sebagian)
 * - PolicyAuthorizationTest: menguji logika Policy (bukan HTTP)
 *
 * File ini menguji rute-rute Dosen yang BELUM punya HTTP Feature Test,
 * yaitu Titik Rawan #1, #2, #4 (update/delete), #5, #6, #7, #8, #9, #11, dan #22.
 *
 * Setiap test menyimulasikan Dosen B (bukan pengampu) menembak endpoint
 * milik Dosen A (pengampu sah), dan memastikan server merespons 403 Forbidden.
 */
class DosenSecurityAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $dosenPengampu;
    protected User $dosenLain;
    protected User $mahasiswa;
    protected Course $courseMilikPengampu;
    protected Course $courseMilikDosenLain;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        // Dosen A — pengampu sah course 1
        $this->dosenPengampu = User::factory()->dosen()->create();

        // Dosen B — dosen lain, BUKAN pengampu course 1
        $this->dosenLain = User::factory()->dosen()->create();

        // Mahasiswa terdaftar di course 1
        $this->mahasiswa = User::factory()->mahasiswa()->create();

        // Course milik Dosen A
        $this->courseMilikPengampu = Course::factory()->create([
            'lecturer_id' => $this->dosenPengampu->id,
            'status'      => 'active',
        ]);
        $this->courseMilikPengampu->students()->attach($this->mahasiswa->id, ['enrolled_at' => now()]);

        // Course milik Dosen B
        $this->courseMilikDosenLain = Course::factory()->create([
            'lecturer_id' => $this->dosenLain->id,
            'status'      => 'active',
        ]);
    }

    /* =========================================================================
     * TITIK RAWAN #1 — GET dosen/courses/{course}
     * Dosen lain ditolak melihat detail kelas bukan miliknya
     * ========================================================================= */

    public function test_dosen_lain_ditolak_melihat_detail_kelas_bukan_miliknya(): void
    {
        $response = $this->actingAs($this->dosenLain)
            ->get(route('dosen.courses.show', $this->courseMilikPengampu));

        $response->assertStatus(403);
    }

    /* =========================================================================
     * TITIK RAWAN #2 — GET dosen/courses/{course}/materials
     * Dosen lain ditolak melihat daftar materi kelas lain
     * ========================================================================= */

    public function test_dosen_lain_ditolak_melihat_daftar_materi_kelas_lain(): void
    {
        $response = $this->actingAs($this->dosenLain)
            ->get(route('dosen.courses.materials.index', $this->courseMilikPengampu));

        $response->assertStatus(403);
    }

    /* =========================================================================
     * TITIK RAWAN #4 — GET/PUT/DELETE dosen/materials/{material}
     * Dosen lain ditolak melihat, mengubah, dan menghapus materi dosen lain
     *
     * (Catatan: POST create sudah diuji di CoreWorkflowFeatureTest,
     *  GET download sudah diuji di MaterialUploadDownloadTest)
     * ========================================================================= */

    public function test_dosen_lain_ditolak_melihat_detail_materi_dosen_lain(): void
    {
        $material = Material::factory()->create([
            'course_id'   => $this->courseMilikPengampu->id,
            'uploaded_by' => $this->dosenPengampu->id,
        ]);

        $response = $this->actingAs($this->dosenLain)
            ->get(route('dosen.materials.show', $material));

        $response->assertStatus(403);
    }

    public function test_dosen_lain_ditolak_mengubah_materi_dosen_lain(): void
    {
        $material = Material::factory()->create([
            'course_id'   => $this->courseMilikPengampu->id,
            'uploaded_by' => $this->dosenPengampu->id,
        ]);

        $response = $this->actingAs($this->dosenLain)
            ->put(route('dosen.materials.update', $material), [
                'title'       => 'Judul Dibajak',
                'description' => 'Dosen lain mencoba mengubah materi.',
                'type'        => 'file',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('materials', ['title' => 'Judul Dibajak']);
    }

    public function test_dosen_lain_ditolak_menghapus_materi_dosen_lain(): void
    {
        $material = Material::factory()->create([
            'course_id'   => $this->courseMilikPengampu->id,
            'uploaded_by' => $this->dosenPengampu->id,
        ]);

        $response = $this->actingAs($this->dosenLain)
            ->delete(route('dosen.materials.destroy', $material));

        $response->assertStatus(403);
        $this->assertDatabaseHas('materials', ['id' => $material->id]);
    }

    /* =========================================================================
     * TITIK RAWAN #5 — GET dosen/courses/{course}/assignments
     * Dosen lain ditolak melihat daftar tugas kelas lain
     * ========================================================================= */

    public function test_dosen_lain_ditolak_melihat_daftar_tugas_kelas_lain(): void
    {
        $response = $this->actingAs($this->dosenLain)
            ->get(route('dosen.courses.assignments.index', $this->courseMilikPengampu));

        $response->assertStatus(403);
    }

    /* =========================================================================
     * TITIK RAWAN #6 — POST dosen/courses/{course}/assignments
     * Dosen lain ditolak membuat tugas di kelas lain
     * ========================================================================= */

    public function test_dosen_lain_ditolak_membuat_tugas_di_kelas_lain(): void
    {
        $response = $this->actingAs($this->dosenLain)
            ->post(route('dosen.courses.assignments.store', $this->courseMilikPengampu), [
                'title'        => 'Tugas Ilegal dari Dosen Lain',
                'instructions' => 'Mencoba menyisipkan tugas.',
                'due_at'       => now()->addDays(3)->format('Y-m-d H:i:s'),
                'max_score'    => 100,
                'allow_late'   => 0,
                'status'       => 'published',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('assignments', ['title' => 'Tugas Ilegal dari Dosen Lain']);
    }

    /* =========================================================================
     * TITIK RAWAN #7 — GET/PUT/DELETE dosen/assignments/{assignment}
     * Dosen lain ditolak melihat, mengubah, dan menghapus tugas dosen lain
     * ========================================================================= */

    public function test_dosen_lain_ditolak_melihat_detail_tugas_dosen_lain(): void
    {
        $assignment = Assignment::factory()->create([
            'course_id' => $this->courseMilikPengampu->id,
        ]);

        $response = $this->actingAs($this->dosenLain)
            ->get(route('dosen.assignments.show', $assignment));

        $response->assertStatus(403);
    }

    public function test_dosen_lain_ditolak_mengubah_tugas_dosen_lain(): void
    {
        $assignment = Assignment::factory()->create([
            'course_id' => $this->courseMilikPengampu->id,
        ]);

        $response = $this->actingAs($this->dosenLain)
            ->put(route('dosen.assignments.update', $assignment), [
                'title'        => 'Tugas Diubah Paksa',
                'instructions' => 'Instruksi diretas.',
                'due_at'       => now()->addDay()->format('Y-m-d H:i:s'),
                'max_score'    => 100,
                'allow_late'   => 0,
                'status'       => 'published',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('assignments', ['title' => 'Tugas Diubah Paksa']);
    }

    public function test_dosen_lain_ditolak_menghapus_tugas_dosen_lain(): void
    {
        $assignment = Assignment::factory()->create([
            'course_id' => $this->courseMilikPengampu->id,
        ]);

        $response = $this->actingAs($this->dosenLain)
            ->delete(route('dosen.assignments.destroy', $assignment));

        $response->assertStatus(403);
        $this->assertDatabaseHas('assignments', ['id' => $assignment->id]);
    }

    /* =========================================================================
     * TITIK RAWAN #8 — GET dosen/assignments/{assignment}/submissions
     * Dosen lain ditolak melihat daftar pengumpulan tugas kelas lain
     * ========================================================================= */

    public function test_dosen_lain_ditolak_melihat_daftar_submission_kelas_lain(): void
    {
        $assignment = Assignment::factory()->create([
            'course_id' => $this->courseMilikPengampu->id,
        ]);

        $response = $this->actingAs($this->dosenLain)
            ->get(route('dosen.assignments.submissions.index', $assignment));

        $response->assertStatus(403);
    }

    /* =========================================================================
     * TITIK RAWAN #9 — GET dosen/submissions/{submission}
     * Dosen lain ditolak melihat detail submission kelas lain
     * ========================================================================= */

    public function test_dosen_lain_ditolak_melihat_detail_submission_kelas_lain(): void
    {
        $assignment = Assignment::factory()->create([
            'course_id' => $this->courseMilikPengampu->id,
        ]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id'       => $this->mahasiswa->id,
        ]);

        $response = $this->actingAs($this->dosenLain)
            ->get(route('dosen.submissions.show', $submission));

        $response->assertStatus(403);
    }

    /* =========================================================================
     * TITIK RAWAN #11 — PUT dosen/grades/{grade}
     * Dosen lain ditolak mengubah nilai yang sudah diberikan dosen pengampu
     * ========================================================================= */

    public function test_dosen_lain_ditolak_mengubah_nilai_dosen_lain(): void
    {
        $assignment = Assignment::factory()->create([
            'course_id' => $this->courseMilikPengampu->id,
            'max_score' => 100,
        ]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id'       => $this->mahasiswa->id,
        ]);

        $grade = Grade::factory()->create([
            'submission_id' => $submission->id,
            'graded_by'     => $this->dosenPengampu->id,
            'score'         => 85,
        ]);

        $response = $this->actingAs($this->dosenLain)
            ->put(route('dosen.grades.update', $grade), [
                'score'    => 50,
                'feedback' => 'Nilai diubah paksa oleh dosen lain.',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('grades', [
            'id'    => $grade->id,
            'score' => 85, // Nilai tetap tidak berubah
        ]);
    }

    /* =========================================================================
     * TITIK RAWAN #22 — GET dosen/courses (index)
     * Dosen hanya melihat mata kuliah yang diampunya sendiri (Query Scoping)
     * ========================================================================= */

    public function test_dosen_hanya_melihat_kelas_yang_diampunya_sendiri(): void
    {
        $response = $this->actingAs($this->dosenLain)
            ->get(route('dosen.courses.index'));

        $response->assertStatus(200);

        // Dosen lain HANYA melihat kelas miliknya sendiri
        $response->assertSee($this->courseMilikDosenLain->name);

        // Dosen lain TIDAK boleh melihat kelas milik dosen pengampu
        $response->assertDontSee($this->courseMilikPengampu->name);
    }
}
