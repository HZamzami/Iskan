<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SiteScope: string implements HasLabel
{
    case None = 'none';
    case All = 'all';
    case Custom = 'custom';

    public function getLabel(): string
    {
        return match ($this) {
            self::None => 'غير مرتبط بالمواقع',
            self::All => 'جميع المواقع',
            self::Custom => 'مواقع محددة',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::None => 'مستند عام لا يرتبط بموقع معيّن',
            self::All => 'يشمل أي موقع يُضاف مستقبلاً تلقائياً',
            self::Custom => 'اختر المواقع التي ينطبق عليها هذا النوع',
        };
    }
}
