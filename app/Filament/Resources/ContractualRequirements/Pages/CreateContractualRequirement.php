<?php

namespace App\Filament\Resources\ContractualRequirements\Pages;

use App\Filament\Resources\ContractualRequirements\ContractualRequirementResource;
use App\Filament\Support\LinksCreatedRecordToTask;
use App\Filament\Support\SubmitsWorkflowOnCreate;
use Filament\Resources\Pages\CreateRecord;

class CreateContractualRequirement extends CreateRecord
{
    use LinksCreatedRecordToTask, SubmitsWorkflowOnCreate {
        SubmitsWorkflowOnCreate::afterCreate as submitWorkflowOnCreate;
        LinksCreatedRecordToTask::afterCreate as linkCreatedRecordToTask;
    }

    protected static string $resource = ContractualRequirementResource::class;

    protected function afterCreate(): void
    {
        $this->submitWorkflowOnCreate();
        $this->linkCreatedRecordToTask();
    }
}
