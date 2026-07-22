<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PeriodicReportType: string implements HasColor, HasLabel
{
    case MonthlyReport = 'monthly_report';
    case FinalReport = 'final_report';
    case WeeklyProgress = 'weekly_progress';
    case WeeklyInventoryCoding = 'weekly_inventory_coding';
    case Guidelines = 'guidelines';

    public function getLabel(): string
    {
        return match ($this) {
            self::MonthlyReport => 'التقرير الشهري لمنظومة إسكان الحجاج بمشعر منى',
            self::FinalReport => 'التقرير الختامي لإدارة إسكان الحجاج بمشعر منى',
            self::WeeklyProgress => 'تقارير إنجاز الأعمال الأسبوعية',
            self::WeeklyInventoryCoding => 'تقارير الحصر والترميز الأسبوعية',
            self::Guidelines => 'الأدلة الإسترشادية والإجرائية',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::MonthlyReport => 'warning',
            self::FinalReport => 'danger',
            self::WeeklyProgress => 'info',
            self::WeeklyInventoryCoding => 'success',
            self::Guidelines => 'gray',
        };
    }

    /**
     * المواقع التي ينطبق عليها هذا النوع، أو null إذا لم يكن مرتبطاً بالمواقع.
     *
     * @return array<int, Site>|null
     */
    public function sites(): ?array
    {
        return Site::cases();
    }
}
