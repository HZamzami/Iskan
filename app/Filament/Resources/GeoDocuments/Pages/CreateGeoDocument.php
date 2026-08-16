<?php

namespace App\Filament\Resources\GeoDocuments\Pages;

use App\Filament\Resources\GeoDocuments\GeoDocumentResource;
use App\Filament\Support\LinksCreatedRecordToTask;
use App\Filament\Support\SubmitsWorkflowOnCreate;
use Filament\Resources\Pages\CreateRecord;

class CreateGeoDocument extends CreateRecord
{
    use LinksCreatedRecordToTask, SubmitsWorkflowOnCreate {
        SubmitsWorkflowOnCreate::afterCreate as submitWorkflowOnCreate;
        LinksCreatedRecordToTask::afterCreate as linkCreatedRecordToTask;
    }

    protected static string $resource = GeoDocumentResource::class;

    protected function afterCreate(): void
    {
        $this->submitWorkflowOnCreate();
        $this->linkCreatedRecordToTask();
    }
}
