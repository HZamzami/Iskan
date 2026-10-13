<?php

namespace App\Filament\Resources\Concerns;

use App\Enums\Module;
use App\Models\Location;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;

/**
 * يقصر استعلام المورد على سجلات المواقع المسموح بها للمستخدم، مع إبقاء
 * السجلات العامة (بدون مواقع) ظاهرة للجميع، وكذلك السجلات المُرحَّلة إلى
 * المستخدم حالياً (وإلا لفتح إشعار الترحيل صفحة 404).
 * ومن لا يملك صلاحية على الوحدة أصلاً (دخلها لأن سجلاً رُحِّل إليه) لا يرى
 * إلا السجلات التي بعهدته.
 */
trait HasSiteRestrictedQuery
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('documentType');

        /** @var User|null $user */
        $user = Filament::auth()->user();
        $module = Module::fromModelClass(static::getModel());

        if ($user !== null && $module !== null && $user->accessLevelFor($module) === null) {
            return $query->where('assigned_to', $user->id);
        }

        $allowed = $user?->allowedSites();

        if ($allowed !== null) {
            $query->where(function (Builder $q) use ($allowed, $user): void {
                $q->whereNull('sites')
                    ->orWhereJsonLength('sites', 0)
                    ->orWhere('assigned_to', $user->id);

                /** @var Location $location */
                foreach ($allowed as $location) {
                    $q->orWhereJsonContains('sites', $location->slug);
                }
            });
        }

        return $query;
    }
}
