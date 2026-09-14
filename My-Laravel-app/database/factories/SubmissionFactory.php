<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Submission>
 */
class SubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isLate = fake()->boolean(20); // ~20% terlambat

        return [
            'assignment_id' => Assignment::factory(),
            'user_id' => User::factory()->mahasiswa(),
            'file_path' => 'submissions/' . fake()->uuid() . '.pdf',
            'original_name' => 'Tugas_' . fake()->word() . '.pdf',
            'file_size' => fake()->numberBetween(50_000, 5_000_000),
            'note' => fake()->optional(0.6)->sentence(),
            'submitted_at' => now(), // akan di-override di seeder
            'is_late' => $isLate,
        ];
    }

    /**
     * State: terlambat submit.
     */
    public function late(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_late' => true,
        ]);
    }

    /**
     * State: tepat waktu.
     */
    public function onTime(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_late' => false,
        ]);
    }
}
