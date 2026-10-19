<?php

namespace Tests\Feature;

use App\Livewire\Courses\CourseList;
use App\Livewire\Courses\EnrollmentManager;
use App\Livewire\Courses\EnrollStudentList;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class NewFeaturesAndFixesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $dosen1;
    protected User $dosen2;
    protected User $mahasiswa1;
    protected User $mahasiswa2;
    protected Course $course1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create(['name' => 'Admin Test']);
        $this->dosen1 = User::factory()->dosen()->create(['name' => 'Dosen Satu']);
        $this->dosen2 = User::factory()->dosen()->create(['name' => 'Dosen Dua']);
        $this->mahasiswa1 = User::factory()->mahasiswa()->create(['name' => 'Mahasiswa Satu']);
        $this->mahasiswa2 = User::factory()->mahasiswa()->create(['name' => 'Mahasiswa Dua']);

        $this->course1 = Course::factory()->create([
            'lecturer_id' => $this->dosen1->id,
            'status'      => 'active',
        ]);

        $this->course1->students()->attach($this->mahasiswa1->id, ['enrolled_at' => now()]);
    }

    public function test_application_timezone_is_set_to_asia_makassar(): void
    {
        $this->assertEquals('Asia/Makassar', config('app.timezone'));
    }

    public function test_draft_assignments_are_hidden_from_mahasiswa(): void
    {
        $draftAssign = Assignment::factory()->draft()->create([
            'course_id' => $this->course1->id,
            'title'     => 'Tugas Rahasia Masih Draft',
        ]);

        $publishedAssign = Assignment::factory()->active()->create([
            'course_id' => $this->course1->id,
            'title'     => 'Tugas Publik Minggu 07',
        ]);

        // 1. Mahasiswa membuka daftar tugas di mata kuliah -> hanya muncul yang published
        $resIndex = $this->actingAs($this->mahasiswa1)->get(route('mahasiswa.courses.assignments.index', $this->course1));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('Tugas Publik Minggu 07');
        $resIndex->assertDontSee('Tugas Rahasia Masih Draft');

        // 2. Mahasiswa mencoba akses langsung ke tugas draft -> 403 Forbidden
        $resShowDraft = $this->actingAs($this->mahasiswa1)->get(route('mahasiswa.assignments.show', $draftAssign));
        $resShowDraft->assertStatus(403);

        // 3. Mahasiswa akses tugas published -> 200 OK
        $resShowPublished = $this->actingAs($this->mahasiswa1)->get(route('mahasiswa.assignments.show', $publishedAssign));
        $resShowPublished->assertStatus(200);

        // 4. Dosen pengampu tetap dapat melihat tugas draft
        $resDosen = $this->actingAs($this->dosen1)->get(route('dosen.assignments.show', $draftAssign));
        $resDosen->assertStatus(200);
    }

    public function test_mahasiswa_can_submit_assignment_and_dosen_can_grade_it(): void
    {
        Storage::fake('public');

        $assign = Assignment::factory()->active()->create([
            'course_id' => $this->course1->id,
            'max_score' => 100,
        ]);

        // 1. Mahasiswa mengunggah submission
        $fakeFile = UploadedFile::fake()->create('laporan.pdf', 500, 'application/pdf');

        $submitRes = $this->actingAs($this->mahasiswa1)->post(
            route('mahasiswa.assignments.submissions.store', $assign),
            [
                'file' => $fakeFile,
                'note' => 'Ini tugas laporan saya pak.',
            ]
        );
        $submitRes->assertRedirect(route('mahasiswa.assignments.show', $assign));

        $this->assertDatabaseHas('submissions', [
            'assignment_id' => $assign->id,
            'user_id'       => $this->mahasiswa1->id,
            'original_name' => 'laporan.pdf',
        ]);

        $submission = Submission::where('assignment_id', $assign->id)
            ->where('user_id', $this->mahasiswa1->id)
            ->firstOrFail();

        // 2. Dosen pengampu memberi nilai ke submission tersebut
        $gradeRes = $this->actingAs($this->dosen1)->post(
            route('dosen.submissions.grades.store', $submission),
            [
                'score'    => 95.5,
                'feedback' => 'Analisis sangat baik dan komprehensif.',
            ]
        );
        $gradeRes->assertSessionHas('success');

        $this->assertDatabaseHas('grades', [
            'submission_id' => $submission->id,
            'graded_by'     => $this->dosen1->id,
            'score'         => 95.5,
        ]);

        // 3. Dosen memperbarui nilai
        $grade = Grade::where('submission_id', $submission->id)->firstOrFail();
        $updateGradeRes = $this->actingAs($this->dosen1)->put(
            route('dosen.grades.update', $grade),
            [
                'score'    => 98.0,
                'feedback' => 'Revisi tambahan sempurna.',
            ]
        );
        $updateGradeRes->assertSessionHas('success');
        $this->assertDatabaseHas('grades', [
            'id'    => $grade->id,
            'score' => 98.0,
        ]);
    }

    public function test_logout_only_accepts_post_requests(): void
    {
        // 1. GET /logout should fail (405 Method Not Allowed)
        $getRes = $this->actingAs($this->mahasiswa1)->get('/logout');
        $getRes->assertStatus(405);

        // 2. POST /logout should succeed and log out
        $postRes = $this->actingAs($this->mahasiswa1)->post('/logout');
        $postRes->assertRedirect();
        $this->assertGuest();
    }

    public function test_dosen_and_admin_can_enroll_and_unenroll_student(): void
    {
        // Pastikan mahasiswa 2 belum terdaftar
        $this->assertFalse($this->course1->students()->where('user_id', $this->mahasiswa2->id)->exists());

        // 1. Dosen pengampu mendaftarkan mahasiswa 2 secara spesifik
        $enrollRes = $this->actingAs($this->dosen1)->post(
            route('dosen.courses.enrollments.store', $this->course1),
            ['user_id' => $this->mahasiswa2->id]
        );
        $enrollRes->assertSessionHas('success');
        $this->assertTrue($this->course1->students()->where('user_id', $this->mahasiswa2->id)->exists());

        // 2. Dosen lain mencoba mendaftarkan mahasiswa ke course1 -> 403
        $forbiddenRes = $this->actingAs($this->dosen2)->post(
            route('dosen.courses.enrollments.store', $this->course1),
            ['user_id' => $this->mahasiswa2->id]
        );
        $forbiddenRes->assertStatus(403);

        // 3. Dosen pengampu mengeluarkan mahasiswa 2 dari course1
        $pivot = \DB::table('course_user')
            ->where('course_id', $this->course1->id)
            ->where('user_id', $this->mahasiswa2->id)
            ->first();

        $unenrollRes = $this->actingAs($this->dosen1)->delete(
            route('dosen.enrollments.destroy', $pivot->id)
        );
        $unenrollRes->assertSessionHas('success');
        $this->assertFalse($this->course1->students()->where('user_id', $this->mahasiswa2->id)->exists());

        // 4. Admin mendaftarkan mahasiswa 2
        $adminEnrollRes = $this->actingAs($this->admin)->post(
            route('admin.courses.enrollments.store', $this->course1),
            ['user_id' => $this->mahasiswa2->id]
        );
        $adminEnrollRes->assertSessionHas('success');
        $this->assertTrue($this->course1->students()->where('user_id', $this->mahasiswa2->id)->exists());
    }

    public function test_public_courses_route_renders_livewire_course_list_with_reactivity(): void
    {
        $res = $this->get('/courses');
        $res->assertStatus(200);
        $res->assertSeeLivewire(CourseList::class);

        // Test Livewire component search & filter reactivity
        Livewire::test(CourseList::class)
            ->assertSee($this->course1->name)
            ->set('search', 'NonExistentXYZ999')
            ->assertDontSee($this->course1->name)
            ->assertSee('Tidak Ada Mata Kuliah')
            ->call('resetFilters')
            ->assertSee($this->course1->name);
    }

    public function test_mahasiswa_does_not_see_draft_stats_or_draft_filter_options(): void
    {
        Course::factory()->draft()->create([
            'lecturer_id' => $this->dosen1->id,
            'name'        => 'Mata Kuliah Draft Dosen',
        ]);

        // 1. Mahasiswa membuka halaman index mata kuliah
        $res = $this->actingAs($this->mahasiswa1)->get(route('mahasiswa.courses.index'));
        $res->assertStatus(200);
        // Pastikan tidak ada card stat Draft dan tidak ada option value="draft"
        $res->assertDontSee('<span class="stat-label">Draft</span>', false);
        $res->assertDontSee('<option value="draft"', false);

        // 2. Dosen membuka halaman index mata kuliah -> melihat opsi draft dan stat draft
        $resDosen = $this->actingAs($this->dosen1)->get(route('dosen.courses.index'));
        $resDosen->assertStatus(200);
        $resDosen->assertSee('Draft');
    }

    public function test_course_show_page_embeds_livewire_enrollment_manager_with_instant_management(): void
    {
        // Daftarkan 11 mahasiswa tambahan agar total menjadi 12 (melebihi 10 default)
        for ($i = 3; $i <= 13; $i++) {
            $m = User::factory()->create(['role' => 'mahasiswa', 'name' => "Mahasiswa Test {$i}"]);
            $this->course1->students()->attach($m->id, ['enrolled_at' => now()]);
        }

        // 1. Dosen membuka detail mata kuliah
        $res = $this->actingAs($this->dosen1)->get(route('dosen.courses.show', $this->course1));
        $res->assertStatus(200);
        $res->assertSeeLivewire(EnrollmentManager::class);
        $res->assertDontSee('Manajemen Instan (Livewire)');
        // Tombol daftarkan mengarah ke halaman pendaftaran
        $res->assertSee(route('dosen.courses.enrollments.index', $this->course1));

        // 2. Test interaksi Livewire EnrollmentManager: default 10 mahasiswa
        $component = Livewire::actingAs($this->dosen1)
            ->test(EnrollmentManager::class, ['course' => $this->course1])
            ->assertSee($this->mahasiswa1->name)
            ->assertSee('Keluarkan')
            ->assertSee('Lihat Selengkapnya')
            // Klik Lihat Selengkapnya
            ->call('toggleShowAll')
            ->assertSet('showAll', true)
            ->assertSee('Tampilkan Lebih Sedikit')
            // Keluarkan mahasiswa 1
            ->call('unenrollStudent', $this->mahasiswa1->id)
            ->assertSee("telah berhasil dikeluarkan dari kelas");

        $this->assertFalse($this->course1->students()->where('user_id', $this->mahasiswa1->id)->exists());
    }

    public function test_dedicated_enrollment_page_shows_only_unregistered_students(): void
    {
        // 1. Dosen membuka halaman pendaftaran mahasiswa
        $res = $this->actingAs($this->dosen1)->get(route('dosen.courses.enrollments.index', $this->course1));
        $res->assertStatus(200);
        $res->assertSeeLivewire(EnrollStudentList::class);

        // 2. Test Livewire EnrollStudentList
        Livewire::actingAs($this->dosen1)
            ->test(EnrollStudentList::class, ['course' => $this->course1])
            // Mahasiswa 1 sudah terdaftar, jadi TIDAK boleh muncul di daftar ini
            ->assertDontSee($this->mahasiswa1->email)
            // Mahasiswa 2 belum terdaftar, jadi HARUS muncul
            ->assertSee($this->mahasiswa2->name)
            ->assertSee('+ Daftarkan ke Kelas')
            // Daftarkan mahasiswa 2
            ->call('enrollStudent', $this->mahasiswa2->id)
            ->assertSee("Berhasil mendaftarkan {$this->mahasiswa2->name}");

        $this->assertTrue($this->course1->students()->where('user_id', $this->mahasiswa2->id)->exists());
    }
}
