<?php

namespace App\Filament\Widgets;

use App\Enums\CorrespondenceDirection;
use App\Enums\CorrespondenceStatus;
use App\Models\Correspondence;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CorrespondenceStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('المعاملات الواردة', Correspondence::where('direction', CorrespondenceDirection::Incoming)->count())
                ->description('إجمالي الوارد في الأرشيف')
                ->descriptionIcon(Heroicon::ArrowDownTray)
                ->color('info'),
            Stat::make('المعاملات الصادرة', Correspondence::where('direction', CorrespondenceDirection::Outgoing)->count())
                ->description('إجمالي الصادر في الأرشيف')
                ->descriptionIcon(Heroicon::ArrowUpTray)
                ->color('warning'),
            Stat::make('قيد المعالجة', Correspondence::where('status', CorrespondenceStatus::InProgress)->count())
                ->description('معاملات تنتظر الإنجاز')
                ->descriptionIcon(Heroicon::Clock)
                ->color('danger'),
        ];
    }
}
