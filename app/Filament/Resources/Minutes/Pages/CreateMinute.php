<?php

namespace App\Filament\Resources\Minutes\Pages;

use App\Filament\Resources\Minutes\MinuteResource;
use App\Filament\Support\LinksCreatedRecordToTask;
use App\Filament\Support\SubmitsWorkflowOnCreate;
use Filament\Resources\Pages\CreateRecord;

class CreateMinute extends CreateRecord
{
    use LinksCreatedRecordToTask, SubmitsWorkflowOnCreate {
        SubmitsWorkflowOnCreate::afterCreate as submitWorkflowOnCreate;
        LinksCreatedRecordToTask::afterCreate as linkCreatedRecordToTask;
    }

    protected static string $resource = MinuteResource::class;

    protected function afterCreate(): void
    {
        $this->submitWorkflowOnCreate();
        $this->linkCreatedRecordToTask();
    }
}
