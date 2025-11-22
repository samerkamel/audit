<?php

namespace Database\Factories;

use App\Models\User;
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
        $effectiveDate = fake()->dateTimeBetween('-1 year', 'now');
        $nextReviewDate = fake()->dateTimeBetween('now', '+1 year');

        return [
            'document_number' => 'QMS-' . strtoupper(fake()->randomLetter()) . fake()->randomLetter() . '-' . str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'title' => fake()->sentence(),
            'category' => fake()->randomElement(['quality_manual', 'procedure', 'work_instruction', 'form', 'record', 'external_document']),
            'description' => fake()->paragraph(),
            'version' => fake()->randomElement(['1.0', '2.0', '2.1', '3.0']),
            'revision_number' => fake()->numberBetween(0, 5),
            'effective_date' => $effectiveDate->format('Y-m-d'),
            'next_review_date' => $nextReviewDate->format('Y-m-d'),
            'status' => fake()->randomElement(['draft', 'pending_review', 'pending_approval', 'approved', 'effective', 'obsolete', 'archived']),
            'owner_id' => User::factory(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Document is due for review soon (within 30 days).
     */
    public function dueForReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'effective',
            'next_review_date' => now()->addDays(15)->format('Y-m-d'),
        ]);
    }

    /**
     * Document is overdue for review.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'effective',
            'next_review_date' => now()->subDays(10)->format('Y-m-d'),
        ]);
    }

    /**
     * Document is in draft status.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Document is effective.
     */
    public function effective(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'effective',
        ]);
    }
}
