<?php

namespace App\Filament\Widgets\Concerns;

use App\Enums\AccessLevel;
use App\Enums\Module;
use App\Models\Location;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

/**
 * يقيّد استعلامات ودجات لوحة المعلومات بمواقع المستخدم المسموح بها
 * وبفلتر الموقع المختار في أعلى اللوحة.
 */
trait AppliesSiteScope
{
    use InteractsWithPageFilters;

    protected function currentUser(): ?User
    {
        $user = Filament::auth()->user();

        return $user instanceof User ? $user : null;
    }

    /**
     * الموقع المختار في فلتر اللوحة، بعد التحقق من أنه ضمن مواقع المستخدم؛
     * الفلتر محفوظ في الجلسة وقد يحمل موقعاً سُحبت صلاحيته.
     */
    protected function filteredSite(): ?Location
    {
        $value = $this->pageFilters['site'] ?? null;
        $location = is_string($value) ? Location::cached()->firstWhere('slug', $value) : null;

        if ($location === null) {
            return null;
        }

        return $this->currentUser()?->canAccessSite($location) === true ? $location : null;
    }

    /**
     * نسخة ثابتة (static) من نطاق المواقع، لاستخدامها داخل canView() التي
     * تُستدعى قبل تركيب الودجة (قبل توفر فلتر الموقع اللحظي في الصفحة)؛
     * تقتصر على مواقع المستخدم المسموح بها فقط.
     */
    protected static function anyExistsForAllowedSites(Builder $query, ?User $user): bool
    {
        $allowed = $user?->allowedSites();

        if ($allowed === null) {
            return $query->exists();
        }

        return $query->where(function (Builder $q) use ($allowed): void {
            $q->whereNull('sites')->orWhereJsonLength('sites', 0);

            /** @var Location $location */
            foreach ($allowed as $location) {
                $q->orWhereJsonContains('sites', $location->slug);
            }
        })->exists();
    }

    protected function applySiteScope(Builder $query): Builder
    {
        $filtered = $this->filteredSite();

        if ($filtered !== null) {
            return $query->whereJsonContains('sites', $filtered->slug);
        }

        $allowed = $this->currentUser()?->allowedSites();

        if ($allowed === null) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($allowed): void {
            $q->whereNull('sites')->orWhereJsonLength('sites', 0);

            /** @var Location $location */
            foreach ($allowed as $location) {
                $q->orWhereJsonContains('sites', $location->slug);
            }
        });
    }

    /**
     * @return array<int, Module>
     */
    protected function accessibleModules(AccessLevel $minimum = AccessLevel::Read): array
    {
        $user = $this->currentUser();

        if ($user === null) {
            return [];
        }

        return array_values(array_filter(
            Module::cases(),
            fn (Module $module): bool => $user->hasModuleAccess($module, $minimum),
        ));
    }
}
