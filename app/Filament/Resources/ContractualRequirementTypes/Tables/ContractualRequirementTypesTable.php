<?php

namespace App\Filament\Resources\ContractualRequirementTypes\Tables;

use App\Enums\PaletteColor;
use App\Filament\Resources\Concerns\LookupDeleteGuard;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ContractualRequirementTypesTable
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
                TextColumn::make('requirementGroup.name')
                    ->label('المجموعة')
                    ->badge()
                    ->color(fn ($record) => $record->requirementGroup?->color ?? 'gray'),
                TextColumn::make('color')
                    ->label('اللون')
                    ->badge()
                    ->formatStateUsing(fn ($record): string => (PaletteColor::tryFrom($record->getColor() ?? 'gray') ?? PaletteColor::Gray)->getLabel())
                    ->color(fn ($record) => $record->getColor() ?? 'gray'),
                TextColumn::make('site_scope')
                    ->label('نطاق المواقع')
                    ->badge(),
                TextColumn::make('documents_count')
                    ->label('عدد السجلات')
                    ->counts('documents')
                    ->sortable(),
                IconColumn::make('requires_workflow')
                    ->label('سير اعتماد')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_active')
                    ->label('نشط'),
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
