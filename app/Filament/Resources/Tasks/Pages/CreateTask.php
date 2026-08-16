<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Tasks\Schemas\TaskForm;
use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Task;
use App\Services\TaskNotifier;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    public function form(Schema $schema): Schema
    {
        return TaskForm::configure($schema);
    }

    protected function afterCreate(): void
    {
        /** @var Task $task */
        $task = $this->record;

        app(TaskNotifier::class)->notifyAssignment($task);
    }
}
