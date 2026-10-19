<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * FileDeletionCascadeTest — Poin 6 BUILD Minggu 9.
 *
 * Membuktikan bahwa penghapusan berkas fisik di disk privat terjadi secara
 * otomatis saat record dihapus, termasuk penanganan kasus cascade:
 *
 * Skenario yang diuji:
 * 1. Hapus Material → berkas materi di disk privat ikut terhapus.
 * 2. Hapus Submission → berkas submission di disk privat ikut terhapus.
 * 3. Hapus Assignment (cascade) → seluruh berkas submission anak terhapus.
 * 4. Hapus Course (cascade dua tingkat) → seluruh berkas materi dan submission terhapus.
 */
class FileDeletionCascadeTest extends TestCase
{
    use RefreshDatabase;

    protected User $dosen;
    protected User $mahasiswa1;
    protected User $mahasiswa2;
    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->dosen = User::factory()->dosen()->create();
        $this->mahasiswa1 = User::factory()->mahasiswa()->create();
        $this->mahasiswa2 = User::factory()->mahasiswa()->create();

        $this->course = Course::factory()->create([
            'lecturer_id' => $this->dosen->id,
            'status'      => 'active',
        ]);

        $this->course->students()->attach($this->mahasiswa1->id, ['enrolled_at' => now()]);
        $this->course->students()->attach($this->mahasiswa2->id, ['enrolled_at' => now()]);
    }

    /* ================================================================== *
     |  Skenario 1: Hapus Material → berkas fisik di disk ikut terhapus
     * ================================================================== */

    public function test_material_deletion_removes_physical_file(): void
    {
        $filePath = 'materials/courses/' . $this->course->id . '/modul_test.pdf';
        Storage::disk('local')->put($filePath, 'konten berkas materi test');

        $material = Material::factory()->create([
            'course_id'   => $this->course->id,
            'uploaded_by' => $this->dosen->id,
            'title'       => 'Materi Test Hapus',
            'type'        => 'file',
            'file_path'   => $filePath,
            'original_name' => 'modul_test.pdf',
            'file_size'   => 1024,
            'mime_type'   => 'application/pdf',
        ]);

        // Pastikan berkas ada sebelum dihapus
        Storage::disk('local')->assertExists($filePath);

        // Hapus record materi
        $material->delete();

        // Berkas fisik harus ikut terhapus
        Storage::disk('local')->assertMissing($filePath);
        $this->assertDatabaseMissing('materials', ['id' => $material->id]);
    }

    public function test_material_link_type_deletion_does_not_cause_error(): void
    {
        $material = Material::factory()->create([
            'course_id'   => $this->course->id,
            'uploaded_by' => $this->dosen->id,
            'title'       => 'Materi Link Test',
            'type'        => 'link',
            'file_path'   => null,
            'external_url' => 'https://example.com/slide',
        ]);

        // Hapus materi bertipe link — tidak boleh error meskipun file_path null
        $material->delete();

        $this->assertDatabaseMissing('materials', ['id' => $material->id]);
    }

    /* ================================================================== *
     |  Skenario 2: Hapus Submission → berkas fisik di disk ikut terhapus
     * ================================================================== */

    public function test_submission_deletion_removes_physical_file(): void
    {
        $assignment = Assignment::factory()->create([
            'course_id'  => $this->course->id,
            'created_by' => $this->dosen->id,
            'status'     => 'published',
        ]);

        $filePath = "submissions/{$assignment->id}/tugas_mhs.pdf";
        Storage::disk('local')->put($filePath, 'konten tugas mahasiswa');

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id'       => $this->mahasiswa1->id,
            'file_path'     => $filePath,
            'original_name' => 'tugas_mhs.pdf',
            'file_size'     => 2048,
            'submitted_at'  => now(),
        ]);

        Storage::disk('local')->assertExists($filePath);

        // Hapus submission
        $submission->delete();

        // Berkas fisik harus ikut terhapus
        Storage::disk('local')->assertMissing($filePath);
        $this->assertDatabaseMissing('submissions', ['id' => $submission->id]);
    }

    /* ================================================================== *
     |  Skenario 3: Hapus Assignment (CASCADE) → seluruh berkas submission terhapus
     * ================================================================== */

    public function test_assignment_cascade_deletion_removes_all_submission_files(): void
    {
        $assignment = Assignment::factory()->create([
            'course_id'  => $this->course->id,
            'created_by' => $this->dosen->id,
            'status'     => 'published',
        ]);

        // Mahasiswa 1 mengumpulkan tugas
        $filePath1 = "submissions/{$assignment->id}/tugas_mhs1.pdf";
        Storage::disk('local')->put($filePath1, 'konten tugas mahasiswa 1');

        $sub1 = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id'       => $this->mahasiswa1->id,
            'file_path'     => $filePath1,
            'original_name' => 'tugas_mhs1.pdf',
            'file_size'     => 1024,
            'submitted_at'  => now(),
        ]);

        // Mahasiswa 2 mengumpulkan tugas
        $filePath2 = "submissions/{$assignment->id}/tugas_mhs2.pdf";
        Storage::disk('local')->put($filePath2, 'konten tugas mahasiswa 2');

        $sub2 = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id'       => $this->mahasiswa2->id,
            'file_path'     => $filePath2,
            'original_name' => 'tugas_mhs2.pdf',
            'file_size'     => 2048,
            'submitted_at'  => now(),
        ]);

        // Pastikan kedua berkas ada
        Storage::disk('local')->assertExists($filePath1);
        Storage::disk('local')->assertExists($filePath2);

        // Dosen menghapus TUGAS (bukan submission satu-satu)
        $assignment->delete();

        // Seluruh berkas submission harus ikut terhapus dari disk
        Storage::disk('local')->assertMissing($filePath1);
        Storage::disk('local')->assertMissing($filePath2);

        // Record submission juga harus sudah tidak ada di database
        $this->assertDatabaseMissing('submissions', ['id' => $sub1->id]);
        $this->assertDatabaseMissing('submissions', ['id' => $sub2->id]);
        $this->assertDatabaseMissing('assignments', ['id' => $assignment->id]);
    }

    /* ================================================================== *
     |  Skenario 4: Hapus Course (CASCADE DUA TINGKAT)
     |  → seluruh berkas materi DAN submission terhapus bersih
     * ================================================================== */

    public function test_course_cascade_deletion_removes_all_material_and_submission_files(): void
    {
        // --- Materi kuliah ---
        $materialPath = 'materials/courses/' . $this->course->id . '/slide_kuliah.pptx';
        Storage::disk('local')->put($materialPath, 'konten slide pptx');

        $material = Material::factory()->create([
            'course_id'     => $this->course->id,
            'uploaded_by'   => $this->dosen->id,
            'title'         => 'Slide Kuliah Pertemuan 1',
            'type'          => 'file',
            'file_path'     => $materialPath,
            'original_name' => 'slide_kuliah.pptx',
            'file_size'     => 5120,
            'mime_type'     => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ]);

        // --- Tugas + Submission ---
        $assignment = Assignment::factory()->create([
            'course_id'  => $this->course->id,
            'created_by' => $this->dosen->id,
            'status'     => 'published',
        ]);

        $subPath = "submissions/{$assignment->id}/tugas_cascade.pdf";
        Storage::disk('local')->put($subPath, 'konten tugas cascade test');

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id'       => $this->mahasiswa1->id,
            'file_path'     => $subPath,
            'original_name' => 'tugas_cascade.pdf',
            'file_size'     => 3072,
            'submitted_at'  => now(),
        ]);

        // Pastikan semua berkas ada sebelum dihapus
        Storage::disk('local')->assertExists($materialPath);
        Storage::disk('local')->assertExists($subPath);

        // Admin/Dosen menghapus MATA KULIAH (induk tertinggi)
        $this->course->delete();

        // Seluruh berkas fisik harus bersih dari disk
        Storage::disk('local')->assertMissing($materialPath);
        Storage::disk('local')->assertMissing($subPath);

        // Semua record anak harus sudah tidak ada di database
        $this->assertDatabaseMissing('materials', ['id' => $material->id]);
        $this->assertDatabaseMissing('assignments', ['id' => $assignment->id]);
        $this->assertDatabaseMissing('submissions', ['id' => $submission->id]);
        $this->assertDatabaseMissing('courses', ['id' => $this->course->id]);
    }

    /* ================================================================== *
     |  Skenario 5: Hapus Assignment via Controller (simulasi Dosen klik 🗑️ Hapus)
     * ================================================================== */

    public function test_dosen_destroy_assignment_via_web_cleans_submission_files(): void
    {
        $assignment = Assignment::factory()->create([
            'course_id'  => $this->course->id,
            'created_by' => $this->dosen->id,
            'status'     => 'published',
        ]);

        $filePath = "submissions/{$assignment->id}/tugas_web_test.pdf";
        Storage::disk('local')->put($filePath, 'konten tugas lewat web');

        Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id'       => $this->mahasiswa1->id,
            'file_path'     => $filePath,
            'original_name' => 'tugas_web_test.pdf',
            'file_size'     => 1024,
            'submitted_at'  => now(),
        ]);

        Storage::disk('local')->assertExists($filePath);

        // Dosen mengklik tombol 🗑️ Hapus di halaman tugas (simulasi HTTP request)
        $response = $this->actingAs($this->dosen)->delete(
            route('dosen.assignments.destroy', $assignment)
        );

        $response->assertRedirect(route('dosen.courses.assignments.index', $this->course));

        // Berkas submission harus ikut terhapus dari disk
        Storage::disk('local')->assertMissing($filePath);
        $this->assertDatabaseMissing('assignments', ['id' => $assignment->id]);
    }
}
