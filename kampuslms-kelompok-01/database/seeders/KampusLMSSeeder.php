<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder utama KampusLMS.
 *
 * Menghasilkan data konsisten secara logis:
 * - 1 admin, 3 dosen, 30 mahasiswa
 * - 5 mata kuliah, tiap MK ≥ 15 mahasiswa terdaftar
 * - 3 tugas per MK (campuran: sudah lewat, aktif, draft)
 * - ≥ 100 submission, ~60% sudah dinilai
 * - Submission hanya dari mahasiswa yang terdaftar di MK terkait
 * - Sebagian submission terlambat (submitted_at > due_at)
 *
 * Idempoten: aman dijalankan ulang setelah migrate:fresh.
 */
class KampusLMSSeeder extends Seeder
{
    /**
     * Daftar mata kuliah (5 buah).
     */
    private array $mataKuliah = [
        ['code' => 'IF101', 'name' => 'Algoritma dan Pemrograman',     'sks' => 3],
        ['code' => 'IF201', 'name' => 'Struktur Data',                 'sks' => 3],
        ['code' => 'IF301', 'name' => 'Basis Data',                    'sks' => 3],
        ['code' => 'IF401', 'name' => 'Pemrograman Web',               'sks' => 3],
        ['code' => 'IF501', 'name' => 'Rekayasa Perangkat Lunak',      'sks' => 4],
    ];

