<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Role;
use App\Models\Task;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return Role::active()->ordered()->get()
            ->map(fn (Role $role): Action => Action::make("create-{$role->slug}")
                ->label(Task::requestTypeLabelFor($role))
                ->icon(Heroicon::Plus)
                ->color('gray')
                ->url(TaskResource::getUrl('create', ['role' => $role->slug])))
            ->all();
    }
}
