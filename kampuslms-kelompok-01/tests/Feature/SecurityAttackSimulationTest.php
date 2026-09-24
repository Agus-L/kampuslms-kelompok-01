<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SecurityAttackSimulationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Skenario 1: Penyerang mencoba menyelipkan lecturer_id milik mahasiswa pada pembuatan mata kuliah.
     * Sistem HARUS menolak karena mahasiswa bukan dosen.
     */
    public function test_cannot_assign_non_lecturer_user_as_lecturer_in_course_creation(): void
    {
        $student = User::create([
            'name'     => 'Mahasiswa Biasa',
            'email'    => 'student_test_' . uniqid() . '@kampuslms.test',
            'password' => 'password123',
            'nim_nip'  => 'MHS' . rand(1000, 9999),
        ]);
        $student->role = 'mahasiswa';
        $student->save();

        $response = $this->post(route('courses.store'), [
            'code'        => 'SEC' . rand(100, 999),
            'name'        => 'Keamanan Siber Terapan',
            'description' => 'Mata kuliah uji keamanan sistem.',
            'sks'         => 3,
            'lecturer_id' => $student->id, // Penyerang mencoba menyelipkan akun mahasiswa
            'status'      => 'active',
        ]);

        $response->assertSessionHasErrors(['lecturer_id']);
        $this->assertDatabaseMissing('courses', [
            'name' => 'Keamanan Siber Terapan',
        ]);
    }

    /**
     * Skenario 2: Menggunakan dosen yang valid harus berhasil.
     */
    public function test_can_assign_valid_lecturer_as_lecturer_in_course_creation(): void
    {
        $lecturer = User::create([
            'name'     => 'Dosen Komputer',
            'email'    => 'lecturer_test_' . uniqid() . '@kampuslms.test',
            'password' => 'password123',
            'nim_nip'  => 'DSN' . rand(1000, 9999),
        ]);
        $lecturer->role = 'dosen';
        $lecturer->save();

        $code = 'SEC' . rand(100, 999);
        $response = $this->post(route('courses.store'), [
            'code'        => $code,
            'name'        => 'Kriptografi Praktis',
            'description' => 'Mata kuliah kriptografi.',
            'sks'         => 3,
            'lecturer_id' => $lecturer->id,
            'status'      => 'active',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('courses', [
            'code'        => $code,
            'lecturer_id' => $lecturer->id,
        ]);
    }

    /**
     * Skenario 3: Penyerang menyelipkan field ilegal (is_admin, email_verified_at, id) pada pembuatan user.
     * Sistem HARUS membuang field tersebut (mass assignment protection).
     */
    public function test_user_store_ignores_injected_privilege_fields(): void
    {
        $email = 'hacker_' . uniqid() . '@test.com';

        $response = $this->post(route('users.store'), [
            'name'                  => 'Hacker Injeksi',
            'email'                 => $email,
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'nim_nip'               => 'HCK' . rand(1000, 9999),
            'role'                  => 'mahasiswa',
            // Field yang diselipkan penyerang untuk mencoba eskalasi / manipulasi status:
            'is_admin'              => 1,
            'email_verified_at'     => '2020-01-01 00:00:00',
            'remember_token'        => 'injected_token',
        ]);

        $response->assertSessionHasNoErrors();

        $createdUser = User::where('email', $email)->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals('mahasiswa', $createdUser->role);
        $this->assertNull($createdUser->email_verified_at);
        $this->assertNull($createdUser->remember_token);
    }

    /**
     * Skenario 4: Penyerang mencoba menurunkan privilege (self-demotion) satu-satunya admin tersisa.
     * Sistem HARUS menolak agar tidak terjadi admin lockout.
     */
    public function test_cannot_demote_the_last_admin(): void
    {
        // Pastikan hanya ada tepat 1 admin aktif dalam testing
        $admins = User::where('role', 'admin')->whereNull('deleted_at')->get();
        if ($admins->count() > 1) {
            // Soft delete admin ekstra untuk pengujian ini
            for ($i = 1; $i < $admins->count(); $i++) {
                $admins[$i]->delete();
            }
        } elseif ($admins->count() === 0) {
            $onlyAdmin = User::create([
                'name'     => 'Only Admin',
                'email'    => 'admin_' . uniqid() . '@test.com',
                'password' => 'password123',
            ]);
            $onlyAdmin->role = 'admin';
            $onlyAdmin->save();
            $admins = collect([$onlyAdmin]);
        }

        $lastAdmin = $admins->first();

        // Coba downgrade role ke mahasiswa
        $response = $this->put(route('users.update', $lastAdmin), [
            'name'                  => $lastAdmin->name,
            'email'                 => $lastAdmin->email,
            'role'                  => 'mahasiswa', // Upaya downgrade
        ]);

        $response->assertSessionHasErrors(['role']);

        $lastAdmin->refresh();
        $this->assertEquals('admin', $lastAdmin->role);
    }

    /**
     * Skenario 5: Penyerang mencoba menghapus satu-satunya admin tersisa.
     * Sistem HARUS menolak penghapusan agar sistem tidak kehilangan administrator.
     */
    public function test_cannot_delete_the_last_admin(): void
    {
        $admins = User::where('role', 'admin')->whereNull('deleted_at')->get();
        if ($admins->count() > 1) {
            for ($i = 1; $i < $admins->count(); $i++) {
                $admins[$i]->delete();
            }
        } elseif ($admins->count() === 0) {
            $onlyAdmin = User::create([
                'name'     => 'Only Admin',
                'email'    => 'admin_' . uniqid() . '@test.com',
                'password' => 'password123',
            ]);
            $onlyAdmin->role = 'admin';
            $onlyAdmin->save();
            $admins = collect([$onlyAdmin]);
        }

        $lastAdmin = $admins->first();

        $response = $this->delete(route('users.destroy', $lastAdmin));
        $response->assertSessionHas('error');

        $this->assertNull($lastAdmin->fresh()->deleted_at);
    }
}
