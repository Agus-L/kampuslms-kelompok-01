<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Halaman dashboard utama — merender tampilan berbeda sesuai peran pengguna.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        return match ($user->role) {
            'admin'     => $this->adminDashboard($user),
            'dosen'     => $this->dosenDashboard($user),
            'mahasiswa' => $this->mahasiswaDashboard($user),
            default     => abort(403, 'Peran pengguna tidak dikenali.'),
        };
    }

    /**
     * Dashboard untuk Peran Admin: Statistik menyeluruh sistem & tata kelola pengguna/MK.
     * Menggunakan cache dengan durasi 30 menit dan invalidasi otomatis saat data berubah (Build #6).
     */
    protected function adminDashboard(User $user)
    {
        $stats = Cache::remember('admin.dashboard.stats', now()->addMinutes(30), function () {
            return [
                'total_users'       => User::count(),
                'total_admin'       => User::where('role', 'admin')->count(),
                'total_dosen'       => User::where('role', 'dosen')->count(),
                'total_mahasiswa'   => User::where('role', 'mahasiswa')->count(),
                'total_courses'     => Course::count(),
                'active_courses'    => Course::where('status', 'active')->count(),
                'total_materials'   => Material::count(),
                'total_assignments' => Assignment::count(),
                'total_submissions' => Submission::count(),
            ];
        });

        $latestUsers = User::latest()->take(5)->get();
        $latestCourses = Course::with('lecturer')->latest()->take(5)->get();

        return view('dashboard.admin', compact('user', 'stats', 'latestUsers', 'latestCourses'));
    }

    /**
     * Dashboard untuk Peran Dosen: Mata kuliah yang diampu & tugas yang butuh penilaian.
     */
    protected function dosenDashboard(User $user)
    {
        $taughtCourses = $user->taughtCourses()
            ->withCount(['students', 'materials', 'assignments'])
            ->get();

        $courseIds = $taughtCourses->pluck('id');

        $assignments = Assignment::whereIn('course_id', $courseIds)
            ->with('course')
            ->latest('due_at')
            ->take(8)
            ->get();

        // Submission yang belum dinilai (Action Required)
        $pendingSubmissions = Submission::whereIn('assignment_id', $assignments->pluck('id'))
            ->whereDoesntHave('grade')
            ->with(['assignment.course', 'student'])
            ->latest('submitted_at')
            ->take(8)
            ->get();

        $stats = [
            'total_courses'        => $taughtCourses->count(),
            'total_students'       => $taughtCourses->sum('students_count'),
            'total_assignments'    => $assignments->count(),
            'pending_grades_count' => $pendingSubmissions->count(),
        ];

        return view('dashboard.dosen', compact('user', 'taughtCourses', 'assignments', 'pendingSubmissions', 'stats'));
    }

    /**
     * Dashboard untuk Peran Mahasiswa: Mata kuliah yang diikuti, tugas mendatang & status nilai.
     */
    protected function mahasiswaDashboard(User $user)
    {
        $enrolledCourses = $user->courses()
            ->with('lecturer')
            ->withCount(['materials', 'assignments'])
            ->get();

        $courseIds = $enrolledCourses->pluck('id');

        $upcomingAssignments = Assignment::whereIn('course_id', $courseIds)
            ->where('status', 'published')
            ->with('course')
            ->orderBy('due_at', 'desc')
            ->take(20)
            ->get();

        // Kumpulan submission yang sudah dikumpulkan mahasiswa
        $mySubmissions = Submission::where('user_id', $user->id)
            ->whereIn('assignment_id', $upcomingAssignments->pluck('id'))
            ->with('grade')
            ->get()
            ->keyBy('assignment_id');

        // Nilai terbaru yang sudah didapat mahasiswa
        $recentGrades = Grade::whereHas('submission', fn ($q) => $q->where('user_id', $user->id))
            ->with(['submission.assignment.course', 'grader'])
            ->latest('graded_at')
            ->take(5)
            ->get();

        $stats = [
            'total_enrolled'    => $enrolledCourses->count(),
            'total_sks'         => (int) $enrolledCourses->sum('sks'),
            'total_submitted'   => Submission::where('user_id', $user->id)->count(),
            'total_graded'      => Grade::whereHas('submission', fn ($q) => $q->where('user_id', $user->id))->count(),
        ];

        return view('dashboard.mahasiswa', compact('user', 'enrolledCourses', 'upcomingAssignments', 'mySubmissions', 'recentGrades', 'stats'));
    }
}
