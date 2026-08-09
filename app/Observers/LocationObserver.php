<?php

namespace App\Observers;

use App\Models\Location;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * يُنشئ صلاحية "site.{slug}" تلقائياً عند إضافة موقع جديد من لوحة الإدارة،
 * ويحذفها عند حذف الموقع، حتى لا يحتاج المدير لإعادة تشغيل seeders يدوياً.
 */
class LocationObserver
{
    public function created(Location $location): void
    {
        Permission::findOrCreate($location->permissionName(), 'web');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function deleted(Location $location): void
    {
        Permission::where('name', $location->permissionName())->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
