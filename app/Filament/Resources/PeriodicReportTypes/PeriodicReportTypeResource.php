<?php

namespace App\Filament\Resources\PeriodicReportTypes;

use App\Filament\Clusters\DocumentTypes;
use App\Filament\Resources\Concerns\DocumentTypeForm;
use App\Filament\Resources\Concerns\DocumentTypesTable;
use App\Filament\Resources\PeriodicReportTypes\Pages\ManagePeriodicReportTypes;
use App\Models\PeriodicReportType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PeriodicReportTypeResource extends Resource
{
    protected static ?string $model = PeriodicReportType::class;

    protected static ?string $cluster = DocumentTypes::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $modelLabel = 'نوع تقرير دوري';

    protected static ?string $pluralModelLabel = 'أنواع التقارير الدورية';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return DocumentTypeForm::configure($schema, static::getModel());
    }

    public static function table(Table $table): Table
    {
        return DocumentTypesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePeriodicReportTypes::route('/'),
        ];
    }
}
