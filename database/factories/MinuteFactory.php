<?php

namespace Database\Factories;

use App\Models\Minute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Minute>
 */
class MinuteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'weekly_meeting',
            'sites' => ['site_a'],
            'title' => fake()->sentence(4),
            'parties' => fake()->company(),
            'document_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'file_path' => 'minutes-files/placeholder.pdf',
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
