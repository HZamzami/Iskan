<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum Site: string implements HasColor, HasIcon, HasLabel
{
    case SiteA = 'site_a';
    case SiteB = 'site_b';
    case SiteC = 'site_c';
    case AbrajKudanah = 'abraj_kudanah';

    public function getLabel(): string
    {
        return match ($this) {
            self::SiteA => 'موقع (أ)',
            self::SiteB => 'موقع (ب)',
            self::SiteC => 'موقع (ج)',
            self::AbrajKudanah => 'أبراج كدانة الوادي',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SiteA => 'info',
            self::SiteB => 'success',
            self::SiteC => 'warning',
            self::AbrajKudanah => 'danger',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::AbrajKudanah => Heroicon::BuildingOffice2,
            default => Heroicon::MapPin,
        };
    }

    /**
     * أقسام المخيمات (أ، ب، ج) دون أبراج كدانة الوادي.
     *
     * @return array<int, self>
     */
    public static function campSites(): array
    {
        return [self::SiteA, self::SiteB, self::SiteC];
    }
}
