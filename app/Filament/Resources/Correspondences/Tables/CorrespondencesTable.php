<?php

namespace App\Filament\Resources\Correspondences\Tables;

use App\Enums\CorrespondenceStatus;
use App\Models\Correspondence;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class CorrespondencesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->label('الرقم المرجعي')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight(FontWeight::Bold),
                TextColumn::make('subject')
                    ->label('الموضوع')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn (Correspondence $record): string => $record->subject),
                TextColumn::make('direction')
                    ->label('النوع')
                    ->badge(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
                TextColumn::make('entity.name')
                    ->label('الجهة')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('sender')
                    ->label('من')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('recipient')
                    ->label('إلى')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('document_date')
                    ->label('تاريخ الملف')
                    ->date('Y/m/d')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الرفع')
                    ->date('Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('document_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(CorrespondenceStatus::class)
                    ->multiple(),
                SelectFilter::make('entity')
                    ->label('الجهة')
                    ->relationship('entity', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('document_date')
                    ->label('تاريخ الملف')
                    ->schema([
                        DatePicker::make('from')->label('من تاريخ'),
                        DatePicker::make('until')->label('إلى تاريخ'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('document_date', '>=', $date))
                        ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('document_date', '<=', $date))),
            ])
            ->recordActions([
                Action::make('preview')
                    ->label('معاينة')
                    ->icon(Heroicon::Eye)
                    ->color('gray')
                    ->url(fn (Correspondence $record): string => Storage::disk('local')->temporaryUrl(
                        $record->file_path,
                        now()->addMinutes(5),
                    ))
                    ->openUrlInNewTab(),
                Action::make('download')
                    ->label('تنزيل')
                    ->icon(Heroicon::ArrowDownTray)
                    ->color('gray')
                    ->action(fn (Correspondence $record) => Storage::disk('local')
                        ->download($record->file_path, $record->reference_number.'.'.pathinfo($record->file_path, PATHINFO_EXTENSION))),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('لا توجد معاملات')
            ->emptyStateDescription('ابدأ بإضافة أول معاملة إدارية إلى الأرشيف.')
            ->emptyStateIcon(Heroicon::OutlinedEnvelope);
    }
}
