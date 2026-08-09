<?php

namespace App\Filament\Resources\Concerns;

use App\Models\Location;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;

/**
 * يقصر استعلام المورد على سجلات المواقع المسموح بها للمستخدم، مع إبقاء
 * السجلات العامة (بدون مواقع) ظاهرة للجميع.
 */
trait HasSiteRestrictedQuery
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('documentType');

        /** @var User|null $user */
        $user = Filament::auth()->user();
        $allowed = $user?->allowedSites();

        if ($allowed !== null) {
            $query->where(function (Builder $q) use ($allowed): void {
                $q->whereNull('sites')->orWhereJsonLength('sites', 0);

                /** @var Location $location */
                foreach ($allowed as $location) {
                    $q->orWhereJsonContains('sites', $location->slug);
                }
            });
        }

        return $query;
    }
}
