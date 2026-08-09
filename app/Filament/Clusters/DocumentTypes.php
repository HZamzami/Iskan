<?php

namespace App\Filament\Clusters;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class DocumentTypes extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'الإدارة';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'أنواع المستندات';

    protected static ?string $clusterBreadcrumb = 'أنواع المستندات';
}
