<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Certificate>
 */
class CertificateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $issueDate = fake()->dateTimeBetween('-1 year', 'now');
        $expiryDate = fake()->dateTimeBetween($issueDate->format('Y-m-d') . ' +2 years', $issueDate->format('Y-m-d') . ' +3 years');

        return [
            'certificate_number' => 'CERT-' . date('Y') . '-' . str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'standard' => fake()->randomElement([
                'ISO 9001:2015',
                'ISO 14001:2015',
                'ISO 45001:2018',
                'API Q1',
                'IATF 16949:2016',
            ]),
            'certification_body' => fake()->randomElement(['BSI', 'SGS', 'TUV', 'DNV', 'LRQA']),
            'certificate_type' => fake()->randomElement(['initial', 'renewal', 'transfer']),
            'issue_date' => $issueDate->format('Y-m-d'),
            'expiry_date' => $expiryDate->format('Y-m-d'),
            'status' => fake()->randomElement(['valid', 'expiring_soon', 'expired', 'suspended', 'revoked']),
            'scope_of_certification' => fake()->paragraph(),
            'covered_sites' => fake()->randomElements(['Main Office', 'Plant A', 'Plant B', 'Warehouse'], rand(1, 3)),
            'covered_processes' => fake()->randomElements(['Manufacturing', 'Design', 'Service', 'Sales'], rand(1, 3)),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Certificate is expiring soon (within 30 days).
     */
    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expiring_soon',
            'expiry_date' => now()->addDays(30)->format('Y-m-d'),
        ]);
    }

    /**
     * Certificate has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expiry_date' => now()->subDays(10)->format('Y-m-d'),
        ]);
    }

    /**
     * Certificate is valid.
     */
    public function valid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'valid',
            'expiry_date' => now()->addYear()->format('Y-m-d'),
        ]);
    }
}
