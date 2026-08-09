<?php

namespace App\Filament\Resources\RequirementGroups\Tables;

use App\Enums\PaletteColor;
use App\Filament\Resources\Concerns\LookupDeleteGuard;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class RequirementGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('color')
                    ->label('اللون')
                    ->badge()
                    ->formatStateUsing(fn ($record): string => (PaletteColor::tryFrom($record->getColor() ?? 'gray') ?? PaletteColor::Gray)->getLabel())
                    ->color(fn ($record) => $record->getColor() ?? 'gray'),
                TextColumn::make('types_count')
                    ->label('عدد الأنواع')
                    ->counts('types')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('نشطة'),
            ])
            ->recordActions([
                EditAction::make(),
                LookupDeleteGuard::action(DeleteAction::make()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    LookupDeleteGuard::bulkAction(DeleteBulkAction::make()),
                ]),
            ]);
    }
}