    /**
     * Nama-nama Indonesia untuk mahasiswa.
     */
    private array $namaMahasiswa = [
        'Andi Pratama', 'Siti Nurhaliza', 'Bima Sakti', 'Dewi Lestari', 'Fajar Nugroho',
        'Gita Savitri', 'Hendra Wijaya', 'Indah Permata', 'Joko Widodo', 'Kartika Sari',
        'Lukman Hakim', 'Maya Angelica', 'Nanda Pratiwi', 'Oscar Lawalata', 'Putri Handayani',
        'Qori Sandioriva', 'Rizky Ramadhan', 'Sari Mutiara', 'Taufik Hidayat', 'Umi Kalsum',
        'Vina Panduwinata', 'Wahyu Setiawan', 'Xena Xenon', 'Yusuf Mansur', 'Zahra Azzahra',
        'Ahmad Fauzi', 'Bella Saphira', 'Citra Kirana', 'Dimas Anggara', 'Elsa Yunita',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ──────────────────────────────────────────────
        // 1. USERS
        // ──────────────────────────────────────────────

        // Admin (akun demo)
        $admin = User::updateOrCreate(
            ['email' => 'admin@kampuslms.test'],
            [
                'name'              => 'Admin KampusLMS',
                'password'          => Hash::make('password'),
                'role'              => 'admin',
                'nim_nip'           => 'ADM000001',
                'email_verified_at' => now(),
            ]
        );

        // Dosen demo
        $dosenDemo = User::updateOrCreate(
            ['email' => 'dosen@kampuslms.test'],
            [
                'name'              => 'Dr. Budi Santoso',
                'password'          => Hash::make('password'),
                'role'              => 'dosen',
                'nim_nip'           => '1980010001',
                'email_verified_at' => now(),
            ]
        );

        // 2 dosen tambahan
        $dosen2 = User::updateOrCreate(
            ['email' => 'dosen2@kampuslms.test'],
            [
                'name'              => 'Prof. Siti Rahayu',
                'password'          => Hash::make('password'),
                'role'              => 'dosen',
                'nim_nip'           => '1975020002',
                'email_verified_at' => now(),
            ]
        );

        $dosen3 = User::updateOrCreate(
            ['email' => 'dosen3@kampuslms.test'],
            [
                'name'              => 'Dr. Ahmad Dahlan',
                'password'          => Hash::make('password'),
                'role'              => 'dosen',
                'nim_nip'           => '1985030003',
                'email_verified_at' => now(),
            ]
        );

        $semuaDosen = collect([$dosenDemo, $dosen2, $dosen3]);

        // Mahasiswa demo
        $mhsDemo = User::updateOrCreate(
            ['email' => 'mahasiswa@kampuslms.test'],
            [
                'name'              => 'Andi Pratama',
                'password'          => Hash::make('password'),
                'role'              => 'mahasiswa',
                'nim_nip'           => '2401010001',
                'email_verified_at' => now(),
            ]
        );

        // 29 mahasiswa tambahan (total 30)
        $semuaMahasiswa = collect([$mhsDemo]);

        for ($i = 1; $i < count($this->namaMahasiswa); $i++) {
            $mhs = User::updateOrCreate(
                ['email' => 'mahasiswa' . ($i + 1) . '@kampuslms.test'],
                [
                    'name'              => $this->namaMahasiswa[$i],
                    'password'          => Hash::make('password'),
                    'role'              => 'mahasiswa',
                    'nim_nip'           => '240101' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                    'email_verified_at' => now(),
                ]
            );
            $semuaMahasiswa->push($mhs);
        }

        // ──────────────────────────────────────────────
        // 2. COURSES + ENROLLMENT
        // ──────────────────────────────────────────────

        $courses = collect();

        foreach ($this->mataKuliah as $idx => $mk) {
            // Distribusikan dosen: demo dosen mengajar 2 MK, sisanya masing-masing 1-2
            $lecturerId = match (true) {
                $idx < 2 => $dosenDemo->id,
                $idx < 4 => $dosen2->id,
                default  => $dosen3->id,
            };

            $course = Course::updateOrCreate(
                ['code' => $mk['code']],
                [
                    'name'        => $mk['name'],
                    'description' => 'Mata kuliah ' . $mk['name'] . ' membahas konsep dasar hingga lanjutan. '
                                   . 'Mahasiswa diharapkan mampu memahami teori dan mengaplikasikannya dalam proyek nyata.',
                    'sks'         => $mk['sks'],
                    'lecturer_id' => $lecturerId,
                    'status'      => 'active',
                ]
            );

            $courses->push($course);
        }

        // Enrollment: tiap MK ≥ 15 mahasiswa. Mahasiswa demo terdaftar di semua MK.
        $shuffledStudents = $semuaMahasiswa->shuffle();

        foreach ($courses as $idx => $course) {
            // Selalu enroll mahasiswa demo
            $enrolledIds = collect([$mhsDemo->id]);

            // Tambah 14-19 mahasiswa lain (unik per MK)
            $remainingStudents = $shuffledStudents->filter(fn ($s) => $s->id !== $mhsDemo->id);
            $howMany = min(fake()->numberBetween(15, 20), $remainingStudents->count());

            // Gunakan rotasi agar distribusi merata
            $offset = ($idx * 6) % $remainingStudents->count();
            $selected = $remainingStudents->values()->slice($offset, $howMany);

            // Jika kurang dari 14, ambil dari awal
            if ($selected->count() < 14) {
                $remaining = 14 - $selected->count();
                $more = $remainingStudents->values()->take($remaining);
                $selected = $selected->merge($more);
            }

            $enrolledIds = $enrolledIds->merge($selected->pluck('id'))->unique();

            // Sync enrollment (tanpa detach mahasiswa yang sudah terdaftar)
            foreach ($enrolledIds as $studentId) {
                $course->students()->syncWithoutDetaching([
                    $studentId => [
                        'enrolled_at' => now()->subDays(fake()->numberBetween(30, 90)),
                    ],
                ]);
            }
        }

        // ──────────────────────────────────────────────
        // 3. MATERIALS (2-3 per MK)
        // ──────────────────────────────────────────────

        $materialTitles = [
            'Pengenalan dan Silabus', 'Slide Pertemuan 1', 'Referensi Buku Utama',
            'Panduan Praktikum', 'Contoh Soal UTS', 'Materi Lanjutan',
            'Video Tutorial', 'Panduan Tugas Besar', 'Rangkuman Materi',
        ];

        foreach ($courses as $course) {
            $materialsCount = fake()->numberBetween(2, 3);

            for ($m = 0; $m < $materialsCount; $m++) {
                $type = fake()->randomElement(['file', 'link']);
                $title = $materialTitles[array_rand($materialTitles)] . ' - ' . $course->name;

                Material::updateOrCreate(
                    ['course_id' => $course->id, 'title' => $title],
                    [
                        'uploaded_by'   => $course->lecturer_id,
                        'description'   => 'Materi untuk mata kuliah ' . $course->name . '.',
                        'type'          => $type,
                        'file_path'     => $type === 'file' ? 'materials/' . fake()->uuid() . '.pdf' : null,
                        'original_name' => $type === 'file' ? str_replace(' ', '_', $title) . '.pdf' : null,
                        'file_size'     => $type === 'file' ? fake()->numberBetween(100_000, 10_000_000) : null,
                        'mime_type'     => $type === 'file' ? 'application/pdf' : null,
                        'external_url'  => $type === 'link' ? 'https://example.com/' . fake()->slug() : null,
                    ]
                );
            }
        }

        // ──────────────────────────────────────────────
        // 4. ASSIGNMENTS (3 per MK: 1 lewat deadline, 1 aktif, 1 draft)
        // ──────────────────────────────────────────────

        $allAssignments = collect();

        $assignmentTemplates = [
            // [title_prefix, type] — type: past, active, draft
            ['Tugas 1: ', 'past'],
            ['Tugas 2: ', 'active'],
            ['Tugas 3: ', 'draft'],
        ];

        $topikTugas = [
            'Algoritma dan Pemrograman'     => ['Implementasi Sorting', 'Program Kalkulator CLI', 'Rekursi dan Iterasi'],
            'Struktur Data'                 => ['Implementasi Stack & Queue', 'Pohon Biner', 'Graph Traversal'],
            'Basis Data'                    => ['Desain ERD Toko Online', 'Query SQL Lanjutan', 'Normalisasi Database'],
            'Pemrograman Web'               => ['Landing Page HTML/CSS', 'CRUD dengan Laravel', 'REST API Design'],
            'Rekayasa Perangkat Lunak'      => ['Dokumen SRS', 'Use Case Diagram', 'Testing & QA Plan'],
        ];

        foreach ($courses as $course) {
            $topik = $topikTugas[$course->name] ?? ['Analisis Masalah', 'Implementasi Solusi', 'Laporan Akhir'];

            foreach ($assignmentTemplates as $tIdx => [$prefix, $type]) {
                $dueAt = match ($type) {
                    'past'   => now()->subDays(fake()->numberBetween(5, 30)),
                    'active' => now()->addDays(fake()->numberBetween(3, 21)),
                    'draft'  => now()->addDays(fake()->numberBetween(21, 60)),
                };

                $assignment = Assignment::updateOrCreate(
                    ['course_id' => $course->id, 'title' => $prefix . $topik[$tIdx]],
                    [
                        'created_by'   => $course->lecturer_id,
                        'instructions' => 'Kerjakan tugas berikut dengan ketentuan:\n\n'
                                        . '1. ' . fake('id_ID')->sentence() . '\n'
                                        . '2. ' . fake('id_ID')->sentence() . '\n'
                                        . '3. Kumpulkan dalam format PDF sebelum deadline.\n\n'
                                        . 'Selamat mengerjakan!',
                        'due_at'       => $dueAt,
                        'max_score'    => 100,
                        'allow_late'   => $type !== 'draft',
                        'status'       => $type === 'draft' ? 'draft' : 'published',
                    ]
                );

                $allAssignments->push([
                    'assignment' => $assignment,
                    'course'     => $course,
                    'type'       => $type,
                ]);
            }
        }

        // ──────────────────────────────────────────────
        // 5. SUBMISSIONS (≥ 100, hanya dari mahasiswa terdaftar)
        // ──────────────────────────────────────────────

        $totalSubmissions = 0;
        $allSubmissions = collect();

        // Hanya buat submission untuk tugas yang sudah published (past & active)
        $publishedAssignments = $allAssignments->filter(fn ($a) => $a['type'] !== 'draft');

        foreach ($publishedAssignments as $aData) {
            $assignment = $aData['assignment'];
            $course = $aData['course'];
            $type = $aData['type'];

            // Ambil mahasiswa yang terdaftar di MK ini
            $enrolledStudentIds = $course->students()->pluck('users.id');

            // Tentukan berapa mahasiswa yang submit
            // Past: 80-100% submit, Active: 40-70% submit
            $submitPercentage = match ($type) {
                'past'   => fake()->numberBetween(80, 100) / 100,
                'active' => fake()->numberBetween(40, 70) / 100,
                default  => 0,
            };

            $howManySubmit = (int) ceil($enrolledStudentIds->count() * $submitPercentage);
            $submittingStudents = $enrolledStudentIds->shuffle()->take($howManySubmit);

            // Pastikan mahasiswa demo selalu submit di tugas past
            if ($type === 'past' && !$submittingStudents->contains($mhsDemo->id)
                && $enrolledStudentIds->contains($mhsDemo->id)) {
                $submittingStudents->push($mhsDemo->id);
            }

            foreach ($submittingStudents as $studentId) {
                // Cek apakah sudah ada submission (idempoten)
                $existing = Submission::where('assignment_id', $assignment->id)
                    ->where('user_id', $studentId)
                    ->first();

                if ($existing) {
                    $allSubmissions->push($existing);
                    $totalSubmissions++;
                    continue;
                }

                // Tentukan waktu submit
                $dueAt = Carbon::parse($assignment->due_at);

                if ($type === 'past') {
                    // ~20% terlambat
                    $isLate = fake()->boolean(20);
                    if ($isLate) {
                        $submittedAt = $dueAt->copy()->addHours(fake()->numberBetween(1, 72));
                    } else {
                        $submittedAt = $dueAt->copy()->subHours(fake()->numberBetween(1, 168)); // 1-7 hari sebelum
                    }
                } else {
                    // Active: semua tepat waktu (belum deadline)
                    $submittedAt = now()->subHours(fake()->numberBetween(1, 48));
                    $isLate = false;
                }

                $submission = Submission::create([
                    'assignment_id' => $assignment->id,
                    'user_id'       => $studentId,
                    'file_path'     => 'submissions/' . fake()->uuid() . '.pdf',
                    'original_name' => 'Tugas_' . str_replace(' ', '_', $assignment->title) . '_' . $studentId . '.pdf',
                    'file_size'     => fake()->numberBetween(50_000, 5_000_000),
                    'note'          => fake('id_ID')->optional(0.5)->sentence(),
                    'submitted_at'  => $submittedAt,
                    'is_late'       => $isLate,
                ]);

                $allSubmissions->push($submission);
                $totalSubmissions++;
            }
        }

        // Jika kurang dari 100, tambahkan submission dari tugas active
        if ($totalSubmissions < 100) {
            $activeAssignments = $allAssignments->filter(fn ($a) => $a['type'] === 'active');

            foreach ($activeAssignments as $aData) {
                if ($totalSubmissions >= 110) break;

                $assignment = $aData['assignment'];
                $course = $aData['course'];
                $enrolledStudentIds = $course->students()->pluck('users.id');

                $existingSubmitters = Submission::where('assignment_id', $assignment->id)
                    ->pluck('user_id');
                $remainingStudents = $enrolledStudentIds->diff($existingSubmitters);

                foreach ($remainingStudents as $studentId) {
                    if ($totalSubmissions >= 110) break;

                    $submission = Submission::create([
                        'assignment_id' => $assignment->id,
                        'user_id'       => $studentId,
                        'file_path'     => 'submissions/' . fake()->uuid() . '.pdf',
                        'original_name' => 'Tugas_Extra_' . $studentId . '.pdf',
                        'file_size'     => fake()->numberBetween(50_000, 5_000_000),
                        'note'          => fake('id_ID')->optional(0.3)->sentence(),
                        'submitted_at'  => now()->subHours(fake()->numberBetween(1, 24)),
                        'is_late'       => false,
                    ]);

                    $allSubmissions->push($submission);
                    $totalSubmissions++;
                }
            }
        }

        // ──────────────────────────────────────────────
        // 6. GRADES (~60% submission yang sudah dinilai)
        // ──────────────────────────────────────────────

        // Prioritaskan grading untuk tugas yang sudah lewat deadline
        $submissionsToGrade = $allSubmissions->shuffle();
        $targetGraded = (int) ceil($allSubmissions->count() * 0.60);
        $gradedCount = 0;

        foreach ($submissionsToGrade as $submission) {
            if ($gradedCount >= $targetGraded) break;

            // Cek apakah sudah ada grade (idempoten)
            $existingGrade = Grade::where('submission_id', $submission->id)->first();
            if ($existingGrade) {
                $gradedCount++;
                continue;
            }

            // Ambil dosen pengampu MK dari assignment
            $assignment = Assignment::find($submission->assignment_id);
            if (!$assignment) continue;

            $course = Course::find($assignment->course_id);
            if (!$course) continue;

            $gradedAt = Carbon::parse($submission->submitted_at)
                ->addDays(fake()->numberBetween(1, 14));

            Grade::create([
                'submission_id' => $submission->id,
                'graded_by'     => $course->lecturer_id,
                'score'         => fake()->randomFloat(2, 30, 100),
                'feedback'      => fake('id_ID')->optional(0.7)->randomElement([
                    'Bagus sekali! Pertahankan kualitas kerja Anda.',
                    'Cukup baik, namun perlu perbaikan di bagian analisis.',
                    'Perlu lebih teliti dalam mengerjakan soal.',
                    'Jawaban sudah tepat, tapi penjelasan bisa diperjelas.',
                    'Tugas terlambat dikumpulkan. Nilai dikurangi.',
                    'Sangat memuaskan. Konsep dipahami dengan baik.',
                    'Masih ada beberapa kesalahan logika yang perlu diperbaiki.',
                    'Pekerjaan rapi dan terstruktur. Nilai bonus diberikan.',
                    'Harap perhatikan format penulisan yang diminta.',
                    'Pemahaman konsep dasar sudah baik, terus tingkatkan.',
                ]),
                'graded_at'     => $gradedAt,
            ]);

            $gradedCount++;
        }

        // ──────────────────────────────────────────────
        // Summary
        // ──────────────────────────────────────────────

        $this->command->info('=== KampusLMS Seeder Summary ===');
        $this->command->info('Admin      : 1');
        $this->command->info('Dosen      : ' . $semuaDosen->count());
        $this->command->info('Mahasiswa  : ' . $semuaMahasiswa->count());
        $this->command->info('Mata Kuliah: ' . $courses->count());
        $this->command->info('Assignments: ' . $allAssignments->count());
        $this->command->info('Submissions: ' . $totalSubmissions);
        $this->command->info('Grades     : ' . $gradedCount);
        $this->command->info('================================');
    }
}
