<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CourseValidationTest extends TestCase
{
    use DatabaseTransactions;

    private User $lecturer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lecturer = User::create([
            'name'     => 'Dosen Pengampu Test',
            'email'    => 'dosen_' . uniqid() . '@kampuslms.test',
            'password' => 'password123',
            'nim_nip'  => 'DSN' . rand(1000, 9999),
        ]);
        $this->lecturer->role = 'dosen';
        $this->lecturer->save();
    }

    public function test_store_course_validation_passes_with_valid_data(): void
    {
        $code = 'MK' . rand(100, 999);

        $response = $this->post(route('courses.store'), [
            'code'        => $code,
            'name'        => 'Pemrograman Berorientasi Objek',
            'description' => null, // nullable test
            'sks'         => 3,
            'lecturer_id' => $this->lecturer->id,
            'status'      => 'active',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('courses', [
            'code' => $code,
            'name' => 'Pemrograman Berorientasi Objek',
        ]);
    }

    public function test_store_course_fails_if_code_duplicate(): void
    {
        $existing = Course::create([
            'code'        => 'DUP' . rand(100, 999),
            'name'        => 'Mata Kuliah Lama',
            'sks'         => 3,
            'lecturer_id' => $this->lecturer->id,
            'status'      => 'active',
        ]);

        $response = $this->post(route('courses.store'), [
            'code'        => $existing->code,
            'name'        => 'Mata Kuliah Baru',
            'sks'         => 2,
            'lecturer_id' => $this->lecturer->id,
            'status'      => 'draft',
        ]);

        $response->assertSessionHasErrors([
            'code' => 'Kode mata kuliah ini sudah terdaftar, silakan gunakan kode lain.',
        ]);
    }

    public function test_store_course_fails_if_sks_exceeds_maximum(): void
    {
        $response = $this->post(route('courses.store'), [
            'code'        => 'SKS7' . rand(10, 99),
            'name'        => 'MK Bobot Berlebih',
            'sks'         => 7, // Max is 6
            'lecturer_id' => $this->lecturer->id,
            'status'      => 'active',
        ]);

        $response->assertSessionHasErrors([
            'sks' => 'Bobot SKS maksimal 6 SKS.',
        ]);
    }

    public function test_update_course_can_keep_same_code(): void
    {
        $course = Course::create([
            'code'        => 'KEEP' . rand(100, 999),
            'name'        => 'Nama Awal',
            'sks'         => 3,
            'lecturer_id' => $this->lecturer->id,
            'status'      => 'draft',
        ]);

        // Mengirimkan kode yang sama saat update (jebakan unique terlewati)
        $response = $this->put(route('courses.update', $course), [
            'code'        => $course->code,
            'name'        => 'Nama Diperbarui',
            'sks'         => 4,
            'lecturer_id' => $this->lecturer->id,
            'status'      => 'active',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('courses', [
            'id'   => $course->id,
            'name' => 'Nama Diperbarui',
            'sks'  => 4,
        ]);
    }

    public function test_update_course_fails_if_code_belongs_to_another_course(): void
    {
        $courseA = Course::create([
            'code'        => 'MKA' . rand(100, 999),
            'name'        => 'Course A',
            'sks'         => 3,
            'lecturer_id' => $this->lecturer->id,
            'status'      => 'active',
        ]);

        $courseB = Course::create([
            'code'        => 'MKB' . rand(100, 999),
            'name'        => 'Course B',
            'sks'         => 3,
            'lecturer_id' => $this->lecturer->id,
            'status'      => 'active',
        ]);

        // Course B mencoba memakai kode milik Course A
        $response = $this->put(route('courses.update', $courseB), [
            'code'        => $courseA->code,
            'name'        => 'Course B Ubah Kode',
            'sks'         => 3,
            'lecturer_id' => $this->lecturer->id,
            'status'      => 'active',
        ]);

        $response->assertSessionHasErrors([
            'code' => 'Kode mata kuliah ini sudah digunakan oleh mata kuliah lain.',
        ]);
    }
}
