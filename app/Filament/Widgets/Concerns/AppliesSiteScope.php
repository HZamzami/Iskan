<?php

namespace App\Filament\Widgets\Concerns;

use App\Enums\AccessLevel;
use App\Enums\Module;
use App\Enums\Site;
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
    protected function filteredSite(): ?Site
    {
        $value = $this->pageFilters['site'] ?? null;
        $site = is_string($value) ? Site::tryFrom($value) : null;

        if ($site === null) {
            return null;
        }

        return $this->currentUser()?->canAccessSite($site) === true ? $site : null;
    }

    protected function applySiteScope(Builder $query): Builder
    {
        $filtered = $this->filteredSite();

        if ($filtered !== null) {
            return $query->whereJsonContains('sites', $filtered->value);
        }

        $allowed = $this->currentUser()?->allowedSites();

        if ($allowed === null) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($allowed): void {
            $q->whereNull('sites')->orWhereJsonLength('sites', 0);

            /** @var Site $site */
            foreach ($allowed as $site) {
                $q->orWhereJsonContains('sites', $site->value);
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
