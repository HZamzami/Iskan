<?php

namespace App\Filament\Resources\Minutes;

use App\Filament\Resources\Concerns\HasSiteRestrictedQuery;
use App\Filament\Resources\Minutes\Pages\CreateMinute;
use App\Filament\Resources\Minutes\Pages\EditMinute;
use App\Filament\Resources\Minutes\Pages\ListMinutes;
use App\Filament\Resources\Minutes\Pages\ViewMinute;
use App\Filament\Resources\Minutes\Schemas\MinuteForm;
use App\Filament\Resources\Minutes\Schemas\MinuteInfolist;
use App\Filament\Resources\Minutes\Tables\MinutesTable;
use App\Models\Minute;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MinuteResource extends Resource
{
    use HasSiteRestrictedQuery {
        HasSiteRestrictedQuery::getEloquentQuery as getSiteRestrictedQuery;
    }

    protected static ?string $model = Minute::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'الأرشيف';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'محضر';

    protected static ?string $pluralModelLabel = 'المحاضر';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getEloquentQuery(): Builder
    {
        return static::getSiteRestrictedQuery()->with('participants');
    }

    public static function form(Schema $schema): Schema
    {
        return MinuteForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MinuteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MinutesTable::configure($table);
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
            'index' => ListMinutes::route('/'),
            'create' => CreateMinute::route('/create'),
            'view' => ViewMinute::route('/{record}'),
            'edit' => EditMinute::route('/{record}/edit'),
        ];
    }
}
