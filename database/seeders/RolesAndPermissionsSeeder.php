<?php

namespace Database\Seeders;

use App\Enums\AccessLevel;
use App\Enums\Module;
use App\Enums\Site;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Module::cases() as $module) {
            foreach (AccessLevel::cases() as $level) {
                Permission::findOrCreate($module->permission($level), 'web');
            }
        }

        foreach (Site::cases() as $site) {
            Permission::findOrCreate("site.{$site->value}", 'web');
        }

        Role::findOrCreate('admin', 'web');

        User::query()
            ->firstOrCreate(
                ['email' => 'admin@iskan.test'],
                ['name' => 'مدير النظام', 'password' => 'password'],
            )
            ->assignRole('admin');
    }
}
