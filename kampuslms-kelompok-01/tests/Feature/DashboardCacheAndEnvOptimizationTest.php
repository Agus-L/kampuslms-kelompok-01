<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Database\Seeders\DemoAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardCacheAndEnvOptimizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build #6: Dashboard admin menyimpan agregat count ke cache.
     */
    public function test_admin_dashboard_stats_are_cached(): void
    {
        Cache::flush();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertFalse(Cache::has('admin.dashboard.stats'));

        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertOk();

        $this->assertTrue(Cache::has('admin.dashboard.stats'));
        $cachedStats = Cache::get('admin.dashboard.stats');

        $this->assertIsArray($cachedStats);
        $this->assertArrayHasKey('total_users', $cachedStats);
        $this->assertArrayHasKey('total_admin', $cachedStats);
        $this->assertArrayHasKey('total_courses', $cachedStats);
        $this->assertArrayHasKey('active_courses', $cachedStats);
        $this->assertArrayHasKey('total_materials', $cachedStats);
        $this->assertArrayHasKey('total_assignments', $cachedStats);
        $this->assertArrayHasKey('total_submissions', $cachedStats);
    }

    /**
     * Build #6: Query berkurang drastis saat cache aktif pada dashboard admin.
     */
    public function test_admin_dashboard_uses_cache_on_subsequent_requests(): void
    {
        Cache::flush();
        $admin = User::factory()->create(['role' => 'admin']);

        // Request pertama: query dijalankan dan dimasukkan ke cache
        $this->actingAs($admin)->get('/dashboard');

        // Request kedua: statistik diambil dari cache
        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->actingAs($admin)->get('/dashboard');

        $queryLog = DB::getQueryLog();
        DB::disableQueryLog();

        // Tidak boleh ada kueri aggregate COUNT pada users, courses, assignments, submissions
        $queries = array_column($queryLog, 'query');
        $hasCountUsersQuery = false;
        foreach ($queries as $sql) {
            if (stripos($sql, 'count(') !== false && stripos($sql, 'users') !== false) {
                $hasCountUsersQuery = true;
                break;
            }
        }

        $this->assertFalse($hasCountUsersQuery, 'Kueri COUNT users seharusnya tidak dijalankan saat cache aktif.');
    }

    /**
     * Build #6: Invalidation otomatis saat model User berubah.
     */
    public function test_cache_is_invalidated_when_user_created_or_deleted(): void
    {
        Cache::put('admin.dashboard.stats', ['cached' => true], 600);
        $this->assertTrue(Cache::has('admin.dashboard.stats'));

        $newUser = User::factory()->create(['role' => 'mahasiswa']);
        $this->assertFalse(Cache::has('admin.dashboard.stats'), 'Cache harus dibersihkan saat user baru dibuat.');

        Cache::put('admin.dashboard.stats', ['cached' => true], 600);
        $newUser->delete();
        $this->assertFalse(Cache::has('admin.dashboard.stats'), 'Cache harus dibersihkan saat user dihapus.');
    }

    /**
     * Build #6: Invalidation otomatis saat Course berubah.
     */
    public function test_cache_is_invalidated_when_course_changes(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);

        Cache::put('admin.dashboard.stats', ['cached' => true], 600);
        $course = Course::factory()->create(['lecturer_id' => $dosen->id]);
        $this->assertFalse(Cache::has('admin.dashboard.stats'), 'Cache harus dibersihkan saat course dibuat.');

        Cache::put('admin.dashboard.stats', ['cached' => true], 600);
        $course->delete();
        $this->assertFalse(Cache::has('admin.dashboard.stats'), 'Cache harus dibersihkan saat course dihapus.');
    }

    /**
     * Build #6: Invalidation otomatis saat Assignment atau Submission berubah.
     */
    public function test_cache_is_invalidated_when_assignment_or_submission_changes(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $course = Course::factory()->create(['lecturer_id' => $dosen->id]);
        $student = User::factory()->create(['role' => 'mahasiswa']);

        Cache::put('admin.dashboard.stats', ['cached' => true], 600);
        $assignment = Assignment::factory()->create(['course_id' => $course->id]);
        $this->assertFalse(Cache::has('admin.dashboard.stats'), 'Cache harus dibersihkan saat assignment dibuat.');

        Cache::put('admin.dashboard.stats', ['cached' => true], 600);
        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id'       => $student->id,
            'file_path'     => 'submissions/dummy.pdf',
            'original_name' => 'dummy.pdf',
            'file_size'     => 1024,
            'mime_type'     => 'application/pdf',
            'submitted_at'  => now(),
            'is_late'       => false,
        ]);
        $this->assertFalse(Cache::has('admin.dashboard.stats'), 'Cache harus dibersihkan saat submission dibuat.');

        Cache::put('admin.dashboard.stats', ['cached' => true], 600);
        $submission->delete();
        $this->assertFalse(Cache::has('admin.dashboard.stats'), 'Cache harus dibersihkan saat submission dihapus.');
    }

    /**
     * Build #7: Tidak ada fungsi env() yang dipanggil di luar config/.
     */
    public function test_no_env_calls_outside_config_directory(): void
    {
        $baseDir = base_path();
        $scanDirs = ['app', 'database', 'resources', 'routes', 'bootstrap'];

        $violations = [];

        foreach ($scanDirs as $dir) {
            $path = $baseDir . DIRECTORY_SEPARATOR . $dir;
            if (!is_dir($path)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $content = file_get_contents($file->getRealPath());
                    // Cek pola pemanggilan env(...)
                    if (preg_match('/\benv\s*\(/', $content)) {
                        $violations[] = str_replace($baseDir . DIRECTORY_SEPARATOR, '', $file->getRealPath());
                    }
                }
            }
        }

        $this->assertEmpty(
            $violations,
            'Ditemukan fungsi env() dipanggil di luar config/: ' . implode(', ', $violations)
        );
    }

    /**
     * Build #7: DemoAccountSeeder menggunakan config('auth.demo_password').
     */
    public function test_demo_account_seeder_uses_config(): void
    {
        config(['auth.demo_password' => 'super-secret-test-password']);

        $seeder = new DemoAccountSeeder();
        $seeder->run();

        $admin = User::where('email', 'admin@kampuslms.test')->first();
        $this->assertNotNull($admin);
        $this->assertTrue(Hash::check('super-secret-test-password', $admin->password));
    }
}

