<?php

namespace App\Filament\Resources\MinuteTypes;

use App\Filament\Clusters\DocumentTypes;
use App\Filament\Resources\Concerns\DocumentTypeForm;
use App\Filament\Resources\Concerns\DocumentTypesTable;
use App\Filament\Resources\MinuteTypes\Pages\ManageMinuteTypes;
use App\Models\MinuteType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MinuteTypeResource extends Resource
{
    protected static ?string $model = MinuteType::class;

    protected static ?string $cluster = DocumentTypes::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'نوع محضر';

    protected static ?string $pluralModelLabel = 'أنواع المحاضر';

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
            'index' => ManageMinuteTypes::route('/'),
        ];
    }
}
