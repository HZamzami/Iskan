<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FinancialFlowType: string implements HasColor, HasLabel
{
    case Consultant = 'consultant';
    case Operation = 'operation';
    case InternalProjects = 'internal_projects';

    public function getLabel(): string
    {
        return match ($this) {
            self::Consultant => 'التدفقات المالية الخاصة بعقد الإستشاري',
            self::Operation => 'التدفقات المالية الخاصة بعقد الصيانة والتشغيل',
            self::InternalProjects => 'التدفقات المالية الخاصة بعقود المشاريع الداخلية',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Consultant => 'info',
            self::Operation => 'warning',
            self::InternalProjects => 'gray',
        };
    }

    /**
     * المواقع التي ينطبق عليها هذا النوع، أو null إذا لم يكن مرتبطاً بالمواقع.
     *
     * @return array<int, Site>|null
     */
    public function sites(): ?array
    {
        return match ($this) {
            self::Operation => Site::cases(),
            default => null,
        };
    }
}
