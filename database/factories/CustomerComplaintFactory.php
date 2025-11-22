<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomerComplaint>
 */
class CustomerComplaintFactory extends Factory
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
            'complaint_date' => $complaintDate->format('Y-m-d'),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'customer_company' => fake()->company(),
            'complaint_subject' => fake()->sentence(),
            'complaint_description' => fake()->paragraph(),
            'complaint_category' => fake()->randomElement(['product_quality', 'service_quality', 'delivery', 'documentation', 'other']),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'severity' => fake()->randomElement(['minor', 'major', 'critical']),
            'status' => fake()->randomElement(['new', 'acknowledged', 'investigating', 'resolved', 'closed', 'escalated']),
            'assigned_to_department_id' => Department::factory(),
            'assigned_to_user_id' => User::factory(),
            'received_by' => User::factory(),
        ];
    }

    /**
     * Complaint with high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    /**
     * Complaint is new/unresolved.
     */
    public function statusNew(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'new',
        ]);
    }

    /**
     * Complaint is resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'resolved_date' => now()->format('Y-m-d'),
        ]);
    }
}
