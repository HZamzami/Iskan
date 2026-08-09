<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

/**
 * مجموعة مختارة من الأيقونات المناسبة للمواقع، لتفادي عرض قائمة أيقونات
 * Filament الكاملة (مئات الخيارات) في نموذج بسيط.
 */
enum LocationIcon: string implements HasIcon, HasLabel
{
    case MapPin = 'map-pin';
    case Map = 'map';
    case BuildingOffice = 'building-office';
    case BuildingOffice2 = 'building-office-2';
    case BuildingLibrary = 'building-library';
    case BuildingStorefront = 'building-storefront';
    case HomeModern = 'home-modern';
    case Home = 'home';
    case Squares2x2 = 'squares-2x2';
    case Flag = 'flag';
    case GlobeAlt = 'globe-alt';
    case Truck = 'truck';

    public function getLabel(): string
    {
        return match ($this) {
            self::MapPin => 'دبوس موقع',
            self::Map => 'خريطة',
            self::BuildingOffice => 'مبنى إداري',
            self::BuildingOffice2 => 'برج',
            self::BuildingLibrary => 'مبنى حكومي',
            self::BuildingStorefront => 'مبنى تجاري',
            self::HomeModern => 'مجمع سكني',
            self::Home => 'منزل',
            self::Squares2x2 => 'مربعات',
            self::Flag => 'علم',
            self::GlobeAlt => 'كرة أرضية',
            self::Truck => 'شاحنة',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::MapPin => Heroicon::MapPin,
            self::Map => Heroicon::Map,
            self::BuildingOffice => Heroicon::BuildingOffice,
            self::BuildingOffice2 => Heroicon::BuildingOffice2,
            self::BuildingLibrary => Heroicon::BuildingLibrary,
            self::BuildingStorefront => Heroicon::BuildingStorefront,
            self::HomeModern => Heroicon::HomeModern,
            self::Home => Heroicon::Home,
            self::Squares2x2 => Heroicon::Squares2x2,
            self::Flag => Heroicon::Flag,
            self::GlobeAlt => Heroicon::GlobeAlt,
            self::Truck => Heroicon::Truck,
        };
    }
}
