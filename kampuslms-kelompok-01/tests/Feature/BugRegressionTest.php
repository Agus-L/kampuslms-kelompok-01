<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * BugRegressionTest — Pengujian Regresi Otomatis untuk 3 Kasus Bug Nyata (Minggu 14 BUILD No. 4 & 5).
 *
 * Menguji:
 * 1. Kasus Bug 1: Pencegahan kebocoran email mahasiswa pada endpoint laporan transkrip nilai (Data Exposure Prevention).
 * 2. Kasus Bug 2: Integritas pembuatan mata kuliah saat input deskripsi kosong atau validasi form (Database Constraint Handling).
 * 3. Kasus Bug 3: Pencegahan celah IDOR pada pengunduhan berkas submission mahasiswa (IDOR Prevention pada Download).
 */
class BugRegressionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $dosen;
    private User $mahasiswa1;
    private User $mahasiswa2;
    private Course $course;
    private Assignment $assignment;
    private Submission $submissionMhs1;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Inisialisasi aktor peran
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin Testing',
        ]);

        $this->dosen = User::factory()->create([
            'role' => 'dosen',
            'name' => 'Dosen Pengampu Testing',
        ]);

        $this->mahasiswa1 = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Mahasiswa Satu Testing',
            'email' => 'mahasiswa1_test@kampuslms.test',
        ]);

        $this->mahasiswa2 = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Mahasiswa Dua Testing',
            'email' => 'mahasiswa2_test@kampuslms.test',
        ]);

        // 2. Mata kuliah aktif
        $this->course = Course::factory()->create([
            'lecturer_id' => $this->dosen->id,
            'status' => 'active',
            'code' => 'REG' . rand(100, 999),
            'name' => 'Arsitektur Web Terapan',
            'description' => 'Mata kuliah khusus pengujian regresi.',
            'sks' => 3,
        ]);

        // Pendaftaran mahasiswa 1 ke mata kuliah
        $this->course->students()->attach($this->mahasiswa1->id);

        // 3. Tugas kuliah
        $this->assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'title' => 'Tugas Regresi 1',
            'status' => 'published',
            'due_at' => now()->addDays(3),
        ]);

        // 4. File disk palsu untuk pengujian unduhan berkas
        Storage::fake('local');
        $fakePath = 'submissions/' . $this->assignment->id . '/berkas_mhs1.pdf';
        Storage::disk('local')->put($fakePath, 'DUMMY PDF CONTENT FOR REGRESSION TEST');

        // Submission milik mahasiswa 1
        $this->submissionMhs1 = Submission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'user_id' => $this->mahasiswa1->id,
            'file_path' => $fakePath,
            'original_name' => 'tugas_mhs1.pdf',
        ]);
    }

    /**
     * Uji Regresi Bug 1: Endpoint transkrip dosen tidak boleh membocorkan atribut email mahasiswa ke respons JSON.
     */
    public function test_bug_1_transcript_api_response_does_not_leak_student_email(): void
    {
        $response = $this->actingAs($this->dosen)->getJson('/dosen/transcript');

        $response->assertOk();

        // Verifikasi tidak ada kunci email mahasiswa dalam respons JSON transkrip
        $response->assertJsonMissing([
            'email' => $this->mahasiswa1->email,
        ]);

        $firstStudentPayload = $response->json('courses.0.students.0') ?? [];
        $this->assertArrayNotHasKey(
            'email',
            $firstStudentPayload,
            'Kolom email mahasiswa bocor pada muatan JSON transkrip dosen!'
        );
    }

    /**
     * Uji Regresi Bug 2: Pembuatan mata kuliah menangani deskripsi kosong secara aman tanpa memicu crash unhandled 500.
     */
    public function test_bug_2_course_creation_handles_description_safely_without_database_crash(): void
    {
        $uniqueCode = 'MK' . rand(1000, 9999);

        // Pengujian simpan mata kuliah dengan deskripsi kosong
        $response = $this->actingAs($this->admin)->post(route('admin.courses.store'), [
            'code' => $uniqueCode,
            'name' => 'Sistem Terdistribusi Lanjutan',
            'description' => null, // Sengaja null untuk memverifikasi penanganan constraint database
            'sks' => 3,
            'lecturer_id' => $this->dosen->id,
            'status' => 'active',
        ]);

        // Sistem harus merespons dengan redirect sukses (bukan HTTP 500 unhandled database exception)
        $response->assertRedirect();
        $this->assertDatabaseHas('courses', [
            'code' => $uniqueCode,
            'name' => 'Sistem Terdistribusi Lanjutan',
        ]);

        // Pengujian validasi input tidak valid: SKS melebihi batas harus dicegat form request (bukan meledak di database)
        $invalidResponse = $this->actingAs($this->admin)->post(route('admin.courses.store'), [
            'code' => 'INVALID' . rand(10, 99),
            'name' => 'Mata Kuliah Cacat',
            'sks' => 99, // Melebihi batas max:6
            'lecturer_id' => $this->dosen->id,
            'status' => 'active',
        ]);

        $invalidResponse->assertSessionHasErrors(['sks']);
    }

    /**
     * Uji Regresi Bug 3: Mahasiswa lain dilarang mengunduh berkas jawaban tugas mahasiswa lain (Anti-IDOR pada download).
     */
    public function test_bug_3_student_cannot_download_other_students_submission_preventing_idor(): void
    {
        // Mahasiswa 2 mencoba mengunduh berkas milik Mahasiswa 1
        $response = $this->actingAs($this->mahasiswa2)->get(
            route('mahasiswa.submissions.download', $this->submissionMhs1)
        );

        // Sistem wajib menolak dengan status 403 Forbidden
        $response->assertStatus(403);

        // Sebaliknya, Mahasiswa 1 (pemilik berkas yang sah) harus dapat mengunduh berkasnya sendiri
        $ownerResponse = $this->actingAs($this->mahasiswa1)->get(
            route('mahasiswa.submissions.download', $this->submissionMhs1)
        );

        $ownerResponse->assertOk();
    }
}
