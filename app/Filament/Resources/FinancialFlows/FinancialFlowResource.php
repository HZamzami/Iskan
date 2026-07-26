<?php

namespace App\Filament\Resources\FinancialFlows;

use App\Filament\Resources\Concerns\HasSiteRestrictedQuery;
use App\Filament\Resources\FinancialFlows\Pages\CreateFinancialFlow;
use App\Filament\Resources\FinancialFlows\Pages\EditFinancialFlow;
use App\Filament\Resources\FinancialFlows\Pages\ListFinancialFlows;
use App\Filament\Resources\FinancialFlows\Pages\ViewFinancialFlow;
use App\Filament\Resources\FinancialFlows\Schemas\FinancialFlowForm;
use App\Filament\Resources\FinancialFlows\Schemas\FinancialFlowInfolist;
use App\Filament\Resources\FinancialFlows\Tables\FinancialFlowsTable;
use App\Models\FinancialFlow;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FinancialFlowResource extends Resource
{
    use HasSiteRestrictedQuery;

    protected static ?string $model = FinancialFlow::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'الأرشيف';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'تدفق مالي';

    protected static ?string $pluralModelLabel = 'التدفقات المالية';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return FinancialFlowForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FinancialFlowInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinancialFlowsTable::configure($table);
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
            'index' => ListFinancialFlows::route('/'),
            'create' => CreateFinancialFlow::route('/create'),
            'view' => ViewFinancialFlow::route('/{record}'),
            'edit' => EditFinancialFlow::route('/{record}/edit'),
        ];
    }
}
