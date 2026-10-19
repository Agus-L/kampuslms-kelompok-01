<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Material;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MaterialUploadDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected User $dosen1;
    protected User $dosen2;
    protected User $mahasiswa1;
    protected User $mahasiswa2;
    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');

        $this->dosen1 = User::factory()->dosen()->create();
        $this->dosen2 = User::factory()->dosen()->create();
        $this->mahasiswa1 = User::factory()->mahasiswa()->create();
        $this->mahasiswa2 = User::factory()->mahasiswa()->create();

        $this->course = Course::factory()->create([
            'lecturer_id' => $this->dosen1->id,
            'status'      => 'active',
        ]);

        // mahasiswa1 terdaftar di kelas course
        $this->course->students()->attach($this->mahasiswa1->id, ['enrolled_at' => now()]);
    }

    public function test_dosen_can_upload_material_file_to_private_storage(): void
    {
        $file = UploadedFile::fake()->create('modul-minggu-9.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->dosen1)->post(
            route('dosen.courses.materials.store', $this->course),
            [
                'title'       => 'Materi Pertemuan 9 - Penyimpanan Berkas Privat',
                'description' => 'Panduan implementasi storage lokal privat',
                'type'        => 'file',
                'file'        => $file,
            ]
        );

        $response->assertRedirect(route('dosen.courses.materials.index', $this->course));
        $response->assertSessionHas('success');

        $material = Material::where('title', 'Materi Pertemuan 9 - Penyimpanan Berkas Privat')->first();
        $this->assertNotNull($material);
        $this->assertEquals('file', $material->type);
        $this->assertEquals('modul-minggu-9.pdf', $material->original_name);
        $this->assertNotNull($material->file_path);

        // Verifikasi berkas disimpan di disk privat (local), BUKAN public
        Storage::disk('local')->assertExists($material->file_path);
        Storage::disk('public')->assertMissing($material->file_path);

        // Verifikasi nama berkas fisik acak dan tersimpan di folder per-course
        $this->assertStringStartsWith('materials/courses/' . $this->course->id . '/', $material->file_path);
        $this->assertStringNotContainsString('modul-minggu-9.pdf', $material->file_path);
    }

    public function test_material_file_validation_rejects_invalid_extension(): void
    {
        $invalidFile = UploadedFile::fake()->create('skrip_berbahaya.exe', 100, 'application/octet-stream');

        $response = $this->actingAs($this->dosen1)->post(
            route('dosen.courses.materials.store', $this->course),
            [
                'title' => 'Materi Gagal Validasi',
                'type'  => 'file',
                'file'  => $invalidFile,
            ]
        );

        $response->assertSessionHasErrors(['file']);
        $this->assertDatabaseMissing('materials', ['title' => 'Materi Gagal Validasi']);
    }

    public function test_material_file_validation_rejects_oversized_file(): void
    {
        // Maksimal 20 MB (20480 KB), buat berkas 25 MB (25600 KB)
        $oversizedFile = UploadedFile::fake()->create('materi_jumbo.pdf', 25600, 'application/pdf');

        $response = $this->actingAs($this->dosen1)->post(
            route('dosen.courses.materials.store', $this->course),
            [
                'title' => 'Materi Jumbo',
                'type'  => 'file',
                'file'  => $oversizedFile,
            ]
        );

        $response->assertSessionHasErrors(['file']);
        $this->assertDatabaseMissing('materials', ['title' => 'Materi Jumbo']);
    }

    public function test_dosen_can_create_link_material_without_file(): void
    {
        $response = $this->actingAs($this->dosen1)->post(
            route('dosen.courses.materials.store', $this->course),
            [
                'title'        => 'Slide Presentasi Google Drive',
                'description'  => 'Tautan slide kuliah online',
                'type'         => 'link',
                'external_url' => 'https://docs.google.com/presentation/d/example',
            ]
        );

        $response->assertRedirect(route('dosen.courses.materials.index', $this->course));

        $material = Material::where('title', 'Slide Presentasi Google Drive')->first();
        $this->assertNotNull($material);
        $this->assertEquals('link', $material->type);
        $this->assertEquals('https://docs.google.com/presentation/d/example', $material->external_url);
        $this->assertNull($material->file_path);
        $this->assertNull($material->original_name);
    }

    public function test_enrolled_mahasiswa_can_download_material_file(): void
    {
        $filePath = 'materials/courses/' . $this->course->id . '/test_doc.pdf';
        Storage::disk('local')->put($filePath, 'konten berkas pdf materi');

        $material = Material::factory()->create([
            'course_id'     => $this->course->id,
            'uploaded_by'   => $this->dosen1->id,
            'title'         => 'Modul Kuliah 1',
            'type'          => 'file',
            'file_path'     => $filePath,
            'original_name' => 'modul_kuliah_asli.pdf',
            'file_size'     => 1024,
            'mime_type'     => 'application/pdf',
        ]);

        $response = $this->actingAs($this->mahasiswa1)->get(
            route('mahasiswa.materials.download', $material)
        );

        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=modul_kuliah_asli.pdf');
    }

    public function test_non_enrolled_mahasiswa_cannot_download_material_file(): void
    {
        $filePath = 'materials/courses/' . $this->course->id . '/secret.pdf';
        Storage::disk('local')->put($filePath, 'konten rahasia');

        $material = Material::factory()->create([
            'course_id'     => $this->course->id,
            'uploaded_by'   => $this->dosen1->id,
            'title'         => 'Materi Terbatas',
            'type'          => 'file',
            'file_path'     => $filePath,
            'original_name' => 'secret.pdf',
        ]);

        // Mahasiswa 2 tidak terdaftar di mata kuliah ini
        $response = $this->actingAs($this->mahasiswa2)->get(
            route('mahasiswa.materials.download', $material)
        );

        $response->assertForbidden();
    }

    public function test_other_dosen_cannot_download_material_file(): void
    {
        $filePath = 'materials/courses/' . $this->course->id . '/soal.docx';
        Storage::disk('local')->put($filePath, 'konten docx');

        $material = Material::factory()->create([
            'course_id'     => $this->course->id,
            'uploaded_by'   => $this->dosen1->id,
            'title'         => 'Soal Ujian',
            'type'          => 'file',
            'file_path'     => $filePath,
            'original_name' => 'soal.docx',
        ]);

        // Dosen 2 bukan pengampu course
        $response = $this->actingAs($this->dosen2)->get(
            route('dosen.materials.download', $material)
        );

        $response->assertForbidden();
    }

    public function test_dosen_can_replace_file_on_update_and_old_file_is_deleted(): void
    {
        $oldFilePath = 'materials/courses/' . $this->course->id . '/old_file.pdf';
        Storage::disk('local')->put($oldFilePath, 'versi lama');

        $material = Material::factory()->create([
            'course_id'     => $this->course->id,
            'uploaded_by'   => $this->dosen1->id,
            'title'         => 'Materi Versi Lama',
            'type'          => 'file',
            'file_path'     => $oldFilePath,
            'original_name' => 'old_file.pdf',
        ]);

        Storage::disk('local')->assertExists($oldFilePath);

        $newFile = UploadedFile::fake()->create('versi_revisi.pptx', 800, 'application/vnd.openxmlformats-officedocument.presentationml.presentation');

        $response = $this->actingAs($this->dosen1)->put(
            route('dosen.materials.update', $material),
            [
                'title' => 'Materi Versi Revisi',
                'type'  => 'file',
                'file'  => $newFile,
            ]
        );

        $response->assertRedirect(route('dosen.courses.materials.index', $this->course));

        // Verifikasi berkas lama telah dihapus dan berkas baru tersimpan
        Storage::disk('local')->assertMissing($oldFilePath);

        $material->refresh();
        $this->assertEquals('versi_revisi.pptx', $material->original_name);
        Storage::disk('local')->assertExists($material->file_path);
    }

    public function test_dosen_destroy_deletes_file_from_storage(): void
    {
        $filePath = 'materials/courses/' . $this->course->id . '/akan_dihapus.pdf';
        Storage::disk('local')->put($filePath, 'konten sebelum hapus');

        $material = Material::factory()->create([
            'course_id'     => $this->course->id,
            'uploaded_by'   => $this->dosen1->id,
            'title'         => 'Materi Dihapus',
            'type'          => 'file',
            'file_path'     => $filePath,
            'original_name' => 'akan_dihapus.pdf',
        ]);

        Storage::disk('local')->assertExists($filePath);

        $response = $this->actingAs($this->dosen1)->delete(
            route('dosen.materials.destroy', $material)
        );

        $response->assertRedirect(route('dosen.courses.materials.index', $this->course));

        // Verifikasi record DB terhapus dan file fisik di disk privat terhapus
        $this->assertDatabaseMissing('materials', ['id' => $material->id]);
        Storage::disk('local')->assertMissing($filePath);
    }
}
