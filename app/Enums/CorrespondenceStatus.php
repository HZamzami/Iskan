<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum CorrespondenceStatus: string implements HasColor, HasIcon, HasLabel
{
    case New = 'new';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return match ($this) {
            self::New => 'جديدة',
            self::InProgress => 'قيد المعالجة',
            self::Completed => 'منجزة',
            self::Archived => 'مؤرشفة',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::New => 'gray',
            self::InProgress => 'warning',
            self::Completed => 'success',
            self::Archived => 'info',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::New => Heroicon::Sparkles,
            self::InProgress => Heroicon::Clock,
            self::Completed => Heroicon::CheckCircle,
            self::Archived => Heroicon::ArchiveBox,
        };
    }
}
