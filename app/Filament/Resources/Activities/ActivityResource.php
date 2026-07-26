<?php

namespace App\Filament\Resources\Activities;

use App\Enums\Module;
use App\Filament\Resources\Activities\Pages\ListActivities;
use App\Filament\Resources\Activities\Pages\ViewActivity;
use App\Filament\Resources\Activities\Schemas\ActivityInfolist;
use App\Filament\Resources\Activities\Tables\ActivitiesTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;
use UnitEnum;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'الإدارة';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'نشاط';

    protected static ?string $pluralModelLabel = 'سجل النشاط';

    public static function infolist(Schema $schema): Schema
    {
        return ActivityInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivitiesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
            'view' => ViewActivity::route('/{record}'),
        ];
    }

    /**
     * تسمية نوع السجل المتأثر بالعملية بالعربية.
     */
    public static function subjectTypeLabel(?string $subjectType): string
    {
        if ($subjectType === null) {
            return '—';
        }

        if ($subjectType === User::class) {
            return 'مستخدم';
        }

        foreach (Module::cases() as $module) {
            if ($module->modelClass() === $subjectType) {
                return $module->getLabel();
            }
        }

        return class_basename($subjectType);
    }

    /**
     * @return array<string, string>
     */
    public static function subjectTypeOptions(): array
    {
        $options = [];

        foreach (Module::cases() as $module) {
            $options[$module->modelClass()] = $module->getLabel();
        }

        $options[User::class] = 'مستخدم';

        return $options;
    }
}
