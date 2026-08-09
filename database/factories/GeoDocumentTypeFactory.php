<?php

namespace Database\Factories;

use App\Models\GeoDocumentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GeoDocumentType>
 */
class GeoDocumentTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'color' => fake()->randomElement(['primary', 'info', 'success', 'warning', 'danger', 'gray']),
            'site_scope' => 'all',
            'is_active' => true,
        ];
    }
}
