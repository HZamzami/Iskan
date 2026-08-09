<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Enums\Module;
use App\Filament\Resources\Locations\Pages\CreateLocation;
use App\Filament\Resources\Locations\Pages\ListLocations;
use App\Filament\Resources\Locations\Pages\ViewLocation;
use App\Models\Location;
use App\Models\Minute;
use App\Models\MinuteType;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class LocationResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_locations_list(): void
    {
        $this->actingAs($this->makeUserWithAccess([
            Module::Minutes->value => AccessLevel::Edit,
        ]));

        Livewire::test(ListLocations::class)
            ->assertForbidden();
    }

    public function test_admin_can_create_location_and_permission_is_created(): void
    {
        $this->actingAs($this->makeAdminUser());

        Livewire::test(CreateLocation::class)
            ->fillForm([
                'name' => 'موقع د الجديد',
                'color' => 'success',
                'icon' => 'flag',
                'contractor' => 'مقاول تجريبي',
                'consultant' => 'استشاري تجريبي',
                'is_active' => true,
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors();

        $location = Location::query()->where('name', 'موقع د الجديد')->firstOrFail();

        $this->assertNotEmpty($location->slug);
        $this->assertTrue(Permission::where('name', $location->permissionName())->exists());
    }

    public function test_new_location_is_instantly_allowed_on_all_scoped_types_without_editing_the_type(): void
    {
        $this->actingAs($this->makeAdminUser());

        /** @var MinuteType $allScopedType */
        $allScopedType = MinuteType::query()->where('slug', 'weekly_meeting')->firstOrFail();
        $this->assertSame('all', $allScopedType->site_scope->value);

        Livewire::test(CreateLocation::class)
            ->fillForm([
                'name' => 'موقع هـ',
                'color' => 'info',
                'icon' => 'map-pin',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $location = Location::query()->where('name', 'موقع هـ')->firstOrFail();

        $this->assertTrue(
            $allScopedType->refresh()->allowedLocations()->contains(fn (Location $allowed): bool => $allowed->is($location))
        );
    }

    public function test_deleting_a_location_in_use_is_blocked(): void
    {
        $this->actingAs($this->makeAdminUser());

        $location = Location::query()->where('slug', 'site_a')->firstOrFail();
        Minute::factory()->ofType('weekly_meeting', 'site_a')->create();

        Livewire::test(ListLocations::class)
            ->callAction(TestAction::make('delete')->table($location))
            ->assertNotified();

        $this->assertDatabaseHas('locations', ['id' => $location->id]);
    }

    public function test_apply_location_to_types_action_syncs_custom_scoped_types(): void
    {
        $this->actingAs($this->makeAdminUser());

        $location = Location::query()->where('slug', 'site_a')->firstOrFail();

        /** @var MinuteType $customType */
        $customType = MinuteType::query()->where('slug', 'asset_tagging')->firstOrFail();
        $this->assertSame('custom', $customType->site_scope->value);
        $this->assertContains('site_a', $customType->sites ?? []);

        Livewire::test(ViewLocation::class, ['record' => $location->getKey()])
            ->callAction('applyToTypes', data: [
                'modules' => [
                    Module::Minutes->value => [],
                ],
            ])
            ->assertNotified();

        $this->assertNotContains('site_a', $customType->refresh()->sites ?? []);
    }
}
