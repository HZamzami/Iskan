<?php

namespace App\Filament\Resources\ContractDocuments\Pages;

use App\Filament\Resources\ContractDocuments\ContractDocumentResource;
use App\Filament\Support\LinksCreatedRecordToTask;
use App\Filament\Support\SubmitsWorkflowOnCreate;
use Filament\Resources\Pages\CreateRecord;

class CreateContractDocument extends CreateRecord
{
    use LinksCreatedRecordToTask, SubmitsWorkflowOnCreate {
        SubmitsWorkflowOnCreate::afterCreate as submitWorkflowOnCreate;
        LinksCreatedRecordToTask::afterCreate as linkCreatedRecordToTask;
    }

    protected static string $resource = ContractDocumentResource::class;

    protected function afterCreate(): void
    {
        $this->submitWorkflowOnCreate();
        $this->linkCreatedRecordToTask();
    }
}
