<?php

namespace App\Filament\Widgets;

use App\Enums\TaskStatus;
use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Task;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TasksOverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return Filament::auth()->user() instanceof User;
    }

    protected function getStats(): array
    {
        $userId = Filament::auth()->id();

        $pending = Task::query()
            ->instances()
            ->where('assigned_to', $userId)
            ->where('status', '!=', TaskStatus::Completed)
            ->count();

        $overdue = Task::query()
            ->instances()
            ->where('assigned_to', $userId)
            ->where('status', '!=', TaskStatus::Completed)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->count();

        $completedThisWeek = Task::query()
            ->instances()
            ->where('assigned_to', $userId)
            ->where('status', TaskStatus::Completed)
            ->where('completed_at', '>=', now()->startOfWeek())
            ->count();

        return [
            Stat::make('مهامي المعلقة', $pending)
                ->description($pending > 0 ? 'مهام تنتظر إنجازك' : 'لا مهام معلقة')
                ->descriptionIcon(Heroicon::Clock)
                ->color($pending > 0 ? 'warning' : 'success')
                ->url(TaskResource::getUrl('index')),
            Stat::make('مهام متأخرة', $overdue)
                ->description($overdue > 0 ? 'تجاوزت تاريخ الانتهاء' : 'لا تأخير حالياً')
                ->descriptionIcon($overdue > 0 ? Heroicon::ExclamationTriangle : Heroicon::CheckCircle)
                ->color($overdue > 0 ? 'danger' : 'success')
                ->url(TaskResource::getUrl('index')),
            Stat::make('أُنجزت هذا الأسبوع', $completedThisWeek)
                ->description('مهامي المكتملة منذ بداية الأسبوع')
                ->descriptionIcon(Heroicon::CheckCircle)
                ->color('success')
                ->url(TaskResource::getUrl('index')),
        ];
    }
}
