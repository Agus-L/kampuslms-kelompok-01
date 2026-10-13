<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        if (! User::where('role', 'admin')->exists()) {
            User::factory()->create(['role' => 'admin', 'password' => 'password']);
        }
        if (! User::where('role', 'mahasiswa')->exists()) {
            User::factory()->create(['role' => 'mahasiswa', 'password' => 'password']);
        }
        if (! User::where('role', 'dosen')->exists()) {
            User::factory()->create(['role' => 'dosen', 'password' => 'password']);
        }
    }

    public function test_guest_is_redirected_to_login_when_accessing_admin_courses(): void
    {
        $response = $this->get('/admin/courses');

        $response->assertRedirect('/login');
    }

    public function test_login_authenticates_admin_successfully(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->post('/login', [
            'login'    => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertEquals('admin', auth()->user()->role);
    }

    public function test_admin_can_access_admin_courses(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/courses');

        $response->assertStatus(200);
    }

    public function test_mahasiswa_gets_403_when_accessing_admin_courses(): void
    {
        $mahasiswa = User::where('role', 'mahasiswa')->first();

        $response = $this->actingAs($mahasiswa)->get('/admin/courses');

        $response->assertStatus(403);
    }

    public function test_dosen_gets_403_when_accessing_admin_courses(): void
    {
        $dosen = User::where('role', 'dosen')->first();

        $response = $this->actingAs($dosen)->get('/admin/courses');

        $response->assertStatus(403);
    }
}
