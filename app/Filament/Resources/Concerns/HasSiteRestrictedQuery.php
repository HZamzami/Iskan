<?php

namespace App\Filament\Resources\Concerns;

use App\Enums\Site;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;

/**
 * يقصر استعلام المورد على سجلات المواقع المسموح بها للمستخدم، مع إبقاء
 * السجلات العامة (بدون موقع) ظاهرة للجميع.
 */
trait HasSiteRestrictedQuery
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        /** @var User|null $user */
        $user = Filament::auth()->user();
        $allowed = $user?->allowedSites();

        if ($allowed !== null) {
            $values = array_map(fn (Site $site): string => $site->value, $allowed);

            $query->where(fn (Builder $q) => $q->whereNull('site')->orWhereIn('site', $values));
        }

        return $query;
    }
}
