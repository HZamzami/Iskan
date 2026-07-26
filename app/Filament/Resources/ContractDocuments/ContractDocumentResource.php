<?php

namespace App\Filament\Resources\ContractDocuments;

use App\Filament\Resources\Concerns\HasSiteRestrictedQuery;
use App\Filament\Resources\ContractDocuments\Pages\CreateContractDocument;
use App\Filament\Resources\ContractDocuments\Pages\EditContractDocument;
use App\Filament\Resources\ContractDocuments\Pages\ListContractDocuments;
use App\Filament\Resources\ContractDocuments\Pages\ViewContractDocument;
use App\Filament\Resources\ContractDocuments\Schemas\ContractDocumentForm;
use App\Filament\Resources\ContractDocuments\Schemas\ContractDocumentInfolist;
use App\Filament\Resources\ContractDocuments\Tables\ContractDocumentsTable;
use App\Models\ContractDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ContractDocumentResource extends Resource
{
    use HasSiteRestrictedQuery;

    protected static ?string $model = ContractDocument::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static string|UnitEnum|null $navigationGroup = 'الأرشيف';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'مستند تعاقدي';

    protected static ?string $pluralModelLabel = 'المستندات التعاقدية';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return ContractDocumentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ContractDocumentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContractDocumentsTable::configure($table);
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
            'index' => ListContractDocuments::route('/'),
            'create' => CreateContractDocument::route('/create'),
            'view' => ViewContractDocument::route('/{record}'),
            'edit' => EditContractDocument::route('/{record}/edit'),
        ];
    }
}
