<?php

namespace Database\Factories;

use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Grade>
 */
class GradeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'submission_id' => Submission::factory(),
            'graded_by' => User::factory()->dosen(),
            'score' => fake()->randomFloat(2, 40, 100),
            'feedback' => fake('id_ID')->optional(0.7)->sentence(),
            'graded_at' => now(),
        ];
    }
}
