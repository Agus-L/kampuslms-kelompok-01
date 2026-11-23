<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TranscriptControllerTest — Pengujian Fitur Tahap 3: Kontroller Antarmuka Web & Ekspor.
 *
 * Menguji:
 * - MahasiswaTranscriptController (index, exportPdf, Anti-IDOR, RBAC)
 * - DosenTranscriptController (show, exportPdf, Boundary Protection antar dosen, RBAC)
 * - AdminTranscriptController (show, exportPdf, Global Audit)
 */
class TranscriptControllerTest extends TestCase
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

        // 1. Inisialisasi User untuk masing-masing peran
        $this->admin  = User::factory()->create(['role' => 'admin']);
        $this->dosen1 = User::factory()->create(['role' => 'dosen']);
        $this->dosen2 = User::factory()->create(['role' => 'dosen']);
        $this->mhs1   = User::factory()->create(['role' => 'mahasiswa']);
        $this->mhs2   = User::factory()->create(['role' => 'mahasiswa']);

        // 2. Course 1 diampu dosen 1, diikuti mhs 1
        $this->course1 = Course::factory()->create([
            'lecturer_id' => $this->dosen1->id,
            'status'      => 'active',
            'code'        => 'IF101',
            'name'        => 'Pemrograman Web',
            'sks'         => 3,
        ]);
        $this->course1->students()->attach($this->mhs1->id);

        // 3. Course 2 diampu dosen 2, diikuti mhs 2
        $this->course2 = Course::factory()->create([
            'lecturer_id' => $this->dosen2->id,
            'status'      => 'active',
            'code'        => 'IF201',
            'name'        => 'Struktur Data',
            'sks'         => 3,
        ]);
        $this->course2->students()->attach($this->mhs2->id);
    }

    /* =========================================================================
     * 1. MahasiswaTranscriptController
     * ========================================================================= */

    public function test_mahasiswa_can_view_own_cumulative_transcript(): void
    {
        $response = $this->actingAs($this->mhs1)->get(route('mahasiswa.transcript.index'));

        $response->assertStatus(200);
        $response->assertViewIs('transcripts.index');
        $response->assertViewHas('data');
    }

    public function test_mahasiswa_can_access_export_pdf_endpoint(): void
    {
        $response = $this->actingAs($this->mhs1)->get(route('mahasiswa.transcript.export.pdf'));

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_access_mahasiswa_transcript(): void
    {
        $response = $this->get(route('mahasiswa.transcript.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_dosen_cannot_access_mahasiswa_cumulative_transcript_route(): void
    {
        $response = $this->actingAs($this->dosen1)->get(route('mahasiswa.transcript.index'));

        $response->assertStatus(403);
    }

    /* =========================================================================
     * 2. DosenTranscriptController & Boundary Protection
     * ========================================================================= */

    public function test_dosen_can_view_transcript_of_student_in_own_course(): void
    {
        $response = $this->actingAs($this->dosen1)->get(
            route('dosen.courses.students.transcript.show', [
                'course'  => $this->course1,
                'student' => $this->mhs1,
            ])
        );

        $response->assertStatus(200);
        $response->assertViewIs('transcripts.course');
        $response->assertViewHas('data');
    }

    public function test_dosen_can_export_transcript_of_student_in_own_course(): void
    {
        $response = $this->actingAs($this->dosen1)->get(
            route('dosen.courses.students.transcript.export.pdf', [
                'course'  => $this->course1,
                'student' => $this->mhs1,
            ])
        );

        $response->assertStatus(200);
    }

    public function test_dosen_cannot_view_transcript_in_other_lecturers_course(): void
    {
        // Dosen 2 mencoba mengakses transkrip mhs 1 di Course 1 milik Dosen 1 -> Ditolak 403
        $response = $this->actingAs($this->dosen2)->get(
            route('dosen.courses.students.transcript.show', [
                'course'  => $this->course1,
                'student' => $this->mhs1,
            ])
        );

        $response->assertStatus(403);
    }

    public function test_dosen_cannot_export_transcript_in_other_lecturers_course(): void
    {
        // Dosen 1 mencoba mengekspor Course 2 milik Dosen 2 -> Ditolak 403
        $response = $this->actingAs($this->dosen1)->get(
            route('dosen.courses.students.transcript.export.pdf', [
                'course'  => $this->course2,
                'student' => $this->mhs2,
            ])
        );

        $response->assertStatus(403);
    }

    public function test_mahasiswa_cannot_access_dosen_transcript_route(): void
    {
        $response = $this->actingAs($this->mhs1)->get(
            route('dosen.courses.students.transcript.show', [
                'course'  => $this->course1,
                'student' => $this->mhs1,
            ])
        );

        $response->assertStatus(403);
    }

    /* =========================================================================
     * 3. AdminTranscriptController
     * ========================================================================= */

    public function test_admin_can_view_any_student_transcript(): void
    {
        $response = $this->actingAs($this->admin)->get(
            route('admin.users.transcript.show', ['user' => $this->mhs1])
        );

        $response->assertStatus(200);
        $response->assertViewIs('transcripts.index');
        $response->assertViewHas('data');
    }

    public function test_admin_can_export_any_student_transcript(): void
    {
        $response = $this->actingAs($this->admin)->get(
            route('admin.users.transcript.export.pdf', ['user' => $this->mhs1])
        );

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_admin_transcript_route(): void
    {
        $response = $this->actingAs($this->mhs1)->get(
            route('admin.users.transcript.show', ['user' => $this->mhs1])
        );

        $response->assertStatus(403);
    }

    public function test_dosen_transcript_json_does_not_expose_student_email(): void
    {
        $response = $this->actingAs($this->dosen1)->getJson('/dosen/transcript');
        $response->assertOk();
        // Memastikan email mahasiswa tidak bocor ke dalam respon JSON laporan
        $response->assertJsonMissing(['email' => $this->mhs1->email]);
        $this->assertArrayNotHasKey('email', $response->json('courses.0.students.0') ?? []);
    }
}

