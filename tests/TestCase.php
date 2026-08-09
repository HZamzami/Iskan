<?php

namespace Tests;

use App\Enums\AccessLevel;
use App\Enums\Module;
use App\Models\User;
use Database\Seeders\LookupSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    protected function seedAccessControl(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->seed(LookupSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function makeAdminUser(): User
    {
        $this->seedAccessControl();

        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    /**
     * @param  array<string, AccessLevel>  $modules  keyed by Module value
     * @param  array<int, string>  $sites  location slugs
     */
    protected function makeUserWithAccess(array $modules, array $sites = []): User
    {
        $this->seedAccessControl();

        $user = User::factory()->create();

        foreach ($modules as $module => $level) {
            $user->givePermissionTo(Module::from($module)->permission($level));
        }

        foreach ($sites as $site) {
            $user->givePermissionTo("site.{$site}");
        }

        return $user;
    }
}
