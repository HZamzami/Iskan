<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Enums\Module;
use App\Filament\Resources\Roles\Pages\ManageRoles;
use App\Filament\Support\WorkflowFormFields;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RoleResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_roles(): void
    {
        $this->actingAs($this->makeUserWithAccess([
            Module::Minutes->value => AccessLevel::Edit,
        ]));

        Livewire::test(ManageRoles::class)
            ->assertForbidden();
    }

    public function test_admin_can_create_a_new_role(): void
    {
        $this->actingAs($this->makeAdminUser());

        Livewire::test(ManageRoles::class)
            ->callAction('create', data: [
                'name' => 'مورّد',
                'is_active' => true,
            ])
            ->assertNotified()
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('roles_lookup', ['name' => 'مورّد']);
    }

    public function test_new_role_is_immediately_selectable_in_the_workflow_category_picker(): void
    {
        $this->actingAs($this->makeAdminUser());

        Livewire::test(ManageRoles::class)
            ->callAction('create', data: [
                'name' => 'مورّد',
                'is_active' => true,
            ])
            ->assertHasNoActionErrors();

        $supplier = Role::query()->where('name', 'مورّد')->firstOrFail();

        $user = User::factory()->create(['role_id' => $supplier->id]);

        $eligible = WorkflowFormFields::eligibleUsers($supplier->id, []);

        $this->assertArrayHasKey($user->id, $eligible);
    }

    public function test_deleting_a_role_in_use_is_blocked(): void
    {
        $this->actingAs($this->makeAdminUser());

        $role = Role::query()->where('slug', 'consultant')->firstOrFail();
        User::factory()->create(['role_id' => $role->id]);

        Livewire::test(ManageRoles::class)
            ->callAction(TestAction::make('delete')->table($role))
            ->assertNotified();

        $this->assertDatabaseHas('roles_lookup', ['id' => $role->id]);
    }
}
