<?php

namespace App\Filament\Resources\EntityTypes;

use App\Filament\Resources\EntityTypes\Pages\ManageEntityTypes;
use App\Filament\Resources\EntityTypes\Schemas\EntityTypeForm;
use App\Filament\Resources\EntityTypes\Tables\EntityTypesTable;
use App\Models\EntityType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EntityTypeResource extends Resource
{
    protected static ?string $model = EntityType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'الإدارة';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'نوع جهة';

    protected static ?string $pluralModelLabel = 'أنواع الجهات';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return EntityTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EntityTypesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageEntityTypes::route('/'),
        ];
    }
}
