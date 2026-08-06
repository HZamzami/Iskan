<?php

namespace App\Filament\Resources\FinancialFlows\Tables;

use App\Enums\Site;
use App\Models\FinancialFlow;
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

class FinancialFlowsTable
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
                TextColumn::make('title')
                    ->label('عنوان المستند')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn (FinancialFlow $record): string => $record->title),
                TextColumn::make('type')
                    ->label('نوع التدفق')
                    ->badge(),
                TextColumn::make('sites')
                    ->label('القسم / الموقع')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('period_month')
                    ->label('الشهر المالي')
                    ->date('Y/m')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' ريال')
                    ->sortable()
                    ->toggleable(),
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
                SelectFilter::make('sites')
                    ->label('القسم / الموقع')
                    ->options(Site::class)
                    ->multiple()
                    ->query(function (Builder $query, array $data): Builder {
                        $values = $data['values'] ?? [];

                        if (blank($values)) {
                            return $query;
                        }

                        return $query->where(function (Builder $q) use ($values): void {
                            foreach ($values as $value) {
                                $q->orWhereJsonContains('sites', $value);
                            }
                        });
                    }),
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
                    ->url(fn (FinancialFlow $record): string => Storage::disk('local')->temporaryUrl(
                        $record->file_path,
                        now()->addMinutes(5),
                    ))
                    ->openUrlInNewTab(),
                Action::make('download')
                    ->label('تنزيل')
                    ->icon(Heroicon::ArrowDownTray)
                    ->color('gray')
                    ->action(fn (FinancialFlow $record) => Storage::disk('local')
                        ->download($record->file_path, $record->reference_number.'.'.pathinfo($record->file_path, PATHINFO_EXTENSION))),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('لا توجد تدفقات مالية')
            ->emptyStateDescription('ابدأ بإضافة أول تدفق مالي إلى الأرشيف.')
            ->emptyStateIcon(Heroicon::OutlinedBanknotes);
    }
}
