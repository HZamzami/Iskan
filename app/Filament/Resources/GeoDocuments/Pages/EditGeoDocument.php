<?php

namespace App\Filament\Resources\GeoDocuments\Pages;

use App\Filament\Resources\GeoDocuments\GeoDocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditGeoDocument extends EditRecord
{
    protected static string $resource = GeoDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
