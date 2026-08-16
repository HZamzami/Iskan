<?php

namespace App\Filament\Resources\Correspondences;

use App\Filament\Resources\Correspondences\Pages\CreateCorrespondence;
use App\Filament\Resources\Correspondences\Pages\EditCorrespondence;
use App\Filament\Resources\Correspondences\Pages\ListCorrespondences;
use App\Filament\Resources\Correspondences\Pages\ViewCorrespondence;
use App\Filament\Resources\Correspondences\Schemas\CorrespondenceForm;
use App\Filament\Resources\Correspondences\Schemas\CorrespondenceInfolist;
use App\Filament\Resources\Correspondences\Tables\CorrespondencesTable;
use App\Filament\Resources\Tasks\RelationManagers\CommentsRelationManager;
use App\Models\Correspondence;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class CorrespondenceResource extends Resource
{
    protected static ?string $model = Correspondence::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|UnitEnum|null $navigationGroup = 'الأرشيف';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'معاملة إدارية';

    protected static ?string $pluralModelLabel = 'المعاملات الإدارية';

    protected static ?string $recordTitleAttribute = 'subject';

    public static function form(Schema $schema): Schema
    {
        return CorrespondenceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CorrespondenceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CorrespondencesTable::configure($table);
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
            'index' => ListCorrespondences::route('/'),
            'create' => CreateCorrespondence::route('/create'),
            'view' => ViewCorrespondence::route('/{record}'),
            'edit' => EditCorrespondence::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['reference_number', 'subject'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return "{$record->reference_number} — {$record->subject}";
    }
}
