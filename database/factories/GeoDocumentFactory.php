<?php

namespace Database\Factories;

use App\Enums\GeoDocumentType;
use App\Enums\Site;
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
            'type' => GeoDocumentType::Gis,
            'site' => Site::SiteA,
            'title' => fake()->sentence(4),
            'drawing_number' => fake()->bothify('DWG-####'),
            'document_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'file_path' => 'geo-documents/placeholder.pdf',
        ];
    }

    public function ofType(GeoDocumentType $type, ?Site $site = null): static
    {
        return $this->state([
            'type' => $type,
            'site' => $site,
        ]);
    }
}
