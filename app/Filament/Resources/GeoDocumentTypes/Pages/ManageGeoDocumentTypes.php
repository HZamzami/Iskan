<?php

namespace App\Filament\Resources\GeoDocumentTypes\Pages;

use App\Filament\Resources\GeoDocumentTypes\GeoDocumentTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageGeoDocumentTypes extends ManageRecords
{
    protected static string $resource = GeoDocumentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
