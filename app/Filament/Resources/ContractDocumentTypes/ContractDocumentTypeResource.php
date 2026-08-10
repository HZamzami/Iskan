<?php

namespace App\Filament\Resources\ContractDocumentTypes;

use App\Filament\Clusters\DocumentTypes;
use App\Filament\Resources\Concerns\DocumentTypeForm;
use App\Filament\Resources\Concerns\DocumentTypesTable;
use App\Filament\Resources\ContractDocumentTypes\Pages\ManageContractDocumentTypes;
use App\Models\ContractDocumentType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContractDocumentTypeResource extends Resource
{
    protected static ?string $model = ContractDocumentType::class;

    protected static ?string $cluster = DocumentTypes::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'نوع مستند تعاقدي';

    protected static ?string $pluralModelLabel = 'أنواع المستندات التعاقدية';

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
            'index' => ManageContractDocumentTypes::route('/'),
        ];
    }
}
