<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Material>
 */
class MaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['file', 'link']);

        return [
            'course_id' => Course::factory(),
            'uploaded_by' => fn (array $attributes) => Course::find($attributes['course_id'])?->lecturer_id ?? User::factory()->dosen(),
            'title' => 'Materi: ' . fake('id_ID')->sentence(3),
            'description' => fake('id_ID')->paragraph(),
            'type' => $type,
            'file_path' => $type === 'file' ? 'materials/' . fake()->uuid() . '.pdf' : null,
            'original_name' => $type === 'file' ? fake()->word() . '.pdf' : null,
            'file_size' => $type === 'file' ? fake()->numberBetween(100_000, 10_000_000) : null,
            'mime_type' => $type === 'file' ? 'application/pdf' : null,
            'external_url' => $type === 'link' ? fake()->url() : null,
        ];
    }
}
