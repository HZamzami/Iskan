<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Enums\Module;
use App\Enums\Site;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\User;
use Filament\Auth\Pages\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_users_list(): void
    {
        $this->actingAs($this->makeUserWithAccess([
            Module::Minutes->value => AccessLevel::Edit,
        ]));

        Livewire::test(ListUsers::class)
            ->assertForbidden();
    }

    public function test_admin_can_create_user_with_module_and_site_access(): void
    {
        $this->actingAs($this->makeAdminUser());

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'سعيد الغامدي',
                'email' => 'saeed@iskan.test',
                'password' => 'password123',
                'is_admin' => false,
                'modules' => [
                    Module::Minutes->value => AccessLevel::Read->value,
                    Module::PeriodicReports->value => AccessLevel::Edit->value,
                ],
                'sites' => [Site::SiteA->value],
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors();

        $user = User::query()->where('email', 'saeed@iskan.test')->firstOrFail();

        $this->assertFalse($user->isAdmin());
        $this->assertSame(AccessLevel::Read, $user->accessLevelFor(Module::Minutes));
        $this->assertSame(AccessLevel::Edit, $user->accessLevelFor(Module::PeriodicReports));
        $this->assertNull($user->accessLevelFor(Module::Correspondences));
        $this->assertSame([Site::SiteA], $user->allowedSites());
    }

    public function test_admin_can_create_admin_user(): void
    {
        $this->actingAs($this->makeAdminUser());

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'مدير آخر',
                'email' => 'admin2@iskan.test',
                'password' => 'password123',
                'is_admin' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::query()->where('email', 'admin2@iskan.test')->firstOrFail();

        $this->assertTrue($user->isAdmin());
        $this->assertCount(0, $user->getDirectPermissions());
    }

    public function test_edit_form_hydrates_access_state(): void
    {
        $this->actingAs($this->makeAdminUser());

        $user = User::factory()->create();
        $user->givePermissionTo(Module::Minutes->permission(AccessLevel::Write));
        $user->givePermissionTo('site.'.Site::SiteB->value);

        Livewire::test(EditUser::class, ['record' => $user->id])
            ->assertSchemaStateSet([
                'is_admin' => false,
                'modules.'.Module::Minutes->value => AccessLevel::Write,
                'sites' => [Site::SiteB],
            ]);
    }

    public function test_editing_replaces_previous_permissions(): void
    {
        $this->actingAs($this->makeAdminUser());

        $user = User::factory()->create();
        $user->givePermissionTo(Module::Minutes->permission(AccessLevel::Edit));

        Livewire::test(EditUser::class, ['record' => $user->id])
            ->fillForm([
                'modules' => [
                    Module::Minutes->value => null,
                    Module::GeoDocuments->value => AccessLevel::Read->value,
                ],
                'sites' => [],
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        $user->refresh()->load('permissions');

        $this->assertNull($user->accessLevelFor(Module::Minutes));
        $this->assertSame(AccessLevel::Read, $user->accessLevelFor(Module::GeoDocuments));
        $this->assertSame([], $user->allowedSites());
    }

    public function test_blank_password_keeps_existing_password_on_edit(): void
    {
        $this->actingAs($this->makeAdminUser());

        $user = User::factory()->create();
        $originalHash = $user->password;

        Livewire::test(EditUser::class, ['record' => $user->id])
            ->fillForm(['name' => 'اسم محدث', 'password' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();

        $this->assertSame('اسم محدث', $user->name);
        $this->assertSame($originalHash, $user->password);
    }

    public function test_admin_can_set_phone_and_entity_on_create_and_edit(): void
    {
        $this->actingAs($this->makeAdminUser());

        $entity = Entity::factory()->create();

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'منى العتيبي',
                'email' => 'mona@iskan.test',
                'phone' => '0501234567',
                'entity_id' => $entity->id,
                'password' => 'password123',
                'is_admin' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::query()->where('email', 'mona@iskan.test')->firstOrFail();

        $this->assertSame('0501234567', $user->phone);
        $this->assertTrue($user->entity->is($entity));

        $otherEntity = Entity::factory()->create();

        Livewire::test(EditUser::class, ['record' => $user->id])
            ->fillForm([
                'phone' => '0559876543',
                'entity_id' => $otherEntity->id,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();

        $this->assertSame('0559876543', $user->phone);
        $this->assertTrue($user->entity->is($otherEntity));
    }

    public function test_entity_with_type_is_linked_to_user(): void
    {
        $this->actingAs($this->makeAdminUser());

        $contractorType = EntityType::factory()->create(['name' => 'مقاول']);
        $entity = Entity::factory()->create([
            'name' => 'شركة الراجحي',
            'entity_type_id' => $contractorType->id,
        ]);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'خالد المطيري',
                'email' => 'khalid@iskan.test',
                'entity_id' => $entity->id,
                'password' => 'password123',
                'is_admin' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::query()->where('email', 'khalid@iskan.test')->firstOrFail();

        $this->assertTrue($user->entity->is($entity));
        $this->assertTrue($user->entity->entityType->is($contractorType));
        $this->assertSame('مقاول', $user->entity->entityType->name);
    }

    public function test_admin_can_deactivate_a_regular_user(): void
    {
        $this->actingAs($this->makeAdminUser());

        $user = User::factory()->create();

        Livewire::test(EditUser::class, ['record' => $user->id])
            ->fillForm(['is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($user->refresh()->is_active);
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->inactive()->create();

        Livewire::test(Login::class)
            ->fillForm([
                'email' => $user->email,
                'password' => 'password',
            ])
            ->call('authenticate');

        $this->assertGuest();
    }

    public function test_cannot_deactivate_last_admin(): void
    {
        $admin = $this->makeAdminUser();
        $this->actingAs($admin);

        User::query()
            ->where('email', 'admin@iskan.test')
            ->first()
            ?->removeRole('admin');

        Livewire::test(EditUser::class, ['record' => $admin->id])
            ->fillForm(['is_active' => false])
            ->call('save');

        $this->assertTrue($admin->refresh()->is_active);
    }

    public function test_can_deactivate_an_admin_when_another_active_admin_remains(): void
    {
        $admin = $this->makeAdminUser();
        $this->actingAs($admin);

        User::query()
            ->where('email', 'admin@iskan.test')
            ->first()
            ?->removeRole('admin');

        $otherAdmin = User::factory()->create();
        $otherAdmin->assignRole('admin');

        Livewire::test(EditUser::class, ['record' => $otherAdmin->id])
            ->fillForm(['is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($otherAdmin->refresh()->is_active);
    }

    public function test_cannot_demote_last_admin(): void
    {
        $admin = $this->makeAdminUser();
        $this->actingAs($admin);

        User::query()
            ->where('email', 'admin@iskan.test')
            ->first()
            ?->removeRole('admin');

        Livewire::test(EditUser::class, ['record' => $admin->id])
            ->fillForm(['is_admin' => false])
            ->call('save');

        $this->assertTrue($admin->refresh()->isAdmin());
    }
}
