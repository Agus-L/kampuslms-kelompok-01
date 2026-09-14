<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assignment>
 */
class AssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'created_by' => fn (array $attributes) => Course::find($attributes['course_id'])?->lecturer_id ?? User::factory()->dosen(),
            'title' => 'Tugas ' . fake()->numberBetween(1, 10) . ': ' . fake('id_ID')->sentence(4),
            'instructions' => fake('id_ID')->paragraphs(2, true),
            'due_at' => now()->addDays(fake()->numberBetween(1, 30)),
            'max_score' => 100,
            'allow_late' => true,
            'status' => 'published',
        ];
    }

    /**
     * State: sudah lewat deadline.
     */
    public function pastDue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_at' => now()->subDays(fake()->numberBetween(3, 30)),
            'status' => 'published',
        ]);
    }

    /**
     * State: sedang aktif (deadline masih di masa depan).
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_at' => now()->addDays(fake()->numberBetween(3, 21)),
            'status' => 'published',
        ]);
    }

    /**
     * State: masih draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_at' => now()->addDays(fake()->numberBetween(14, 45)),
            'status' => 'draft',
        ]);
    }
}
