<?php

namespace Database\Factories;

use App\Enums\FinancialFlowType;
use App\Enums\Site;
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
            'type' => FinancialFlowType::Consultant,
            'sites' => null,
            'title' => fake()->sentence(4),
            'period_month' => fake()->dateTimeBetween('-1 year', 'now'),
            'amount' => fake()->randomFloat(2, 10000, 5000000),
            'document_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'file_path' => 'financial-flows/placeholder.pdf',
        ];
    }

    public function ofType(FinancialFlowType $type, ?Site $site = null): static
    {
        return $this->state([
            'type' => $type,
            'sites' => $site !== null ? [$site] : null,
        ]);
    }
}
