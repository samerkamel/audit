<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuditPlan>
 */
class AuditPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'audit_type' => fake()->randomElement(['internal', 'external', 'compliance', 'operational', 'financial', 'it', 'quality']),
            'scope' => fake()->paragraph(),
            'objectives' => fake()->sentence(10),
            'lead_auditor_id' => User::factory(),
            'created_by' => User::factory(),
            'status' => fake()->randomElement(['draft', 'planned', 'in_progress', 'completed', 'cancelled']),
            'is_active' => true,
        ];
    }

    /**
     * Audit plan is planned.
     */
    public function planned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'planned',
        ]);
    }

    /**
     * Audit plan is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'actual_start_date' => now(),
        ]);
    }

    /**
     * Audit plan is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'actual_start_date' => now()->subDays(7),
            'actual_end_date' => now(),
        ]);
    }
}
