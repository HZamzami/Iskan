<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TaskRecurrence: string implements HasColor, HasLabel
{
    case Once = 'once';
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';

    public function getLabel(): string
    {
        return match ($this) {
            self::Once => 'مرة واحدة',
            self::Daily => 'يومي',
            self::Weekly => 'أسبوعي',
            self::Monthly => 'شهري',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Once => 'gray',
            self::Daily => 'info',
            self::Weekly => 'warning',
            self::Monthly => 'success',
        };
    }
}
