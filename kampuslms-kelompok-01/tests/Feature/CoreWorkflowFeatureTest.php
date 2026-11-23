<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\AssignmentPublishedNotification;
use App\Notifications\SubmissionGradedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * CoreWorkflowFeatureTest — Uji 5 Alur Inti Pembelajaran & Titik Rawan Keamanan (Minggu 14 BUILD).
 *
 * Mencakup minimal 20 feature test:
 * 1. Autentikasi: login berhasil, login gagal, akses tanpa login (3 tests)
 * 2. CRUD Mata Kuliah: sukses oleh admin, ditolak untuk dosen/mahasiswa (3 tests)
 * 3. Enrollment: mahasiswa hanya melihat MK-nya, dosen ditolak enroll kelas lain, mahasiswa ditolak enroll (3 tests)
 * 4. Unggah Materi: sukses oleh dosen pengampu, ditolak untuk dosen lain (2 tests)
 * 5. Pengumpulan Tugas: sukses, ditolak setelah deadline bila allow_late false, ditolak untuk tidak terdaftar (3 tests)
 * 6. Unduhan: submission ditolak untuk bukan pemilik, materi ditolak untuk tidak terdaftar (2 tests)
 * 7. Penilaian: sukses oleh dosen pengampu, ditolak untuk dosen lain (2 tests)
 * 8. Notifikasi: terkirim saat tugas dibuat, terkirim saat dinilai, proteksi IDOR update notifikasi (3 tests)
 *
 * Serta mengimplementasikan 10 Prioritas Utama dari test-prioritas.md.
 */
class CoreWorkflowFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $dosen1;
    protected User $dosen2;
    protected User $mhs1;
    protected User $mhs2;
    protected Course $course1;
    protected Course $course2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        // Siapkan aktor untuk ketiga peran (Admin, Dosen, Mahasiswa)
        $this->admin = User::factory()->admin()->create(['password' => bcrypt('password')]);
        $this->dosen1 = User::factory()->dosen()->create(['password' => bcrypt('password')]);
        $this->dosen2 = User::factory()->dosen()->create(['password' => bcrypt('password')]);
        $this->mhs1 = User::factory()->mahasiswa()->create(['password' => bcrypt('password')]);
        $this->mhs2 = User::factory()->mahasiswa()->create(['password' => bcrypt('password')]);

        // Course 1 diampu oleh Dosen 1, diikuti oleh Mahasiswa 1
        $this->course1 = Course::factory()->create([
            'name'        => 'Mata Kuliah Enrolled Mahasiswa 1',
            'lecturer_id' => $this->dosen1->id,
            'status'      => 'active',
        ]);
        $this->course1->students()->attach($this->mhs1->id, ['enrolled_at' => now()]);

        // Course 2 diampu oleh Dosen 2, diikuti oleh Mahasiswa 2
        $this->course2 = Course::factory()->create([
            'name'        => 'Mata Kuliah Terisolasi Mahasiswa 2',
            'lecturer_id' => $this->dosen2->id,
            'status'      => 'active',
        ]);
        $this->course2->students()->attach($this->mhs2->id, ['enrolled_at' => now()]);
    }

    /* =========================================================================
     * 1. AUTENTIKASI (3 Tests)
     * ========================================================================= */

    public function test_login_berhasil_mengarahkan_ke_dashboard_sesuai_role(): void
    {
        $response = $this->post('/login', [
            'login'    => $this->admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertEquals('admin', auth()->user()->role);
    }

    public function test_login_gagal_dengan_kredensial_salah(): void
    {
        $response = $this->post('/login', [
            'login'    => $this->mhs1->email,
            'password' => 'password-yang-salah',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_akses_rute_terproteksi_tanpa_login_dialihkan_ke_login(): void
    {
        $responseMahasiswa = $this->get('/mahasiswa/courses');
        $responseMahasiswa->assertRedirect('/login');

        $responseDosen = $this->get('/dosen/courses');
        $responseDosen->assertRedirect('/login');

        $responseAdmin = $this->get('/admin/courses');
        $responseAdmin->assertRedirect('/login');
    }

    /* =========================================================================
     * 2. CRUD MATA KULIAH (3 Tests)
     * ========================================================================= */

    public function test_crud_mata_kuliah_sukses_oleh_admin(): void
    {
        $payload = [
            'code'        => 'IF888',
            'name'        => 'Arsitektur Perangkat Lunak',
            'description' => 'Mata kuliah desain arsitektur sistem skala besar.',
            'sks'         => 3,
            'lecturer_id' => $this->dosen1->id,
            'status'      => 'active',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.courses.store'), $payload);

        $response->assertRedirect(route('admin.courses.index'));
        $this->assertDatabaseHas('courses', [
            'code' => 'IF888',
            'name' => 'Arsitektur Perangkat Lunak',
        ]);
    }

    public function test_crud_mata_kuliah_ditolak_untuk_dosen(): void
    {
        $payload = [
            'code'        => 'IF777',
            'name'        => 'Mata Kuliah Ilegal Dosen',
            'description' => 'Dosen tidak boleh bypass otorisasi admin.',
            'sks'         => 3,
            'lecturer_id' => $this->dosen1->id,
            'status'      => 'active',
        ];

        $response = $this->actingAs($this->dosen1)->post(route('admin.courses.store'), $payload);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('courses', ['code' => 'IF777']);
    }

    public function test_crud_mata_kuliah_ditolak_untuk_mahasiswa(): void
    {
        $payload = [
            'code'        => 'IF666',
            'name'        => 'Mata Kuliah Ilegal Mahasiswa',
            'description' => 'Mahasiswa dilarang membuat mata kuliah.',
            'sks'         => 3,
            'lecturer_id' => $this->dosen1->id,
            'status'      => 'active',
        ];

        $response = $this->actingAs($this->mhs1)->post(route('admin.courses.store'), $payload);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('courses', ['code' => 'IF666']);
    }

    /* =========================================================================
     * 3. ENROLLMENT (3 Tests — Termasuk Prioritas #6 & #10)
     * ========================================================================= */

    public function test_enrollment_mahasiswa_hanya_melihat_mata_kuliahnya(): void
    {
        $response = $this->actingAs($this->mhs1)->get(route('mahasiswa.courses.index'));

        $response->assertStatus(200);
        $response->assertSee('Mata Kuliah Enrolled Mahasiswa 1');
        $response->assertDontSee('Mata Kuliah Terisolasi Mahasiswa 2');
    }

    /**
     * PRIORITAS #6: Dosen dilarang mendaftarkan mahasiswa ke kelas dosen lain (IDOR).
     */
    public function test_dosen_ditolak_enroll_mahasiswa_ke_kelas_dosen_lain(): void
    {
        $response = $this->actingAs($this->dosen2)->post(
            route('dosen.courses.enrollments.store', $this->course1),
            ['user_id' => $this->mhs2->id]
        );

        $response->assertStatus(403);
        $this->assertFalse(
            $this->course1->students()->where('user_id', $this->mhs2->id)->exists()
        );
    }

    /**
     * PRIORITAS #10: Mahasiswa dilarang keras mendaftarkan diri sendiri atau orang lain.
     */
    public function test_mahasiswa_ditolak_saat_mencoba_enroll_diri_atau_orang_lain(): void
    {
        $response = $this->actingAs($this->mhs1)->post(
            route('dosen.courses.enrollments.store', $this->course2),
            ['user_id' => $this->mhs1->id]
        );

        $response->assertStatus(403);
        $this->assertFalse(
            $this->course2->students()->where('user_id', $this->mhs1->id)->exists()
        );
    }

    /* =========================================================================
     * 4. UNGGAH MATERI (2 Tests — Termasuk Prioritas #9)
     * ========================================================================= */

    /**
     * PRIORITAS #9: Dosen pengampu berhasil mengunggah berkas modul ke private storage.
     */
    public function test_unggah_materi_sukses_oleh_dosen_pengampu(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('modul-minggu-14.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->dosen1)->post(
            route('dosen.courses.materials.store', $this->course1),
            [
                'title'       => 'Materi Testing dan Debugging',
                'description' => 'Modul panduan testing Laravel 12',
                'type'        => 'file',
                'file'        => $file,
            ]
        );

        $response->assertRedirect(route('dosen.courses.materials.index', $this->course1));
        $this->assertDatabaseHas('materials', [
            'course_id' => $this->course1->id,
            'title'     => 'Materi Testing dan Debugging',
        ]);

        $material = Material::where('course_id', $this->course1->id)->first();
        Storage::disk('local')->assertExists($material->file_path);
    }

    public function test_unggah_materi_ditolak_untuk_dosen_lain(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('modul-ilegal.pdf', 200, 'application/pdf');

        $response = $this->actingAs($this->dosen2)->post(
            route('dosen.courses.materials.store', $this->course1),
            [
                'title'       => 'Materi Sabotase Dosen 2',
                'description' => 'Mencoba upload ke kelas orang lain',
                'type'        => 'file',
                'file'        => $file,
            ]
        );

        $response->assertStatus(403);
        $this->assertDatabaseMissing('materials', [
            'title' => 'Materi Sabotase Dosen 2',
        ]);
        Storage::disk('local')->assertMissing('materials/courses/' . $this->course1->id . '/' . $file->hashName());
    }

    /* =========================================================================
     * 5. PENGUMPULAN TUGAS (3 Tests — Termasuk Prioritas #4 & #7)
     * ========================================================================= */

    /**
     * PRIORITAS #7: Mahasiswa terdaftar berhasil mengumpulkan tugas tepat waktu.
     */
    public function test_pengumpulan_tugas_sukses_oleh_mahasiswa_terdaftar(): void
    {
        Storage::fake('local');

        $assignment = Assignment::factory()->active()->create([
            'course_id'  => $this->course1->id,
            'due_at'     => now()->addDays(2),
            'allow_late' => false,
        ]);

        $file = UploadedFile::fake()->create('jawaban_budi.pdf', 400, 'application/pdf');

        $response = $this->actingAs($this->mhs1)->post(
            route('mahasiswa.assignments.submissions.store', $assignment),
            [
                'file' => $file,
                'note' => 'Berikut lembar jawaban tugas saya.',
            ]
        );

        $response->assertRedirect(route('mahasiswa.assignments.show', $assignment));
        $this->assertDatabaseHas('submissions', [
            'assignment_id' => $assignment->id,
            'user_id'       => $this->mhs1->id,
            'is_late'       => false,
        ]);

        $submission = Submission::where('assignment_id', $assignment->id)->first();
        Storage::disk('local')->assertExists($submission->file_path);
    }

    /**
     * PRIORITAS #4: Pengumpulan tugas ditolak setelah deadline jika allow_late = false (Otorisasi Policy).
     */
    public function test_pengumpulan_tugas_ditolak_setelah_deadline_bila_allow_late_false(): void
    {
        Storage::fake('local');

        $assignment = Assignment::factory()->active()->create([
            'course_id'  => $this->course1->id,
            'due_at'     => now()->subHour(),
            'allow_late' => false,
        ]);

        $file = UploadedFile::fake()->create('jawaban_terlambat.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->mhs1)->post(
            route('mahasiswa.assignments.submissions.store', $assignment),
            [
                'file' => $file,
            ]
        );

        // Ditolak oleh SubmissionPolicy (403 Forbidden)
        $response->assertStatus(403);
        $this->assertDatabaseMissing('submissions', [
            'assignment_id' => $assignment->id,
            'user_id'       => $this->mhs1->id,
        ]);
    }

    public function test_pengumpulan_tugas_ditolak_untuk_mahasiswa_tidak_terdaftar(): void
    {
        Storage::fake('local');

        $assignment = Assignment::factory()->active()->create([
            'course_id'  => $this->course1->id,
            'due_at'     => now()->addDay(),
            'allow_late' => true,
        ]);

        $file = UploadedFile::fake()->create('tugas_ilegal.pdf', 300, 'application/pdf');

        // Mahasiswa 2 tidak terdaftar di course 1
        $response = $this->actingAs($this->mhs2)->post(
            route('mahasiswa.assignments.submissions.store', $assignment),
            [
                'file' => $file,
            ]
        );

        $response->assertStatus(403);
        $this->assertDatabaseMissing('submissions', [
            'assignment_id' => $assignment->id,
            'user_id'       => $this->mhs2->id,
        ]);
        Storage::disk('local')->assertMissing('submissions/' . $assignment->id . '/' . $file->hashName());
    }

    /* =========================================================================
     * 6. UNDUHAN (2 Tests — Termasuk Prioritas #1 & #2)
     * ========================================================================= */

    /**
     * PRIORITAS #1: Mahasiswa dilarang mengunduh berkas tugas mahasiswa lain (Anti-IDOR).
     */
    public function test_unduhan_submission_ditolak_untuk_bukan_pemilik(): void
    {
        Storage::fake('local');

        $assignment = Assignment::factory()->active()->create(['course_id' => $this->course1->id]);

        $fakePath = 'submissions/' . $assignment->id . '/submission_mhs1.pdf';
        Storage::disk('local')->put($fakePath, 'Isi rahasia tugas mahasiswa 1');

        $submissionMhs1 = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id'       => $this->mhs1->id,
            'file_path'     => $fakePath,
            'original_name' => 'submission_mhs1.pdf',
        ]);

        // Mahasiswa 2 mencoba mengunduh submission milik Mahasiswa 1
        $response = $this->actingAs($this->mhs2)->get(
            route('mahasiswa.submissions.download', $submissionMhs1)
        );

        $response->assertStatus(403);
    }

    /**
     * PRIORITAS #2: Mahasiswa tidak terdaftar dilarang mengunduh berkas materi (Boundary).
     */
    public function test_unduhan_materi_ditolak_untuk_mahasiswa_tidak_terdaftar(): void
    {
        Storage::fake('local');

        $fakePath = 'materials/courses/' . $this->course1->id . '/modul_privat.pdf';
        Storage::disk('local')->put($fakePath, 'Konten modul privat kelas');

        $material = Material::factory()->create([
            'course_id'     => $this->course1->id,
            'uploaded_by'   => $this->dosen1->id,
            'type'          => 'file',
            'file_path'     => $fakePath,
            'original_name' => 'modul_privat.pdf',
        ]);

        // Mahasiswa 2 yang tidak terdaftar di course 1 mencoba download materi
        $response = $this->actingAs($this->mhs2)->get(
            route('mahasiswa.materials.download', $material)
        );

        $response->assertStatus(403);
    }

    /* =========================================================================
     * 7. PENILAIAN (2 Tests — Termasuk Prioritas #3 & #8)
     * ========================================================================= */

    /**
     * PRIORITAS #8: Dosen pengampu berhasil memberi nilai dan umpan balik.
     */
    public function test_penilaian_sukses_oleh_dosen_pengampu(): void
    {
        $assignment = Assignment::factory()->active()->create([
            'course_id' => $this->course1->id,
            'max_score' => 100,
        ]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id'       => $this->mhs1->id,
        ]);

        $response = $this->actingAs($this->dosen1)->post(
            route('dosen.submissions.grades.store', $submission),
            [
                'score'    => 90,
                'feedback' => 'Penjelasan terstruktur dan komprehensif.',
            ]
        );

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('grades', [
            'submission_id' => $submission->id,
            'graded_by'     => $this->dosen1->id,
            'score'         => 90,
            'feedback'      => 'Penjelasan terstruktur dan komprehensif.',
        ]);
    }

    /**
     * PRIORITAS #3: Dosen dilarang memberi nilai pada mata kuliah milik dosen lain.
     */
    public function test_penilaian_ditolak_untuk_dosen_lain(): void
    {
        $assignment = Assignment::factory()->active()->create([
            'course_id' => $this->course1->id,
            'max_score' => 100,
        ]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id'       => $this->mhs1->id,
        ]);

        // Dosen 2 mencoba memberi nilai pada submission di kelas Dosen 1
        $response = $this->actingAs($this->dosen2)->post(
            route('dosen.submissions.grades.store', $submission),
            [
                'score'    => 75,
                'feedback' => 'Mencoba menilai tugas kelas lain.',
            ]
        );

        $response->assertStatus(403);
        $this->assertDatabaseMissing('grades', [
            'submission_id' => $submission->id,
            'graded_by'     => $this->dosen2->id,
        ]);
    }

    /* =========================================================================
     * 8. NOTIFIKASI (3 Tests — Termasuk Prioritas #5)
     * ========================================================================= */

    public function test_notifikasi_terkirim_ke_mahasiswa_saat_tugas_diterbitkan(): void
    {
        Notification::fake();

        $payload = [
            'title'        => 'Tugas Pengenalan Database',
            'instructions' => 'Kerjakan modul 1 sampai 5.',
            'due_at'       => now()->addDays(3)->format('Y-m-d H:i:s'),
            'max_score'    => 100,
            'allow_late'   => 1,
            'status'       => 'published',
        ];

        $response = $this->actingAs($this->dosen1)->post(
            route('dosen.courses.assignments.store', $this->course1),
            $payload
        );

        $response->assertRedirect(route('dosen.courses.assignments.index', $this->course1));

        // Verifikasi notifikasi terkirim ke mahasiswa 1 yang terdaftar di course 1
        Notification::assertSentTo($this->mhs1, AssignmentPublishedNotification::class);

        // Verifikasi mahasiswa 2 (tidak terdaftar di course 1) TIDAK menerima notifikasi (Boundary Isolation)
        Notification::assertNotSentTo($this->mhs2, AssignmentPublishedNotification::class);
    }

    public function test_notifikasi_tidak_terkirim_saat_tugas_masih_berstatus_draft(): void
    {
        Notification::fake();

        $payload = [
            'title'        => 'Tugas Rancangan Belum Rilis',
            'instructions' => 'Draft instruksi tugas.',
            'due_at'       => now()->addDays(5)->format('Y-m-d H:i:s'),
            'max_score'    => 100,
            'allow_late'   => 1,
            'status'       => 'draft',
        ];

        $response = $this->actingAs($this->dosen1)->post(
            route('dosen.courses.assignments.store', $this->course1),
            $payload
        );

        $response->assertRedirect(route('dosen.courses.assignments.index', $this->course1));

        // Verifikasi tidak ada notifikasi yang terkirim saat status tugas masih draft
        Notification::assertNothingSent();
    }

    public function test_notifikasi_terkirim_ke_mahasiswa_saat_nilai_diberikan(): void
    {
        Notification::fake();

        $assignment = Assignment::factory()->active()->create(['course_id' => $this->course1->id]);
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id'       => $this->mhs1->id,
        ]);

        $this->actingAs($this->dosen1)->post(
            route('dosen.submissions.grades.store', $submission),
            [
                'score'    => 95,
                'feedback' => 'Hasil sangat memuaskan.',
            ]
        );

        // Verifikasi notifikasi nilai terkirim khusus ke mahasiswa pemilik submission
        Notification::assertSentTo($this->mhs1, SubmissionGradedNotification::class);

        // Verifikasi mahasiswa lain TIDAK menerima notifikasi nilai ini
        Notification::assertNotSentTo($this->mhs2, SubmissionGradedNotification::class);
    }

    /**
     * PRIORITAS #5: Pengguna dilarang menandai notifikasi milik orang lain sebagai dibaca (Anti-IDOR).
     */
    public function test_user_tidak_bisa_menandai_notifikasi_user_lain_sebagai_dibaca(): void
    {
        $notificationId = Str::uuid()->toString();

        // Buat notifikasi khusus milik Mahasiswa 1
        $this->mhs1->notifications()->create([
            'id'      => $notificationId,
            'type'    => AssignmentPublishedNotification::class,
            'data'    => ['message' => 'Tugas baru tersedia'],
            'read_at' => null,
        ]);

        // Mahasiswa 2 mencoba menandai notifikasi milik Mahasiswa 1 sebagai terbaca
        $response = $this->actingAs($this->mhs2)->patch("/notifications/{$notificationId}");

        $response->assertStatus(403);

        $notification = \DB::table('notifications')->where('id', $notificationId)->first();
        $this->assertNull($notification->read_at);
    }
}
