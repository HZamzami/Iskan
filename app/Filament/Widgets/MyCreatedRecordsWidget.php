<?php

namespace App\Filament\Widgets;

use App\Filament\Exports\ActivityExporter;
use App\Filament\Resources\Activities\ActivityResource;
use Filament\Actions\ExportAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Spatie\Activitylog\Models\Activity;

class MyCreatedRecordsWidget extends TableWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'ما رفعته';

    public function table(Table $table): Table
    {
        return $table
            ->heading('ما رفعته')
            ->headerActions([
                ExportAction::make()
                    ->label('تصدير إلى Excel')
                    ->exporter(ActivityExporter::class),
            ])
            ->query(Activity::query()
                ->with('causer')
                ->where('causer_id', Filament::auth()->id())
                ->where('event', 'created')
                ->latest())
            ->columns([
                TextColumn::make('created_at')
                    ->label('الوقت')
                    ->since(),
                TextColumn::make('subject_type')
                    ->label('الوحدة')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (?string $state): string => ActivityResource::subjectTypeLabel($state)),
                TextColumn::make('subject_id')
                    ->label('رقم السجل'),
            ])
            ->recordUrl(fn (Activity $record): ?string => ActivityResource::subjectUrl($record->subject_type, $record->subject_id))
            ->emptyStateHeading('لم ترفع أي سجل بعد');
    }
}
