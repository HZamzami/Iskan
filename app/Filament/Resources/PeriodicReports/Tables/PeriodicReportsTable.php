<?php

namespace App\Filament\Resources\PeriodicReports\Tables;

use App\Enums\WorkflowStatus;
use App\Filament\Support\WorkflowActions;
use App\Models\Location;
use App\Models\PeriodicReport;
use App\Models\PeriodicReportType;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
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
                TextColumn::make('documentType.name')
                    ->label('نوع التقرير')
                    ->badge()
                    ->color(fn (PeriodicReport $record): string => $record->documentType?->color ?? 'gray'),
                TextColumn::make('sites')
                    ->label('القسم / الموقع')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Location::cached()->firstWhere('slug', $state)?->name ?? $state)
                    ->color(fn (string $state): string => Location::cached()->firstWhere('slug', $state)?->color ?? 'gray')
                    ->placeholder('—'),
                TextColumn::make('period')
                    ->label('فترة التقرير')
                    ->date('Y/m/d')
                    ->sortable(),
                TextColumn::make('document_date')
                    ->label('تاريخ الملف')
                    ->date('Y/m/d')
                    ->sortable(),
                TextColumn::make('workflow_status')
                    ->label('حالة الاعتماد')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('assignee.name')
                    ->label('بانتظار')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الرفع')
                    ->date('Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('document_date', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('نوع التقرير')
                    ->options(fn (): array => PeriodicReportType::active()->ordered()->pluck('name', 'slug')->all())
                    ->searchable(),
                SelectFilter::make('workflow_status')
                    ->label('حالة الاعتماد')
                    ->options(WorkflowStatus::class),
                TernaryFilter::make('assigned_to_me')
                    ->label('بانتظار إجرائي')
                    ->placeholder('الجميع')
                    ->trueLabel('بانتظار إجرائي فقط')
                    ->falseLabel('غير ذلك')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->where('assigned_to', Filament::auth()->id()),
                        false: fn (Builder $query): Builder => $query->where(fn (Builder $q) => $q->whereNull('assigned_to')->orWhere('assigned_to', '!=', Filament::auth()->id())),
                    ),
                SelectFilter::make('sites')
                    ->label('القسم / الموقع')
                    ->options(fn (): array => Location::active()->ordered()->pluck('name', 'slug')->all())
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
                    ->url(fn (PeriodicReport $record): string => Storage::disk('local')->temporaryUrl(
                        $record->file_path,
                        now()->addMinutes(5),
                    ))
                    ->openUrlInNewTab(),
                Action::make('download')
                    ->label('تنزيل')
                    ->icon(Heroicon::ArrowDownTray)
                    ->color('gray')
                    ->action(fn (PeriodicReport $record) => Storage::disk('local')
                        ->download($record->file_path, $record->reference_number.'.'.pathinfo($record->file_path, PATHINFO_EXTENSION))),
                ViewAction::make(),
                EditAction::make(),
                ActionGroup::make(WorkflowActions::forRecord())
                    ->label('إجراء الاعتماد')
                    ->icon(Heroicon::CheckCircle)
                    ->color('warning')
                    ->visible(fn (PeriodicReport $record): bool => $record->workflow_status === WorkflowStatus::Pending),
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
