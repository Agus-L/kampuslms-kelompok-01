<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Daftar mata kuliah realistis dalam bahasa Indonesia.
     */
    private static array $mataKuliah = [
        ['code' => 'IF101', 'name' => 'Algoritma dan Pemrograman',     'sks' => 3],
        ['code' => 'IF201', 'name' => 'Struktur Data',                 'sks' => 3],
        ['code' => 'IF301', 'name' => 'Basis Data',                    'sks' => 3],
        ['code' => 'IF401', 'name' => 'Pemrograman Web',               'sks' => 3],
        ['code' => 'IF501', 'name' => 'Rekayasa Perangkat Lunak',      'sks' => 4],
        ['code' => 'IF102', 'name' => 'Matematika Diskrit',            'sks' => 3],
        ['code' => 'IF202', 'name' => 'Jaringan Komputer',             'sks' => 3],
        ['code' => 'IF302', 'name' => 'Sistem Operasi',                'sks' => 3],
        ['code' => 'IF402', 'name' => 'Kecerdasan Buatan',             'sks' => 3],
        ['code' => 'IF502', 'name' => 'Keamanan Siber',                'sks' => 3],
    ];

    private static int $mkIndex = 0;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mk = self::$mataKuliah[self::$mkIndex % count(self::$mataKuliah)];
        self::$mkIndex++;

        return [
            'code' => $mk['code'],
            'name' => $mk['name'],
            'description' => 'Mata kuliah ' . $mk['name'] . ' membahas konsep dasar hingga lanjutan dalam bidang terkait.',
            'sks' => $mk['sks'],
            'lecturer_id' => User::factory()->dosen(),
            'status' => 'active',
        ];
    }

    /**
     * State: archived course.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }

    /**
     * State: draft course.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }
}
