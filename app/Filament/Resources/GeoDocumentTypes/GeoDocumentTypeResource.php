<?php

namespace App\Filament\Resources\GeoDocumentTypes;

use App\Filament\Clusters\DocumentTypes;
use App\Filament\Resources\Concerns\DocumentTypeForm;
use App\Filament\Resources\Concerns\DocumentTypesTable;
use App\Filament\Resources\GeoDocumentTypes\Pages\ManageGeoDocumentTypes;
use App\Models\GeoDocumentType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GeoDocumentTypeResource extends Resource
{
    protected static ?string $model = GeoDocumentType::class;

    protected static ?string $cluster = DocumentTypes::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static ?string $modelLabel = 'نوع خريطة / رسم جيومكاني';

    protected static ?string $pluralModelLabel = 'أنواع الخرائط والرسومات الجيومكانية';

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
            'index' => ManageGeoDocumentTypes::route('/'),
        ];
    }
}
