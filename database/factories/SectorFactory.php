<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sector>
 */
class SectorFactory extends Factory
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
                'Manufacturing',
                'Services',
                'Technology',
                'Healthcare',
                'Education',
                'Finance',
                'Retail',
                'Logistics',
            ]),
            'name_ar' => fake()->randomElement([
                'التصنيع',
                'الخدمات',
                'التكنولوجيا',
                'الرعاية الصحية',
                'التعليم',
                'المالية',
                'التجزئة',
                'اللوجستية',
            ]),
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'description' => fake()->sentence(),
        ];
    }
}
