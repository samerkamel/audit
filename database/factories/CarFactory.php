<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Car>
 */
class CarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'car_number' => 'CAR-' . date('Y') . '-' . str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'subject' => fake()->sentence(),
            'ncr_description' => fake()->paragraph(),
            'source_type' => fake()->randomElement(['internal_audit', 'external_audit', 'customer_complaint', 'process_performance', 'other']),
            'source_id' => null,
            'audit_finding_id' => null,
            'customer_complaint_id' => null,
            'from_department_id' => Department::factory(),
            'to_department_id' => Department::factory(),
            'issued_date' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'status' => fake()->randomElement(['draft', 'pending_approval', 'issued', 'in_progress', 'pending_review', 'closed', 'late']),
            'issued_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the CAR is issued (active).
     */
    public function issued(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'issued',
        ]);
    }

    /**
     * Indicate that the CAR is closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
        ]);
    }

    /**
     * Indicate that the CAR is high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }
}
