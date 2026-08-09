<?php

namespace Tests\Unit;

use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LookupSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_arabic_name_transliterates_to_a_non_empty_slug(): void
    {
        $location = Location::create(['name' => 'موقع الاختبار']);

        $this->assertNotEmpty($location->slug);
        $this->assertMatchesRegularExpression('/^[a-z0-9_]+$/', $location->slug);
    }

    public function test_duplicate_names_get_a_unique_suffixed_slug(): void
    {
        $first = Location::create(['name' => 'Test Location']);
        $second = Location::create(['name' => 'Test Location']);
        $third = Location::create(['name' => 'Test Location']);

        $this->assertSame('test_location', $first->slug);
        $this->assertSame('test_location_2', $second->slug);
        $this->assertSame('test_location_3', $third->slug);
    }

    public function test_blank_or_punctuation_only_name_falls_back_to_class_basename(): void
    {
        $location = Location::create(['name' => '!!!']);

        $this->assertSame('location', $location->slug);
    }

    public function test_explicit_slug_is_preserved_on_create(): void
    {
        $location = Location::create(['name' => 'Custom', 'slug' => 'custom_slug']);

        $this->assertSame('custom_slug', $location->slug);
    }

    public function test_slug_is_immutable_after_creation(): void
    {
        $location = Location::create(['name' => 'Original Name']);
        $originalSlug = $location->slug;

        $location->update(['slug' => 'attempted_new_slug', 'name' => 'Renamed']);

        $this->assertSame($originalSlug, $location->fresh()->slug);
        $this->assertSame('Renamed', $location->fresh()->name);
    }
}
