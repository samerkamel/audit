<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Quality Assurance',
                'Manufacturing',
                'Engineering',
                'Sales & Marketing',
                'Human Resources',
                'Finance',
                'Operations',
                'IT Department',
            ]),
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'description' => fake()->sentence(),
            'sector_id' => \App\Models\Sector::factory(),
        ];
    }
}
