<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Task;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class ViewTask extends ViewRecord
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('معاينة المرفق')
                ->icon(Heroicon::Eye)
                ->color('gray')
                ->visible(fn (Task $record): bool => filled($record->file_path))
                ->url(fn (Task $record): string => Storage::disk('local')->temporaryUrl(
                    $record->file_path,
                    now()->addMinutes(5),
                ))
                ->openUrlInNewTab(),
            Action::make('download')
                ->label('تنزيل المرفق')
                ->icon(Heroicon::ArrowDownTray)
                ->color('gray')
                ->visible(fn (Task $record): bool => filled($record->file_path))
                ->action(fn (Task $record) => Storage::disk('local')
                    ->download($record->file_path, $record->title.'.'.pathinfo($record->file_path, PATHINFO_EXTENSION))),
            EditAction::make(),
        ];
    }
}
