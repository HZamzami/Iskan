<?php

namespace App\Filament\Widgets;

use App\Enums\Module;
use App\Filament\Widgets\Concerns\AppliesSiteScope;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

/**
 * ودجة "بانتظار إجرائي": تجمع سجلات سير الاعتماد المُسندة حالياً للمستخدم
 * الحالي عبر كل الوحدات الست (Correspondences مستثناة لأنها لا تملك مفهوم
 * نوع/سير اعتماد أصلاً).
 */
class PendingMyAction extends Widget
{
    use AppliesSiteScope;

    protected string $view = 'filament.widgets.pending-my-action';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = ['default' => 1, 'xl' => 2];

    public static function canView(): bool
    {
        return Filament::auth()->user() instanceof User;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $user = $this->currentUser();
        $items = collect();

        if ($user === null) {
            return ['items' => $items];
        }

        foreach (Module::cases() as $module) {
            if ($module === Module::Correspondences) {
                continue;
            }

            $records = $module->modelClass()::query()
                ->where('assigned_to', $user->id)
                ->get();

            foreach ($records as $record) {
                $items->push([
                    'module' => $module,
                    'title' => $record->title,
                    'reference_number' => $record->reference_number,
                    'status' => $record->workflow_status,
                    'updated_at' => $record->updated_at,
                    'url' => $module->resourceClass()::getUrl('view', ['record' => $record]),
                ]);
            }
        }

        return ['items' => $items->sortBy('updated_at')->take(10)->values()];
    }
}
