<?php

namespace App\Filament\Resources\ContractualRequirements;

use App\Filament\Resources\Concerns\HasSiteRestrictedQuery;
use App\Filament\Resources\ContractualRequirements\Pages\CreateContractualRequirement;
use App\Filament\Resources\ContractualRequirements\Pages\EditContractualRequirement;
use App\Filament\Resources\ContractualRequirements\Pages\ListContractualRequirements;
use App\Filament\Resources\ContractualRequirements\Pages\ViewContractualRequirement;
use App\Filament\Resources\ContractualRequirements\Schemas\ContractualRequirementForm;
use App\Filament\Resources\ContractualRequirements\Schemas\ContractualRequirementInfolist;
use App\Filament\Resources\ContractualRequirements\Tables\ContractualRequirementsTable;
use App\Filament\Resources\Tasks\RelationManagers\CommentsRelationManager;
use App\Models\ContractualRequirement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ContractualRequirementResource extends Resource
{
    use HasSiteRestrictedQuery;

    protected static ?string $model = ContractualRequirement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'الأرشيف';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'متطلب تعاقدي';

    protected static ?string $pluralModelLabel = 'المتطلبات التعاقدية';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return ContractualRequirementForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ContractualRequirementInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContractualRequirementsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContractualRequirements::route('/'),
            'create' => CreateContractualRequirement::route('/create'),
            'view' => ViewContractualRequirement::route('/{record}'),
            'edit' => EditContractualRequirement::route('/{record}/edit'),
        ];
    }
}
