<?php

namespace App\Filament\Resources\FinancialFlows\Pages;

use App\Filament\Resources\FinancialFlows\FinancialFlowResource;
use App\Filament\Support\LinksCreatedRecordToTask;
use App\Filament\Support\SubmitsWorkflowOnCreate;
use Filament\Resources\Pages\CreateRecord;

class CreateFinancialFlow extends CreateRecord
{
    use LinksCreatedRecordToTask, SubmitsWorkflowOnCreate {
        SubmitsWorkflowOnCreate::afterCreate as submitWorkflowOnCreate;
        LinksCreatedRecordToTask::afterCreate as linkCreatedRecordToTask;
    }

    protected static string $resource = FinancialFlowResource::class;

    protected function afterCreate(): void
    {
        $this->submitWorkflowOnCreate();
        $this->linkCreatedRecordToTask();
    }
}
