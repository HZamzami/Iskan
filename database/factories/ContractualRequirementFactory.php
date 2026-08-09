<?php

namespace Database\Factories;

use App\Models\ContractualRequirement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContractualRequirement>
 */
class ContractualRequirementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'labor_count',
            'sites' => ['site_a'],
            'title' => fake()->sentence(4),
            'period' => fake()->dateTimeBetween('-1 year', 'now'),
            'document_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'file_path' => 'contractual-requirements/placeholder.pdf',
        ];
    }

    public function ofType(string $typeSlug, ?string $siteSlug = null): static
    {
        return $this->state([
            'type' => $typeSlug,
            'sites' => $siteSlug !== null ? [$siteSlug] : null,
        ]);
    }
}
