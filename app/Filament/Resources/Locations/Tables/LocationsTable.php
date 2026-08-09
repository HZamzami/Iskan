<?php

namespace App\Filament\Resources\Locations\Tables;

use App\Filament\Resources\Concerns\LookupDeleteGuard;
use App\Filament\Resources\Locations\Actions\ApplyLocationToTypesAction;
use App\Models\Location;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class LocationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('اسم الموقع')
                    ->searchable()
                    ->weight('medium')
                    ->icon(fn (Location $record) => $record->getIcon())
                    ->iconColor(fn (Location $record) => $record->getColor()),
                TextColumn::make('contractor')
                    ->label('المقاول')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('consultant')
                    ->label('الاستشاري')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('asset_manager')
                    ->label('مدير الأصول')
                    ->placeholder('—')
                    ->toggleable(),
                IconColumn::make('is_in_use')
                    ->label('مستخدم')
                    ->state(fn (Location $record): bool => $record->isInUse())
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_active')
                    ->label('نشط'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                ApplyLocationToTypesAction::make(),
                LookupDeleteGuard::action(DeleteAction::make()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    LookupDeleteGuard::bulkAction(DeleteBulkAction::make()),
                ]),
            ])
            ->emptyStateHeading('لا توجد مواقع')
            ->emptyStateIcon(Heroicon::OutlinedMapPin);
    }
}
