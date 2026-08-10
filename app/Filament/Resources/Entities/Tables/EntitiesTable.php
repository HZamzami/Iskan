<?php

namespace App\Filament\Resources\Entities\Tables;

use App\Filament\Resources\Concerns\LookupDeleteGuard;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
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
                TextColumn::make('entityType.name')
                    ->label('نوع الجهة')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),
                TextColumn::make('users_count')
                    ->label('عدد المستخدمين')
                    ->counts('users')
                    ->sortable(),
                TextColumn::make('correspondences_count')
                    ->label('عدد المعاملات')
                    ->counts('correspondences')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('entity_type_id')
                    ->label('نوع الجهة')
                    ->relationship('entityType', 'name'),
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
