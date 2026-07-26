<?php

namespace App\Filament\Widgets;

use App\Enums\Module;
use App\Enums\Site;
use App\Filament\Widgets\Concerns\AppliesSiteScope;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class SiteOverview extends Widget
{
    use AppliesSiteScope;

    protected string $view = 'filament.widgets.site-overview';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->allowedSites() !== [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $user = $this->currentUser();
        $sites = $user?->allowedSites() ?? Site::cases();
        $modules = array_values(array_filter(
            $this->accessibleModules(),
            fn (Module $module): bool => $module->isSiteScoped(),
        ));
        $siteValues = array_map(fn (Site $site): string => $site->value, $sites);

        $counts = [];

        foreach ($modules as $module) {
            $counts[$module->value] = $module->modelClass()::query()
                ->whereIn('site', $siteValues)
                ->selectRaw('site, count(*) as total')
                ->groupBy('site')
                ->pluck('total', 'site')
                ->all();
        }

        $cards = collect($sites)->map(fn (Site $site): array => [
            'site' => $site,
            'modules' => collect($modules)->map(fn (Module $module): array => [
                'module' => $module,
                'count' => (int) ($counts[$module->value][$site->value] ?? 0),
                'url' => $module->resourceClass()::getUrl('index'),
            ]),
            'total' => collect($modules)->sum(
                fn (Module $module): int => (int) ($counts[$module->value][$site->value] ?? 0),
            ),
        ])->all();

        return ['cards' => $cards];
    }
}
