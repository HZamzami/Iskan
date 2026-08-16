<?php

namespace App\Filament\Resources\Activities\Tables;

use App\Filament\Exports\ActivityExporter;
use App\Filament\Resources\Activities\ActivityResource;
use App\Models\User;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
                ExportAction::make()
                    ->label('تصدير إلى Excel')
                    ->exporter(ActivityExporter::class),
            ])
            ->columns([
                TextColumn::make('created_at')
                    ->label('التاريخ والوقت')
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),
                TextColumn::make('causer.name')
                    ->label('المستخدم')
                    ->placeholder('النظام'),
                TextColumn::make('event')
                    ->label('العملية')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'created' => 'إنشاء',
                        'updated' => 'تعديل',
                        'deleted' => 'حذف',
                        default => $state ?? '—',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('subject_type')
                    ->label('الوحدة')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (?string $state): string => ActivityResource::subjectTypeLabel($state)),
                TextColumn::make('subject_id')
                    ->label('رقم السجل')
                    ->formatStateUsing(fn (Activity $record): string => ActivityResource::subjectLabel($record->subject_type, $record->subject_id))
                    ->url(fn (Activity $record): ?string => ActivityResource::subjectUrl($record->subject_type, $record->subject_id))
                    ->openUrlInNewTab()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('causer_id')
                    ->label('المستخدم')
                    ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->all()),
                SelectFilter::make('event')
                    ->label('العملية')
                    ->options([
                        'created' => 'إنشاء',
                        'updated' => 'تعديل',
                        'deleted' => 'حذف',
                    ]),
                SelectFilter::make('subject_type')
                    ->label('الوحدة')
                    ->options(ActivityResource::subjectTypeOptions()),
                Filter::make('created_at')
                    ->label('التاريخ')
                    ->schema([
                        DatePicker::make('from')->label('من تاريخ'),
                        DatePicker::make('until')->label('إلى تاريخ'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date))),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->emptyStateHeading('لا يوجد نشاط مسجل')
            ->emptyStateIcon(Heroicon::OutlinedClipboardDocumentList);
    }
}
