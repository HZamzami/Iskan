<?php

namespace App\Filament\Resources\GeoDocuments\Pages;

use App\Filament\Resources\GeoDocuments\GeoDocumentResource;
use App\Models\GeoDocument;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class ViewGeoDocument extends ViewRecord
{
    protected static string $resource = GeoDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('تنزيل الملف')
                ->icon(Heroicon::ArrowDownTray)
                ->color('gray')
                ->action(fn (GeoDocument $record) => Storage::disk('local')
                    ->download($record->file_path, $record->reference_number.'.'.pathinfo($record->file_path, PATHINFO_EXTENSION))),
            EditAction::make(),
        ];
    }
}
