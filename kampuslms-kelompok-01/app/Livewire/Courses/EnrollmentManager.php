<?php

namespace App\Livewire\Courses;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class EnrollmentManager extends Component
{
    public Course $course;
    public bool $showAll = false;
    public string $searchEnrolled = '';
    public string $message = '';
    public string $messageType = 'success';

    public function mount(Course $course): void
    {
        $this->course = $course;
    }

    public function toggleShowAll(): void
    {
        $this->showAll = !$this->showAll;
    }

    public function unenrollStudent(int $userId): void
    {
        Gate::authorize('manageEnrollment', $this->course);

        $student = User::find($userId);
        $this->course->students()->detach($userId);

        $studentName = $student->name ?? 'Mahasiswa';
        $this->message = "{$studentName} telah berhasil dikeluarkan dari kelas.";
        $this->messageType = 'success';
    }

    public function enrollStudent(int $userId): void
    {
        Gate::authorize('manageEnrollment', $this->course);

        $student = User::where('id', $userId)->where('role', 'mahasiswa')->first();
        if (!$student) {
            $this->message = 'Data mahasiswa tidak ditemukan.';
            $this->messageType = 'error';
            return;
        }

        if ($this->course->students()->where('user_id', $student->id)->exists()) {
            $this->message = "{$student->name} sudah terdaftar pada kelas ini.";
            $this->messageType = 'error';
            return;
        }

        $this->course->students()->attach($student->id, [
            'enrolled_at' => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $this->message = "Berhasil mendaftarkan {$student->name} ke mata kuliah ini.";
        $this->messageType = 'success';
    }

    public function render()
    {
        Gate::authorize('manageEnrollment', $this->course);

        $enrolledStudentsQuery = $this->course->students()
            ->withPivot('id', 'enrolled_at', 'created_at')
            ->orderBy('name');

        if (trim($this->searchEnrolled) !== '') {
            $term = trim($this->searchEnrolled);
            $enrolledStudentsQuery->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $allEnrolled = $enrolledStudentsQuery->get();
        $totalCount = $this->course->students()->count();

        // Default tampilkan 10 mahasiswa; jika $showAll true, tampilkan seluruhnya
        $displayedStudents = (!$this->showAll && trim($this->searchEnrolled) === '')
            ? $allEnrolled->take(10)
            : $allEnrolled;

        $rolePrefix = (auth()->check() && auth()->user()->role === 'dosen') ? 'dosen' : 'admin';
        $enrollRoute = $rolePrefix === 'dosen'
            ? route('dosen.courses.enrollments.index', $this->course)
            : route('admin.courses.enrollments.index', $this->course);

        return view('livewire.courses.enrollment-manager', [
            'enrolledStudents' => $displayedStudents,
            'totalCount'       => $totalCount,
            'hasMore'          => $totalCount > 10,
            'remainingCount'   => max(0, $totalCount - 10),
            'enrollRoute'      => $enrollRoute,
        ]);
    }
}
