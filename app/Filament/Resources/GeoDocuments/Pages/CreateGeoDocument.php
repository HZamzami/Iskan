<?php

namespace App\Filament\Resources\GeoDocuments\Pages;

use App\Filament\Resources\GeoDocuments\GeoDocumentResource;
use App\Filament\Support\SubmitsWorkflowOnCreate;
use Filament\Resources\Pages\CreateRecord;

class CreateGeoDocument extends CreateRecord
{
    use SubmitsWorkflowOnCreate;

    protected static string $resource = GeoDocumentResource::class;
}
