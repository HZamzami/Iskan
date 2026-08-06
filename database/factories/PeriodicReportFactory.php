<?php

namespace Database\Factories;

use App\Enums\PeriodicReportType;
use App\Enums\Site;
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
            'type' => PeriodicReportType::WeeklyProgress,
            'sites' => [Site::SiteA],
            'title' => fake()->sentence(4),
            'period' => fake()->dateTimeBetween('-1 year', 'now'),
            'document_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'file_path' => 'periodic-reports/placeholder.pdf',
        ];
    }

    public function ofType(PeriodicReportType $type, ?Site $site = null): static
    {
        return $this->state([
            'type' => $type,
            'sites' => $site !== null ? [$site] : null,
        ]);
    }
}
