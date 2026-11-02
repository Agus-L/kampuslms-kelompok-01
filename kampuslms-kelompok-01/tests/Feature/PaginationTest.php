<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class PaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dosen_submissions_index_is_paginated(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $course = Course::factory()->create(['lecturer_id' => $dosen->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'status'    => 'published',
        ]);

        // Daftarkan 20 mahasiswa ke kelas
        $students = User::factory()->count(20)->create(['role' => 'mahasiswa']);
        $course->students()->attach($students->pluck('id'), [
            'enrolled_at' => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Buat submission untuk 5 mahasiswa pertama
        foreach ($students->take(5) as $student) {
            Submission::create([
                'assignment_id' => $assignment->id,
                'user_id'       => $student->id,
                'file_path'     => 'private/materials/dummy.pdf',
                'original_name' => 'dummy.pdf',
                'file_size'     => 1024,
                'mime_type'     => 'application/pdf',
                'submitted_at'  => now(),
                'is_late'       => false,
            ]);
        }

        $response = $this->actingAs($dosen)->get(route('dosen.assignments.submissions.index', $assignment));

        $response->assertOk();
        $response->assertViewHas('studentList');

        $studentList = $response->viewData('studentList');
        $this->assertInstanceOf(LengthAwarePaginator::class, $studentList);
        $this->assertEquals(20, $studentList->total());
        $this->assertEquals(15, $studentList->perPage());
        $this->assertCount(15, $studentList->items());

        // Verifikasi statistik dihitung dari database (total kelas, bukan hanya per page)
        $stats = $response->viewData('stats');
        $this->assertEquals(20, $stats->total);
        $this->assertEquals(5, $stats->submitted);
        $this->assertEquals(15, $stats->unsubmitted);
        $this->assertEquals(0, $stats->late);
    }

    public function test_dosen_submissions_filter_works_with_pagination(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $course = Course::factory()->create(['lecturer_id' => $dosen->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'status'    => 'published',
        ]);

        $students = User::factory()->count(10)->create(['role' => 'mahasiswa']);
        $course->students()->attach($students->pluck('id'), [
            'enrolled_at' => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // 3 mahasiswa mengumpulkan
        foreach ($students->take(3) as $student) {
            Submission::create([
                'assignment_id' => $assignment->id,
                'user_id'       => $student->id,
                'file_path'     => 'private/materials/dummy.pdf',
                'original_name' => 'dummy.pdf',
                'file_size'     => 1024,
                'mime_type'     => 'application/pdf',
                'submitted_at'  => now(),
                'is_late'       => false,
            ]);
        }

        // Filter: submitted
        $response = $this->actingAs($dosen)->get(route('dosen.assignments.submissions.index', [
            $assignment,
            'status' => 'submitted',
        ]));

        $response->assertOk();
        $studentList = $response->viewData('studentList');
        $this->assertEquals(3, $studentList->total());

        // Filter: unsubmitted
        $responseUnsub = $this->actingAs($dosen)->get(route('dosen.assignments.submissions.index', [
            $assignment,
            'status' => 'unsubmitted',
        ]));

        $responseUnsub->assertOk();
        $studentListUnsub = $responseUnsub->viewData('studentList');
        $this->assertEquals(7, $studentListUnsub->total());
    }

    public function test_mahasiswa_submissions_index_is_paginated(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $course = Course::factory()->create(['lecturer_id' => $dosen->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'status'    => 'published',
        ]);

        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);
        $course->students()->attach($mahasiswa->id, [
            'enrolled_at' => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $response = $this->actingAs($mahasiswa)->get(route('mahasiswa.assignments.submissions.index', $assignment));

        $response->assertOk();
        $response->assertViewHas('submissions');

        $submissions = $response->viewData('submissions');
        $this->assertInstanceOf(LengthAwarePaginator::class, $submissions);
    }
}

