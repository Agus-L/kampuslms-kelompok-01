<?php

namespace App\Livewire\Courses;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class EnrollStudentList extends Component
{
    use WithPagination;

    public Course $course;
    public string $search = '';
    public string $message = '';
    public string $messageType = 'success';

    public function mount(Course $course): void
    {
        $this->course = $course;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
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
        $this->resetPage();
    }

    public function render()
    {
        Gate::authorize('manageEnrollment', $this->course);

        $enrolledIds = $this->course->students()->pluck('users.id')->toArray();

        $query = User::where('role', 'mahasiswa')
            ->whereNotIn('id', $enrolledIds)
            ->orderBy('name');

        $totalAvailableCount = (clone $query)->count();

        if (trim($this->search) !== '') {
            $term = trim($this->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $availableStudents = $query->paginate(15);

        return view('livewire.courses.enroll-student-list', [
            'availableStudents'   => $availableStudents,
            'totalAvailableCount' => $totalAvailableCount,
            'totalEnrolledCount'  => count($enrolledIds),
        ]);
    }
}
