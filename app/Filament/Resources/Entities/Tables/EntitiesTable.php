<?php

namespace App\Filament\Resources\Entities\Tables;

use App\Filament\Resources\Concerns\LookupDeleteGuard;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EntitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('اسم الجهة')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('correspondences_count')
                    ->label('عدد المعاملات')
                    ->counts('correspondences')
                    ->sortable()
                    ->toggleable(),
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
            ->emptyStateHeading('لا توجد جهات');
    }
}
