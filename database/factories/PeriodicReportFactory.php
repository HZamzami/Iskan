<?php

namespace Database\Factories;

use App\Models\PeriodicReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PeriodicReport>
 */
class PeriodicReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'weekly_progress',
            'sites' => ['site_a'],
            'title' => fake()->sentence(4),
            'period' => fake()->dateTimeBetween('-1 year', 'now'),
            'document_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'file_path' => 'periodic-reports/placeholder.pdf',
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
