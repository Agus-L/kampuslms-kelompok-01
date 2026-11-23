<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\AssignmentPublishedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * MahasiswaSecurityAccessTest — HTTP Feature Test proteksi IDOR & Otorisasi seluruh rute Mahasiswa.
 *
 * File ini menguji rute-rute Mahasiswa sesuai Titik Rawan IDOR di docs/keamanan.md:
 * - Titik Rawan #12: GET mahasiswa/courses/{course}
 * - Titik Rawan #13: GET mahasiswa/courses/{course}/materials
 * - Titik Rawan #14: GET mahasiswa/materials/{material} & download
 * - Titik Rawan #15: GET mahasiswa/courses/{course}/assignments
 * - Titik Rawan #16: GET mahasiswa/assignments/{assignment}
 * - Titik Rawan #17: GET/POST mahasiswa/assignments/{assignment}/submissions (index, create, store, scoping)
 * - Titik Rawan #18: GET mahasiswa/submissions/{submission} & download
 * - Titik Rawan #19: GET/PUT mahasiswa/submissions/{submission}/edit & update
 * - Titik Rawan #20: GET mahasiswa/submissions/{submission}/grades & GET mahasiswa/courses/{course}/grades
 * - Titik Rawan #21: PATCH notifications/{notification}
 * - Titik Rawan #23: GET mahasiswa/courses (index query scoping)
 */
class MahasiswaSecurityAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $dosen;
    protected User $mahasiswaTerdaftar;
    protected User $mahasiswaSekelas;
    protected User $mahasiswaLain;
    protected Course $courseEnrolled;
    protected Course $courseLain;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        // 1. Dosen pengampu
        $this->dosen = User::factory()->dosen()->create();

        // 2. Mahasiswa Terdaftar (Budi) - terdaftar di courseEnrolled
        $this->mahasiswaTerdaftar = User::factory()->mahasiswa()->create([
            'name' => 'Budi Mahasiswa',
        ]);

        // 3. Mahasiswa Sekelas (Cici) - juga terdaftar di courseEnrolled (uji IDOR sesama mahasiswa)
        $this->mahasiswaSekelas = User::factory()->mahasiswa()->create([
            'name' => 'Cici Mahasiswa',
        ]);

        // 4. Mahasiswa Lain (Ani) - TIDAK terdaftar di courseEnrolled, terdaftar di courseLain
        $this->mahasiswaLain = User::factory()->mahasiswa()->create([
            'name' => 'Ani Mahasiswa',
        ]);

        // 5. Course Enrolled (Mata Kuliah Utama)
        $this->courseEnrolled = Course::factory()->create([
            'lecturer_id' => $this->dosen->id,
            'status'      => 'active',
            'name'        => 'Pemrograman Web Enrolled',
        ]);
        $this->courseEnrolled->students()->attach([
            $this->mahasiswaTerdaftar->id,
            $this->mahasiswaSekelas->id,
        ], ['enrolled_at' => now()]);

        // 6. Course Lain (Mata Kuliah Terpisah)
        $this->courseLain = Course::factory()->create([
            'lecturer_id' => $this->dosen->id,
            'status'      => 'active',
            'name'        => 'Basis Data Terisolasi',
        ]);
        $this->courseLain->students()->attach($this->mahasiswaLain->id, ['enrolled_at' => now()]);
    }

    /* =========================================================================
     * TITIK RAWAN #12 — GET mahasiswa/courses/{course}
     * Mahasiswa tidak terdaftar ditolak melihat detail mata kuliah
     * ========================================================================= */

    public function test_mahasiswa_tidak_terdaftar_ditolak_melihat_detail_kelas(): void
    {
        $response = $this->actingAs($this->mahasiswaLain)
            ->get(route('mahasiswa.courses.show', $this->courseEnrolled));

        $response->assertStatus(403);
    }

    public function test_mahasiswa_terdaftar_dapat_melihat_detail_kelas(): void
    {
        $response = $this->actingAs($this->mahasiswaTerdaftar)
            ->get(route('mahasiswa.courses.show', $this->courseEnrolled));

        $response->assertStatus(200);
        $response->assertSee('Pemrograman Web Enrolled');
    }

    /* =========================================================================
     * TITIK RAWAN #13 — GET mahasiswa/courses/{course}/materials
     * Mahasiswa tidak terdaftar ditolak melihat daftar materi kelas lain
     * ========================================================================= */

    public function test_mahasiswa_tidak_terdaftar_ditolak_melihat_daftar_materi_kelas_lain(): void
    {
        $response = $this->actingAs($this->mahasiswaLain)
            ->get(route('mahasiswa.courses.materials.index', $this->courseEnrolled));

        $response->assertStatus(403);
    }

    /* =========================================================================
     * TITIK RAWAN #14 — GET mahasiswa/materials/{material} & download
     * Mahasiswa tidak terdaftar ditolak melihat detail dan mengunduh berkas materi
     * ========================================================================= */

    public function test_mahasiswa_tidak_terdaftar_ditolak_melihat_detail_materi_kelas_lain(): void
    {
        $material = Material::factory()->create([
            'course_id'   => $this->courseEnrolled->id,
            'uploaded_by' => $this->dosen->id,
            'title'       => 'Materi Kuliah Rahasia',
        ]);

        $response = $this->actingAs($this->mahasiswaLain)
            ->get(route('mahasiswa.materials.show', $material));

        $response->assertStatus(403);
    }

    public function test_mahasiswa_tidak_terdaftar_ditolak_mengunduh_berkas_materi_kelas_lain(): void
    {
        Storage::fake('local');
        $filePath = 'materials/courses/' . $this->courseEnrolled->id . '/slide.pdf';
        Storage::disk('local')->put($filePath, 'PDF Content');

        $material = Material::factory()->create([
            'course_id'     => $this->courseEnrolled->id,
            'uploaded_by'   => $this->dosen->id,
            'type'          => 'file',
            'file_path'     => $filePath,
            'original_name' => 'slide.pdf',
        ]);

        $response = $this->actingAs($this->mahasiswaLain)
            ->get(route('mahasiswa.materials.download', $material));

        $response->assertStatus(403);
    }

    /* =========================================================================
     * TITIK RAWAN #15 — GET mahasiswa/courses/{course}/assignments
     * Mahasiswa tidak terdaftar ditolak melihat daftar tugas kelas lain
     * ========================================================================= */

    public function test_mahasiswa_tidak_terdaftar_ditolak_melihat_daftar_tugas_kelas_lain(): void
    {
        $response = $this->actingAs($this->mahasiswaLain)
            ->get(route('mahasiswa.courses.assignments.index', $this->courseEnrolled));

        $response->assertStatus(403);
    }

    /* =========================================================================
     * TITIK RAWAN #16 — GET mahasiswa/assignments/{assignment}
     * Mahasiswa tidak terdaftar ditolak melihat detail tugas kelas lain
     * ========================================================================= */

    public function test_mahasiswa_tidak_terdaftar_ditolak_melihat_detail_tugas_kelas_lain(): void
    {
        $assignment = Assignment::factory()->active()->create([
            'course_id' => $this->courseEnrolled->id,
            'title'     => 'Tugas Proyek Akhir',
        ]);

        $response = $this->actingAs($this->mahasiswaLain)
            ->get(route('mahasiswa.assignments.show', $assignment));

        $response->assertStatus(403);
    }

    /* =========================================================================
     * TITIK RAWAN #17 — GET/POST mahasiswa/assignments/{assignment}/submissions
     * Mahasiswa tidak terdaftar ditolak mengakses pengumpulan tugas kelas lain,
     * dan mahasiswa terdaftar hanya melihat submission miliknya (Query Scoping)
     * ========================================================================= */

    public function test_mahasiswa_tidak_terdaftar_ditolak_melihat_daftar_submission_tugas_kelas_lain(): void
    {
        $assignment = Assignment::factory()->active()->create([
            'course_id' => $this->courseEnrolled->id,
        ]);

        $response = $this->actingAs($this->mahasiswaLain)
            ->get(route('mahasiswa.assignments.submissions.index', $assignment));

        $response->assertStatus(403);
    }

    public function test_mahasiswa_tidak_terdaftar_ditolak_membuka_form_create_submission_tugas_kelas_lain(): void
    {
        $assignment = Assignment::factory()->active()->create([
            'course_id' => $this->courseEnrolled->id,
        ]);

        $response = $this->actingAs($this->mahasiswaLain)
            ->get(route('mahasiswa.assignments.submissions.create', $assignment));

        $response->assertStatus(403);
    }

    public function test_mahasiswa_tidak_terdaftar_ditolak_mengumpulkan_tugas_ke_kelas_lain(): void
    {
        Storage::fake('local');
        $assignment = Assignment::factory()->active()->create([
            'course_id' => $this->courseEnrolled->id,
        ]);

        $response = $this->actingAs($this->mahasiswaLain)->post(
            route('mahasiswa.assignments.submissions.store', $assignment),
            [
                'file' => UploadedFile::fake()->create('jawaban_ilegal.pdf', 100, 'application/pdf'),
                'note' => 'Mencoba mengumpulkan ke kelas lain',
            ]
        );

        $response->assertStatus(403);
        $this->assertDatabaseMissing('submissions', [
            'assignment_id' => $assignment->id,
            'user_id'       => $this->mahasiswaLain->id,
        ]);
    }

    public function test_mahasiswa_hanya_melihat_submission_miliknya_sendiri_pada_index_submission(): void
    {
        $assignment = Assignment::factory()->active()->create([
            'course_id' => $this->courseEnrolled->id,
        ]);

        // Submission milik Budi
        Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id'       => $this->mahasiswaTerdaftar->id,
            'original_name' => 'jawaban_tugas_budi.pdf',
        ]);

        // Submission milik Cici (teman sekelas)
        Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id'       => $this->mahasiswaSekelas->id,
            'original_name' => 'jawaban_tugas_cici.pdf',
        ]);

        $response = $this->actingAs($this->mahasiswaTerdaftar)
            ->get(route('mahasiswa.assignments.submissions.index', $assignment));

        $response->assertStatus(200);
        $response->assertSee('jawaban_tugas_budi.pdf');
        $response->assertDontSee('jawaban_tugas_cici.pdf');
        $response->assertSee($this->mahasiswaTerdaftar->name);
        $response->assertDontSee($this->mahasiswaSekelas->name);
    }

    /* =========================================================================
     * TITIK RAWAN #18 — GET mahasiswa/submissions/{submission} & download
     * Mahasiswa lain ditolak melihat detail dan mengunduh berkas submission milik orang lain (Anti-IDOR / Anti-Plagiasi)
     * ========================================================================= */

    public function test_mahasiswa_lain_ditolak_melihat_detail_submission_orang_lain(): void
    {
        $assignment = Assignment::factory()->active()->create([
            'course_id' => $this->courseEnrolled->id,
        ]);

        $submissionBudi = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id'       => $this->mahasiswaTerdaftar->id,
            'note'          => 'Submission Asli Milik Budi',
        ]);

        // Cici (teman sekelas) mencoba melihat detail submission Budi
        $response = $this->actingAs($this->mahasiswaSekelas)
            ->get(route('mahasiswa.submissions.show', $submissionBudi));

        $response->assertStatus(403);
    }

    public function test_mahasiswa_lain_ditolak_mengunduh_berkas_submission_orang_lain(): void
    {
        Storage::fake('local');
        $assignment = Assignment::factory()->active()->create([
            'course_id' => $this->courseEnrolled->id,
        ]);

        $fakePath = 'submissions/' . $assignment->id . '/jawaban_budi.pdf';
        Storage::disk('local')->put($fakePath, 'Isi rahasia lembar jawaban Budi');

        $submissionBudi = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id'       => $this->mahasiswaTerdaftar->id,
            'file_path'     => $fakePath,
            'original_name' => 'jawaban_budi.pdf',
        ]);

        // Cici mencoba mengunduh berkas submission Budi (percobaan plagiasi via IDOR)
        $response = $this->actingAs($this->mahasiswaSekelas)
            ->get(route('mahasiswa.submissions.download', $submissionBudi));

        $response->assertStatus(403);
    }

    /* =========================================================================
     * TITIK RAWAN #19 — GET/PUT mahasiswa/submissions/{submission}/edit & update
     * Mahasiswa lain ditolak membuka form edit atau mengubah submission orang lain
     * ========================================================================= */

    public function test_mahasiswa_lain_ditolak_membuka_form_edit_submission_orang_lain(): void
    {
        $assignment = Assignment::factory()->active()->create([
            'course_id' => $this->courseEnrolled->id,
        ]);

        $submissionBudi = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id'       => $this->mahasiswaTerdaftar->id,
        ]);

        $response = $this->actingAs($this->mahasiswaSekelas)
            ->get(route('mahasiswa.submissions.edit', $submissionBudi));

        $response->assertStatus(403);
    }

    public function test_mahasiswa_lain_ditolak_mengubah_submission_orang_lain(): void
    {
        $assignment = Assignment::factory()->active()->create([
            'course_id' => $this->courseEnrolled->id,
        ]);

        $submissionBudi = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id'       => $this->mahasiswaTerdaftar->id,
            'note'          => 'Catatan Asli Budi',
        ]);

        $response = $this->actingAs($this->mahasiswaSekelas)
            ->put(route('mahasiswa.submissions.update', $submissionBudi), [
                'note' => 'Catatan dibajak oleh Cici',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('submissions', [
            'id'   => $submissionBudi->id,
            'note' => 'Catatan dibajak oleh Cici',
        ]);
        $this->assertDatabaseHas('submissions', [
            'id'   => $submissionBudi->id,
            'note' => 'Catatan Asli Budi',
        ]);
    }

    /* =========================================================================
     * TITIK RAWAN #20 — GET mahasiswa/submissions/{submission}/grades & courses/{course}/grades
     * Mahasiswa lain ditolak melihat nilai tugas orang lain dan rekap nilai kelas lain
     * ========================================================================= */

    public function test_mahasiswa_lain_ditolak_melihat_nilai_submission_orang_lain(): void
    {
        $assignment = Assignment::factory()->active()->create([
            'course_id' => $this->courseEnrolled->id,
            'max_score' => 100,
        ]);

        $submissionBudi = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id'       => $this->mahasiswaTerdaftar->id,
        ]);

        Grade::factory()->create([
            'submission_id' => $submissionBudi->id,
            'graded_by'     => $this->dosen->id,
            'score'         => 98,
            'feedback'      => 'Sempurna Budi!',
        ]);

        // Cici mencoba mengintip nilai Budi via URL endpoint grades
        $response = $this->actingAs($this->mahasiswaSekelas)
            ->get(route('mahasiswa.submissions.grades.index', $submissionBudi));

        $response->assertStatus(403);
    }

    public function test_mahasiswa_tidak_terdaftar_ditolak_melihat_rekap_nilai_kelas_lain(): void
    {
        // Ani (mahasiswa luar kelas) mencoba melihat rekap nilai kelas Enrolled
        $response = $this->actingAs($this->mahasiswaLain)
            ->get(route('mahasiswa.courses.grades.index', $this->courseEnrolled));

        $response->assertStatus(403);
    }

    /* =========================================================================
     * TITIK RAWAN #21 — PATCH notifications/{notification}
     * Mahasiswa ditolak menandai notifikasi milik mahasiswa lain sebagai dibaca
     * ========================================================================= */

    public function test_mahasiswa_tidak_bisa_menandai_notifikasi_mahasiswa_lain_sebagai_dibaca(): void
    {
        $notificationId = Str::uuid()->toString();

        $this->mahasiswaTerdaftar->notifications()->create([
            'id'      => $notificationId,
            'type'    => AssignmentPublishedNotification::class,
            'data'    => ['message' => 'Tugas baru telah diumumkan.'],
            'read_at' => null,
        ]);

        $response = $this->actingAs($this->mahasiswaLain)
            ->patch(route('notifications.update', $notificationId));

        $response->assertStatus(403);

        $notification = DB::table('notifications')->where('id', $notificationId)->first();
        $this->assertNull($notification->read_at);
    }

    /* =========================================================================
     * TITIK RAWAN #23 — GET mahasiswa/courses (index)
     * Mahasiswa hanya melihat mata kuliah yang diikutinya sendiri (Query Scoping)
     * ========================================================================= */

    public function test_mahasiswa_hanya_melihat_mata_kuliah_yang_diikutinya_sendiri(): void
    {
        $response = $this->actingAs($this->mahasiswaTerdaftar)
            ->get(route('mahasiswa.courses.index'));

        $response->assertStatus(200);

        // Mahasiswa Budi melihat kelas yang diikutinya
        $response->assertSee($this->courseEnrolled->name);

        // Mahasiswa Budi TIDAK boleh melihat kelas lain yang tidak diikutinya
        $response->assertDontSee($this->courseLain->name);
    }
}
