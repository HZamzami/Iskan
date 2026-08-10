<?php

namespace App\Filament\Resources\PeriodicReports\Pages;

use App\Filament\Resources\PeriodicReports\PeriodicReportResource;
use App\Filament\Support\WorkflowActions;
use App\Models\PeriodicReport;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class ViewPeriodicReport extends ViewRecord
{
    protected static string $resource = PeriodicReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('معاينة الملف')
                ->icon(Heroicon::Eye)
                ->color('gray')
                ->url(fn (PeriodicReport $record): string => Storage::disk('local')->temporaryUrl(
                    $record->file_path,
                    now()->addMinutes(5),
                ))
                ->openUrlInNewTab(),
            Action::make('download')
                ->label('تنزيل الملف')
                ->icon(Heroicon::ArrowDownTray)
                ->color('gray')
                ->action(fn (PeriodicReport $record) => Storage::disk('local')
                    ->download($record->file_path, $record->reference_number.'.'.pathinfo($record->file_path, PATHINFO_EXTENSION))),
            ...WorkflowActions::forRecord(),
            EditAction::make(),
        ];
    }
}
