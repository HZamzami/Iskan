<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Enums\Module;
use App\Filament\Resources\EntityTypes\Pages\ManageEntityTypes;
use App\Models\Entity;
use App\Models\EntityType;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EntityTypeResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_entity_types(): void
    {
        $this->actingAs($this->makeUserWithAccess([
            Module::Minutes->value => AccessLevel::Edit,
        ]));

        Livewire::test(ManageEntityTypes::class)
            ->assertForbidden();
    }

    public function test_admin_can_create_a_new_entity_type(): void
    {
        $this->actingAs($this->makeAdminUser());

        Livewire::test(ManageEntityTypes::class)
            ->callAction('create', data: [
                'name' => 'مورد',
                'is_active' => true,
            ])
            ->assertNotified()
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas(EntityType::class, ['name' => 'مورد', 'slug' => 'mord']);
    }

    public function test_deleting_an_entity_type_in_use_is_blocked(): void
    {
        $this->actingAs($this->makeAdminUser());

        $type = EntityType::factory()->create();
        Entity::factory()->create(['entity_type_id' => $type->id]);

        Livewire::test(ManageEntityTypes::class)
            ->callAction(TestAction::make('delete')->table($type))
            ->assertNotified();

        $this->assertDatabaseHas(EntityType::class, ['id' => $type->id]);
    }
}
