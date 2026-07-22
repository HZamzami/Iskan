<?php

namespace App\Filament\Resources\PeriodicReports\Tables;

use App\Enums\Site;
use App\Models\PeriodicReport;
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
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class PeriodicReportsTable
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
                    ->tooltip(fn (PeriodicReport $record): string => $record->title),
                TextColumn::make('type')
                    ->label('نوع التقرير')
                    ->badge(),
                TextColumn::make('site')
                    ->label('القسم / الموقع')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('period')
                    ->label('فترة التقرير')
                    ->date('Y/m/d')
                    ->sortable(),
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
            ->groups([
                Group::make('site')
                    ->label('الموقع')
                    ->getTitleFromRecordUsing(fn (PeriodicReport $record): string => $record->site?->getLabel() ?? 'غير مرتبط بموقع'),
            ])
            ->filters([
                SelectFilter::make('site')
                    ->label('القسم / الموقع')
                    ->options(Site::class),
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
                Action::make('download')
                    ->label('تنزيل')
                    ->icon(Heroicon::ArrowDownTray)
                    ->color('gray')
                    ->action(fn (PeriodicReport $record) => Storage::disk('local')
                        ->download($record->file_path, $record->reference_number.'.pdf')),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('لا توجد تقارير')
            ->emptyStateDescription('ابدأ بإضافة أول تقرير دوري إلى الأرشيف.')
            ->emptyStateIcon(Heroicon::OutlinedChartBar);
    }
}
