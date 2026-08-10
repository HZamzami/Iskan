<?php

namespace App\Filament\Resources\ContractualRequirementTypes;

use App\Filament\Clusters\DocumentTypes;
use App\Filament\Resources\Concerns\DocumentTypeForm;
use App\Filament\Resources\ContractualRequirementTypes\Pages\ManageContractualRequirementTypes;
use App\Filament\Resources\ContractualRequirementTypes\Tables\ContractualRequirementTypesTable;
use App\Models\ContractualRequirementType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContractualRequirementTypeResource extends Resource
{
    protected static ?string $model = ContractualRequirementType::class;

    protected static ?string $cluster = DocumentTypes::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'نوع متطلب تعاقدي';

    protected static ?string $pluralModelLabel = 'أنواع المتطلبات التعاقدية';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return DocumentTypeForm::configure($schema, static::getModel());
    }

    public static function table(Table $table): Table
    {
        return ContractualRequirementTypesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageContractualRequirementTypes::route('/'),
        ];
    }
}
