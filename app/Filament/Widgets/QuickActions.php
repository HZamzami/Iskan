<?php

namespace App\Filament\Widgets;

use App\Enums\AccessLevel;
use App\Enums\Module;
use App\Filament\Widgets\Concerns\AppliesSiteScope;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class QuickActions extends Widget
{
    use AppliesSiteScope;

    protected string $view = 'filament.widgets.quick-actions';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        foreach (Module::cases() as $module) {
            if ($user->hasModuleAccess($module, AccessLevel::Write)) {
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
        return [
            'actions' => collect($this->accessibleModules(AccessLevel::Write))
                ->map(fn (Module $module): array => [
                    'label' => $module->createLabel(),
                    'icon' => $module->getIcon(),
                    'url' => $module->resourceClass()::getUrl('create'),
                ])
                ->values(),
        ];
    }
}
