<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Complaint>
 */
class ComplaintFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $complaintDate = fake()->dateTimeBetween('-3 months', 'now');

        return [
            'complaint_number' => 'COMP-' . date('Y') . '-' . str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'subject' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'customer_name' => fake()->company(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'category' => fake()->randomElement(['product_quality', 'service_quality', 'delivery', 'documentation', 'other']),
            'severity' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'status' => fake()->randomElement(['new', 'investigating', 'action_required', 'resolved', 'closed', 'rejected']),
            'complaint_date' => $complaintDate->format('Y-m-d'),
            'assigned_to' => \App\Models\User::factory(),
            'department_id' => \App\Models\Department::factory(),
            'sector_id' => \App\Models\Sector::factory(),
        ];
    }
}
