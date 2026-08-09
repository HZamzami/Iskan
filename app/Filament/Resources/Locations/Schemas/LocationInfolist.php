<?php

namespace App\Filament\Resources\Locations\Schemas;

use App\Enums\Module;
use App\Models\Location;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class LocationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الموقع')
                    ->icon(Heroicon::MapPin)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('name')
                                ->label('اسم الموقع')
                                ->badge()
                                ->color(fn (Location $record): string => $record->getColor())
                                ->icon(fn (Location $record) => $record->getIcon())
                                ->columnSpan(1),
                            IconEntry::make('is_active')
                                ->label('نشط')
                                ->boolean()
                                ->columnSpan(1),
                            TextEntry::make('slug')
                                ->label('المعرّف')
                                ->copyable()
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('جهات الموقع')
                    ->icon(Heroicon::UserGroup)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('contractor')
                                ->label('المقاول')
                                ->placeholder('—')
                                ->columnSpan(1),
                            TextEntry::make('consultant')
                                ->label('الاستشاري')
                                ->placeholder('—')
                                ->columnSpan(1),
                            TextEntry::make('asset_manager')
                                ->label('مدير الأصول')
                                ->placeholder('—')
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('الاستخدام في الوحدات')
                    ->description('عدد السجلات المرتبطة بهذا الموقع، وعدد الأنواع ذات النطاق المخصص التي تتضمنه')
                    ->icon(Heroicon::ChartBar)
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema(
                        collect(Module::cases())
                            ->filter(fn (Module $module): bool => $module->isSiteScoped())
                            ->map(fn (Module $module) => TextEntry::make("usage_{$module->value}")
                                ->label($module->getLabel())
                                ->state(fn (Location $record): string => self::formatUsage($record, $module))
                                ->columnSpan(1))
                            ->all()
                    ),

                Section::make('ملاحظات')
                    ->icon(Heroicon::PencilSquare)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('notes')
                            ->hiddenLabel()
                            ->placeholder('لا توجد ملاحظات')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function formatUsage(Location $record, Module $module): string
    {
        $counts = $record->usageBreakdown()[$module->value] ?? ['records' => 0, 'types' => 0];

        $summary = "{$counts['records']} سجل";

        if ($counts['types'] > 0) {
            $summary .= " · {$counts['types']} نوع مخصص لهذا الموقع";
        }

        return $summary;
    }
}
