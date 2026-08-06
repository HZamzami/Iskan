<?php

namespace App\Filament\Resources\Correspondences\Pages;

use App\Filament\Resources\Correspondences\CorrespondenceResource;
use App\Models\Correspondence;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class ViewCorrespondence extends ViewRecord
{
    protected static string $resource = CorrespondenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('معاينة الملف')
                ->icon(Heroicon::Eye)
                ->color('gray')
                ->url(fn (Correspondence $record): string => Storage::disk('local')->temporaryUrl(
                    $record->file_path,
                    now()->addMinutes(5),
                ))
                ->openUrlInNewTab(),
            Action::make('download')
                ->label('تنزيل الملف')
                ->icon(Heroicon::ArrowDownTray)
                ->color('gray')
                ->action(fn (Correspondence $record) => Storage::disk('local')
                    ->download($record->file_path, $record->reference_number.'.'.pathinfo($record->file_path, PATHINFO_EXTENSION))),
            EditAction::make(),
        ];
    }
}
