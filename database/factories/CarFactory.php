<?php

namespace Database\Factories;

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
        $issuedDate = fake()->dateTimeBetween('-3 months', 'now');
        $dueDate = fake()->dateTimeBetween($issuedDate, '+2 months');

        return [
            'car_number' => 'CAR-' . date('Y') . '-' . str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'subject' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'source' => fake()->randomElement(['internal_audit', 'external_audit', 'customer_complaint', 'management_review', 'process_monitoring', 'other']),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'status' => fake()->randomElement(['open', 'in_progress', 'pending_verification', 'closed', 'cancelled']),
            'issued_date' => $issuedDate->format('Y-m-d'),
            'due_date' => $dueDate->format('Y-m-d'),
            'assigned_to' => \App\Models\User::factory(),
            'department_id' => \App\Models\Department::factory(),
            'sector_id' => \App\Models\Sector::factory(),
        ];
    }
}
