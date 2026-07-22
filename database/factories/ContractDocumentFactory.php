<?php

namespace Database\Factories;

use App\Enums\ContractDocumentType;
use App\Enums\Site;
use App\Models\ContractDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContractDocument>
 */
class ContractDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => ContractDocumentType::ConsultantContract,
            'site' => null,
            'title' => fake()->sentence(4),
            'contracting_party' => fake()->company(),
            'document_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'file_path' => 'contract-documents/placeholder.pdf',
        ];
    }

    public function ofType(ContractDocumentType $type, ?Site $site = null): static
    {
        return $this->state([
            'type' => $type,
            'site' => $site,
        ]);
    }
}
