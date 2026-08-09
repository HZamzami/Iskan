<?php

namespace Database\Factories;

use App\Models\RequirementGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RequirementGroup>
 */
class RequirementGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'color' => fake()->randomElement(['primary', 'info', 'success', 'warning', 'danger', 'gray']),
            'is_active' => true,
        ];
    }
}
