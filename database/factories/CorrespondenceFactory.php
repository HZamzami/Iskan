<?php

namespace Database\Factories;

use App\Enums\CorrespondenceDirection;
use App\Enums\CorrespondenceStatus;
use App\Models\Correspondence;
use App\Models\Entity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Correspondence>
 */
class CorrespondenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject' => fake()->sentence(6),
            'direction' => fake()->randomElement(CorrespondenceDirection::cases()),
            'status' => fake()->randomElement(CorrespondenceStatus::cases()),
            'sender' => fake()->name(),
            'recipient' => fake()->name(),
            'entity_id' => Entity::factory(),
            'document_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'file_path' => 'correspondence-files/placeholder.pdf',
        ];
    }

    public function incoming(): static
    {
        return $this->state(['direction' => CorrespondenceDirection::Incoming]);
    }

    public function outgoing(): static
    {
        return $this->state(['direction' => CorrespondenceDirection::Outgoing]);
    }
}
