<?php

namespace App\Filament\Resources\GeoDocuments\Pages;

use App\Filament\Resources\GeoDocuments\GeoDocumentResource;
use App\Filament\Support\FileActions;
use App\Filament\Support\WorkflowActions;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGeoDocument extends ViewRecord
{
    protected static string $resource = GeoDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            FileActions::preview(),
            FileActions::download(),
            ...WorkflowActions::forRecord(),
            EditAction::make(),
        ];
    }
}
