<?php

namespace App\Filament\Resources\GeoDocuments;

use App\Filament\Resources\GeoDocuments\Pages\CreateGeoDocument;
use App\Filament\Resources\GeoDocuments\Pages\EditGeoDocument;
use App\Filament\Resources\GeoDocuments\Pages\ListGeoDocuments;
use App\Filament\Resources\GeoDocuments\Pages\ViewGeoDocument;
use App\Filament\Resources\GeoDocuments\Schemas\GeoDocumentForm;
use App\Filament\Resources\GeoDocuments\Schemas\GeoDocumentInfolist;
use App\Filament\Resources\GeoDocuments\Tables\GeoDocumentsTable;
use App\Models\GeoDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class GeoDocumentResource extends Resource
{
    protected static ?string $model = GeoDocument::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static string|UnitEnum|null $navigationGroup = 'الأرشيف';

    protected static ?int $navigationSort = 7;

    protected static ?string $modelLabel = 'خريطة / رسم جيومكاني';

    protected static ?string $pluralModelLabel = 'الخرائط والرسومات الجيومكانية';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return GeoDocumentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GeoDocumentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GeoDocumentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGeoDocuments::route('/'),
            'create' => CreateGeoDocument::route('/create'),
            'view' => ViewGeoDocument::route('/{record}'),
            'edit' => EditGeoDocument::route('/{record}/edit'),
        ];
    }
}
