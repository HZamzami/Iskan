<?php

namespace App\Filament\Resources\RequirementGroups;

use App\Filament\Clusters\DocumentTypes;
use App\Filament\Resources\RequirementGroups\Pages\ManageRequirementGroups;
use App\Filament\Resources\RequirementGroups\Schemas\RequirementGroupForm;
use App\Filament\Resources\RequirementGroups\Tables\RequirementGroupsTable;
use App\Models\RequirementGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RequirementGroupResource extends Resource
{
    protected static ?string $model = RequirementGroup::class;

    protected static ?string $cluster = DocumentTypes::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static ?string $modelLabel = 'مجموعة متطلبات';

    protected static ?string $pluralModelLabel = 'مجموعات المتطلبات';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return RequirementGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RequirementGroupsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRequirementGroups::route('/'),
        ];
    }
}
