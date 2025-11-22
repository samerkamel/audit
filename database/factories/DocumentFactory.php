<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $issueDate = fake()->dateTimeBetween('-2 years', 'now');
        $reviewDate = fake()->dateTimeBetween($issueDate->format('Y-m-d') . ' +1 year', '+1 year');

        return [
            'document_number' => 'QMS-' . strtoupper(fake()->randomLetter()) . fake()->randomLetter() . '-' . str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'title' => fake()->sentence(),
            'category' => fake()->randomElement(['procedure', 'form', 'record', 'policy', 'manual', 'work_instruction']),
            'description' => fake()->paragraph(),
            'revision' => fake()->randomElement(['1.0', '2.0', '2.1', '3.0', '3.1']),
            'issue_date' => $issueDate->format('Y-m-d'),
            'review_date' => $reviewDate->format('Y-m-d'),
            'status' => fake()->randomElement(['draft', 'active', 'under_review', 'obsolete', 'archived']),
            'owner_id' => \App\Models\User::factory(),
            'department_id' => \App\Models\Department::factory(),
            'sector_id' => \App\Models\Sector::factory(),
        ];
    }
}
