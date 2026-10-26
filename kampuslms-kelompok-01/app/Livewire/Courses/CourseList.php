<?php

namespace App\Livewire\Courses;

use App\Models\Course;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class CourseList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    public int $perPage = 9;

    /**
     * Reset pagination when search query changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Reset pagination when status filter changes.
     */
    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Clear all active filters.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'status']);
        $this->resetPage();
    }

    public function render()
    {
        $query = Course::query()
            ->with('lecturer')
            ->withCount(['materials', 'assignments'])
            ->when($this->search !== '', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('code', 'like', '%' . $this->search . '%')
                        ->orWhereHas('lecturer', function ($lq) {
                            $lq->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->status !== '', function ($q) {
                $q->where('status', $this->status);
            })
            ->latest();

        $courses = $query->paginate($this->perPage);

        $stats = [
            'total'    => Course::count(),
            'active'   => Course::where('status', 'active')->count(),
            'draft'    => Course::where('status', 'draft')->count(),
            'archived' => Course::where('status', 'archived')->count(),
        ];

        return view('livewire.courses.course-list', [
            'courses' => $courses,
            'stats'   => $stats,
        ]);
    }
}
