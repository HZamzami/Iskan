<?php

namespace Database\Factories;

use App\Enums\MinuteType;
use App\Enums\Site;
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
            'type' => MinuteType::WeeklyMeeting,
            'sites' => [Site::SiteA],
            'title' => fake()->sentence(4),
            'parties' => fake()->company(),
            'document_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'file_path' => 'minutes-files/placeholder.pdf',
        ];
    }

    public function ofType(MinuteType $type, ?Site $site = null): static
    {
        return $this->state([
            'type' => $type,
            'sites' => $site !== null ? [$site] : null,
        ]);
    }
}
