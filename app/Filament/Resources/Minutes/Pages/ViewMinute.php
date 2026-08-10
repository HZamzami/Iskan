<?php

namespace App\Filament\Resources\Minutes\Pages;

use App\Filament\Resources\Minutes\MinuteResource;
use App\Filament\Support\WorkflowActions;
use App\Models\Minute;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class ViewMinute extends ViewRecord
{
    protected static string $resource = MinuteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('معاينة الملف')
                ->icon(Heroicon::Eye)
                ->color('gray')
                ->url(fn (Minute $record): string => Storage::disk('local')->temporaryUrl(
                    $record->file_path,
                    now()->addMinutes(5),
                ))
                ->openUrlInNewTab(),
            Action::make('download')
                ->label('تنزيل الملف')
                ->icon(Heroicon::ArrowDownTray)
                ->color('gray')
                ->action(fn (Minute $record) => Storage::disk('local')
                    ->download($record->file_path, $record->reference_number.'.'.pathinfo($record->file_path, PATHINFO_EXTENSION))),
            ...WorkflowActions::forRecord(),
            EditAction::make(),
        ];
    }
}
