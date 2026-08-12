<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum TaskPriority: string implements HasColor, HasIcon, HasLabel
{
    case Urgent = 'urgent';
    case Medium = 'medium';
    case Normal = 'normal';

    public function getLabel(): string
    {
        return match ($this) {
            self::Urgent => 'عاجلة',
            self::Medium => 'متوسطة',
            self::Normal => 'عادية',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Urgent => 'danger',
            self::Medium => 'warning',
            self::Normal => 'gray',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Urgent => Heroicon::ExclamationTriangle,
            self::Medium => Heroicon::MinusCircle,
            self::Normal => Heroicon::Bars2,
        };
    }
}
