<?php

namespace Database\Factories;

use App\Models\GeoDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GeoDocument>
 */
class GeoDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'gis',
            'sites' => ['site_a'],
            'title' => fake()->sentence(4),
            'drawing_number' => fake()->bothify('DWG-####'),
            'document_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'file_path' => 'geo-documents/placeholder.pdf',
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
