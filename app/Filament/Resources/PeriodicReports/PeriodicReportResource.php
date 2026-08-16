<?php

namespace App\Filament\Resources\PeriodicReports;

use App\Filament\Resources\Concerns\HasSiteRestrictedQuery;
use App\Filament\Resources\PeriodicReports\Pages\CreatePeriodicReport;
use App\Filament\Resources\PeriodicReports\Pages\EditPeriodicReport;
use App\Filament\Resources\PeriodicReports\Pages\ListPeriodicReports;
use App\Filament\Resources\PeriodicReports\Pages\ViewPeriodicReport;
use App\Filament\Resources\PeriodicReports\Schemas\PeriodicReportForm;
use App\Filament\Resources\PeriodicReports\Schemas\PeriodicReportInfolist;
use App\Filament\Resources\PeriodicReports\Tables\PeriodicReportsTable;
use App\Filament\Resources\Tasks\RelationManagers\CommentsRelationManager;
use App\Models\PeriodicReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class PeriodicReportResource extends Resource
{
    use HasSiteRestrictedQuery;

    protected static ?string $model = PeriodicReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'الأرشيف';

    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'تقرير دوري';

    protected static ?string $pluralModelLabel = 'التقارير الدورية';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return PeriodicReportForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PeriodicReportInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PeriodicReportsTable::configure($table);
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
            'index' => ListPeriodicReports::route('/'),
            'create' => CreatePeriodicReport::route('/create'),
            'view' => ViewPeriodicReport::route('/{record}'),
            'edit' => EditPeriodicReport::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['reference_number', 'title'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return "{$record->reference_number} — {$record->title}";
    }
}
