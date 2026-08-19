<?php

namespace App\Filament\Widgets;

use App\Enums\Module;
use App\Filament\Widgets\Concerns\AppliesSiteScope;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class LatestDocuments extends Widget
{
    use AppliesSiteScope;

    protected string $view = 'filament.widgets.latest-documents';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = ['default' => 1, 'xl' => 2];

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        foreach (Module::cases() as $module) {
            if ($user->accessLevelFor($module) !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $items = collect();

        foreach ($this->accessibleModules() as $module) {
            $query = $module->modelClass()::query()->latest()->limit(8);

            if ($module->isSiteScoped()) {
                $query = $this->applySiteScope($query);
            }

            foreach ($query->get() as $record) {
                $items->push([
                    'module' => $module,
                    'title' => $record->subject ?? $record->title,
                    'sites' => $module->isSiteScoped() ? $record->siteLocations() : collect(),
                    'created_at' => $record->created_at,
                    'url' => $module->resourceClass()::getUrl('view', ['record' => $record]),
                ]);
            }
        }

        return ['items' => $items->sortByDesc('created_at')->take(8)->values()];
    }
}
