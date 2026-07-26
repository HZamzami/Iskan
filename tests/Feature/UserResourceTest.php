<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Enums\Module;
use App\Enums\Site;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
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
