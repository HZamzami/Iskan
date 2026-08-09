<?php

namespace Database\Factories;

use App\Models\FinancialFlow;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialFlow>
 */
class FinancialFlowFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'consultant',
            'sites' => null,
            'title' => fake()->sentence(4),
            'period_month' => fake()->dateTimeBetween('-1 year', 'now'),
            'amount' => fake()->randomFloat(2, 10000, 5000000),
            'document_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'file_path' => 'financial-flows/placeholder.pdf',
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
