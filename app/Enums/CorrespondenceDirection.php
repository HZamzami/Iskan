<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum CorrespondenceDirection: string implements HasColor, HasIcon, HasLabel
{
    case Incoming = 'incoming';
    case Outgoing = 'outgoing';

    public function getLabel(): string
    {
        return match ($this) {
            self::Incoming => 'وارد',
            self::Outgoing => 'صادر',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Incoming => 'info',
            self::Outgoing => 'warning',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Incoming => Heroicon::ArrowDownTray,
            self::Outgoing => Heroicon::ArrowUpTray,
        };
    }

    public function referencePrefix(): string
    {
        return match ($this) {
            self::Incoming => 'و',
            self::Outgoing => 'ص',
        };
    }
}
