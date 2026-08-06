<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum GeoDocumentType: string implements HasColor, HasLabel
{
    case Gis = 'gis';
    case KmlKmz = 'kml_kmz';
    case AsBuiltDrawing = 'as_built_drawing';

    public function getLabel(): string
    {
        return match ($this) {
            self::Gis => 'GIS',
            self::KmlKmz => 'KML & KMZ',
            self::AsBuiltDrawing => 'المخططات كما نُفذت (As Built Drawing)',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Gis => 'info',
            self::KmlKmz => 'success',
            self::AsBuiltDrawing => 'warning',
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

    /**
     * امتدادات الملفات المسموحة لهذا النوع.
     *
     * @return array<int, string>
     */
    public function acceptedExtensions(): array
    {
        return match ($this) {
            self::Gis => ['gpkg', 'rar', 'zip'],
            self::KmlKmz => ['kml', 'kmz'],
            self::AsBuiltDrawing => ['pdf', 'dwg'],
        };
    }
}
