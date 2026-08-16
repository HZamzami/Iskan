<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Tasks\Schemas\TaskForm;
use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Task;
use App\Services\TaskNotifier;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    public function form(Schema $schema): Schema
    {
        return TaskForm::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        /** @var Task $task */
        $task = $this->record;

        if ($task->wasChanged('assigned_to')) {
            app(TaskNotifier::class)->notifyAssignment($task);
        }
    }
}
