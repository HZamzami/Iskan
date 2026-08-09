<?php

namespace Database\Factories;

use App\Models\ContractualRequirementType;
use App\Models\RequirementGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContractualRequirementType>
 */
class ContractualRequirementTypeFactory extends Factory
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
            'requirement_group_id' => RequirementGroup::factory(),
            'site_scope' => 'all',
            'is_active' => true,
        ];
    }
}
