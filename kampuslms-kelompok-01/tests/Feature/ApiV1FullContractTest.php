<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiV1FullContractTest extends TestCase
{
    use DatabaseTransactions;

    private User $dosen1;
    private User $dosen2;
    private User $mahasiswa1;
    private User $mahasiswa2;
    private Course $course1;
    private Assignment $assignment1;

    protected function setUp(): void
    {
        parent::setUp();

        // Dosen 1 (Pengampu Course 1)
        $this->dosen1 = User::create([
            'name'     => 'Dosen Satu',
            'email'    => 'dosen1_' . uniqid() . '@test.com',
            'password' => Hash::make('password123'),
            'nim_nip'  => 'DSN' . rand(1000, 9999),
        ]);
        $this->dosen1->role = 'dosen';
        $this->dosen1->save();

        // Dosen 2 (Pengampu Lain)
        $this->dosen2 = User::create([
            'name'     => 'Dosen Dua',
            'email'    => 'dosen2_' . uniqid() . '@test.com',
            'password' => Hash::make('password123'),
            'nim_nip'  => 'DSN' . rand(1000, 9999),
        ]);
        $this->dosen2->role = 'dosen';
        $this->dosen2->save();

        // Mahasiswa 1 (Terdaftar di Course 1)
        $this->mahasiswa1 = User::create([
            'name'     => 'Mahasiswa Terdaftar',
            'email'    => 'mhs1_' . uniqid() . '@test.com',
            'password' => Hash::make('password123'),
            'nim_nip'  => 'MHS' . rand(1000, 9999),
        ]);
        $this->mahasiswa1->role = 'mahasiswa';
        $this->mahasiswa1->save();

        // Mahasiswa 2 (Tidak terdaftar)
        $this->mahasiswa2 = User::create([
            'name'     => 'Mahasiswa Luar',
            'email'    => 'mhs2_' . uniqid() . '@test.com',
            'password' => Hash::make('password123'),
            'nim_nip'  => 'MHS' . rand(1000, 9999),
        ]);
        $this->mahasiswa2->role = 'mahasiswa';
        $this->mahasiswa2->save();

        // Course 1
        $this->course1 = Course::create([
            'code'        => 'CS' . rand(100, 999),
            'name'        => 'Pemrograman Web Lanjut',
            'description' => 'Mata kuliah web',
            'sks'         => 3,
            'lecturer_id' => $this->dosen1->id,
            'status'      => 'active',
        ]);

        // Enroll Mahasiswa 1 ke Course 1
        $this->course1->students()->attach($this->mahasiswa1->id, [
            'enrolled_at' => now(),
        ]);

        // Assignment 1
        $this->assignment1 = Assignment::forceCreate([
            'course_id'    => $this->course1->id,
            'created_by'   => $this->dosen1->id,
            'title'        => 'Tugas 1: RESTful API',
            'instructions' => 'Buat API sesuai kontrak',
            'due_at'       => now()->addDays(7),
            'max_score'    => 100,
            'allow_late'   => true,
            'status'       => 'published',
        ]);
    }

    public function test_auth_login_and_logout_and_me(): void
    {
        // 1. Login Sukses
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'email'    => $this->dosen1->email,
            'password' => 'password123',
        ]);

        $loginRes->assertStatus(200);
        $loginRes->assertJsonStructure([
            'data' => [
                'token',
                'token_type',
                'user' => ['id', 'name', 'email', 'role', 'nim_nip', 'created_at'],
            ],
        ]);

        $token = $loginRes->json('data.token');

        // 2. GET /me dengan Bearer token
        $meRes = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/v1/me');
        $meRes->assertStatus(200);
        $meRes->assertJson([
            'data' => [
                'id'    => $this->dosen1->id,
                'email' => $this->dosen1->email,
                'role'  => 'dosen',
            ],
        ]);

        // 3. Logout
        $logoutRes = $this->withHeader('Authorization', "Bearer $token")->postJson('/api/v1/auth/logout');
        $logoutRes->assertStatus(200);

        // 4. Setelah logout token tidak valid (401)
        $this->app['auth']->forgetGuards();
        $meAfter = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/v1/me');
        $meAfter->assertStatus(401);
    }

    public function test_courses_index_and_show_contract(): void
    {
        // Dosen 1 melihat courses miliknya
        $resDosen = $this->actingAs($this->dosen1, 'sanctum')->getJson('/api/v1/courses');
        $resDosen->assertStatus(200);
        $resDosen->assertJsonStructure([
            'data' => [
                '*' => ['id', 'code', 'name', 'description', 'sks', 'status', 'lecturer' => ['id', 'name'], 'created_at', 'updated_at'],
            ],
            'meta' => ['current_page', 'last_page', 'total'],
        ]);
        $this->assertArrayNotHasKey('links', $resDosen->json());

        // Mahasiswa 1 melihat courses yang diikutinya
        $resMhs = $this->actingAs($this->mahasiswa1, 'sanctum')->getJson('/api/v1/courses');
        $resMhs->assertStatus(200);
        $this->assertEquals(1, $resMhs->json('meta.total'));

        // Detail Course 1
        $detailRes = $this->actingAs($this->dosen1, 'sanctum')->getJson("/api/v1/courses/{$this->course1->id}");
        $detailRes->assertStatus(200);
        $detailRes->assertJsonStructure([
            'data' => [
                'id', 'code', 'name', 'description', 'sks', 'status',
                'lecturer' => ['id', 'name'],
                'materials_count', 'assignments_count',
                'created_at', 'updated_at',
            ],
        ]);

        // Mahasiswa 2 (tidak enroll) akses detail course 1 -> 403
        $forbiddenRes = $this->actingAs($this->mahasiswa2, 'sanctum')->getJson("/api/v1/courses/{$this->course1->id}");
        $forbiddenRes->assertStatus(403);
    }

    public function test_assignments_crud_and_course_assignments(): void
    {
        // 1. GET /courses/{id}/assignments
        $resAssignments = $this->actingAs($this->mahasiswa1, 'sanctum')->getJson(
            "/api/v1/courses/{$this->course1->id}/assignments?status=published"
        );
        $resAssignments->assertStatus(200);
        $resAssignments->assertJsonStructure([
            'data' => [
                '*' => ['id', 'course_id', 'title', 'instructions', 'due_at', 'max_score', 'allow_late', 'status', 'created_by'],
            ],
            'meta' => ['current_page', 'last_page', 'total'],
        ]);

        // 2. Dosen 1 membuat tugas baru -> 201
        $storeRes = $this->actingAs($this->dosen1, 'sanctum')->postJson('/api/v1/assignments', [
            'course_id'    => $this->course1->id,
            'title'        => 'Tugas 2: Vue JS',
            'instructions' => 'Buat komponen Vue',
            'due_at'       => now()->addDays(10)->format('Y-m-d H:i:s'),
            'max_score'    => 100,
            'allow_late'   => false,
            'status'       => 'published',
        ]);
        $storeRes->assertStatus(201);
        $newAssignmentId = $storeRes->json('data.id');

        // 3. Dosen 2 (bukan pemilik) coba update tugas Dosen 1 -> 403
        $updateForbidden = $this->actingAs($this->dosen2, 'sanctum')->putJson("/api/v1/assignments/{$newAssignmentId}", [
            'title' => 'Coba bajak tugas',
        ]);
        $updateForbidden->assertStatus(403);

        // 4. Dosen 1 update tugas miliknya -> 200
        $updateRes = $this->actingAs($this->dosen1, 'sanctum')->putJson("/api/v1/assignments/{$newAssignmentId}", [
            'title' => 'Tugas 2: Vue JS (Diperbarui)',
        ]);
        $updateRes->assertStatus(200);
        $this->assertEquals('Tugas 2: Vue JS (Diperbarui)', $updateRes->json('data.title'));

        // 5. Dosen 1 hapus tugas -> 204
        $deleteRes = $this->actingAs($this->dosen1, 'sanctum')->deleteJson("/api/v1/assignments/{$newAssignmentId}");
        $deleteRes->assertStatus(204);
    }
}
