<?php

namespace Database\Factories;

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
            'certificate_name' => fake()->randomElement([
                'ISO 9001:2015 Certification',
                'ISO 14001 Environmental Certification',
                'ISO 45001 Health & Safety Certification',
                'API Certification',
                'IATF 16949 Automotive Certification',
            ]),
            'certificate_type' => fake()->randomElement(['iso_certification', 'accreditation', 'license', 'other']),
            'issuing_authority' => fake()->randomElement(['BSI', 'SGS', 'TUV', 'DNV', 'LRQA']),
            'issue_date' => $issueDate->format('Y-m-d'),
            'expiry_date' => $expiryDate->format('Y-m-d'),
            'scope' => fake()->sentence(),
            'status' => fake()->randomElement(['active', 'expiring_soon', 'expired', 'suspended', 'revoked']),
            'department_id' => \App\Models\Department::factory(),
            'sector_id' => \App\Models\Sector::factory(),
        ];
    }
}
