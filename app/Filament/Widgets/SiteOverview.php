<?php

namespace App\Filament\Widgets;

use App\Enums\Module;
use App\Enums\PaletteColor;
use App\Filament\Widgets\Concerns\AppliesSiteScope;
use App\Models\Location;
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
        $sites = $user?->allowedSites() ?? Location::active()->ordered()->get()->all();
        $modules = array_values(array_filter(
            $this->accessibleModules(),
            fn (Module $module): bool => $module->isSiteScoped(),
        ));
        $counts = [];

        foreach ($modules as $module) {
            $counts[$module->value] = collect($sites)->mapWithKeys(
                fn (Location $location): array => [
                    $location->slug => $module->modelClass()::query()
                        ->whereJsonContains('sites', $location->slug)
                        ->count(),
                ],
            )->all();
        }

        $cards = collect($sites)->map(fn (Location $location): array => [
            'site' => $location,
            'accent' => (PaletteColor::tryFrom($location->color) ?? PaletteColor::Primary)->hex(),
            'modules' => collect($modules)->map(fn (Module $module): array => [
                'module' => $module,
                'count' => (int) ($counts[$module->value][$location->slug] ?? 0),
                'url' => $module->resourceClass()::getUrl('index'),
            ]),
            'total' => collect($modules)->sum(
                fn (Module $module): int => (int) ($counts[$module->value][$location->slug] ?? 0),
            ),
        ])->all();

        return ['cards' => $cards];
    }
}
