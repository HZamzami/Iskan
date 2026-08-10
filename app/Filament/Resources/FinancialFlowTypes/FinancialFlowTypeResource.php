<?php

namespace App\Filament\Resources\FinancialFlowTypes;

use App\Filament\Clusters\DocumentTypes;
use App\Filament\Resources\Concerns\DocumentTypeForm;
use App\Filament\Resources\Concerns\DocumentTypesTable;
use App\Filament\Resources\FinancialFlowTypes\Pages\ManageFinancialFlowTypes;
use App\Models\FinancialFlowType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FinancialFlowTypeResource extends Resource
{
    protected static ?string $model = FinancialFlowType::class;

    protected static ?string $cluster = DocumentTypes::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'نوع تدفق مالي';

    protected static ?string $pluralModelLabel = 'أنواع التدفقات المالية';

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
            'index' => ManageFinancialFlowTypes::route('/'),
        ];
    }
}
