<?php

namespace App\Filament\Resources\EntityTypes\Tables;

use App\Filament\Resources\Concerns\LookupDeleteGuard;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class EntityTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('نوع الجهة')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('slug')
                    ->label('المعرّف')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('entities_count')
                    ->label('عدد الجهات')
                    ->counts('entities')
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
            ])
            ->emptyStateHeading('لا توجد جهات مصنَّفة');
    }
}
