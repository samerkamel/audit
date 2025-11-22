<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExternalAudit>
 */
class ExternalAuditFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+3 months');
        $endDate = fake()->dateTimeBetween($startDate, $startDate->format('Y-m-d') . ' +5 days');

        return [
            'audit_number' => 'EA-' . date('Y') . '-' . str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'audit_type' => fake()->randomElement(['initial', 'surveillance', 'recertification', 'special']),
            'standard' => 'ISO 9001:2015',
            'certification_body' => fake()->randomElement(['BSI', 'SGS', 'TUV', 'DNV', 'LRQA']),
            'lead_auditor_name' => fake()->name(),
            'lead_auditor_email' => fake()->safeEmail(),
            'scheduled_start_date' => $startDate->format('Y-m-d'),
            'scheduled_end_date' => $endDate->format('Y-m-d'),
            'scope_description' => fake()->sentence(),
            'status' => fake()->randomElement(['scheduled', 'in_progress', 'completed', 'cancelled']),
            'result' => fake()->randomElement(['pending', 'passed', 'passed_with_conditions', 'failed']),
        ];
    }
}
