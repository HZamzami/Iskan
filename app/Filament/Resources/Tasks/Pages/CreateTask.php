<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Tasks\Schemas\TaskForm;
use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Role;
use App\Models\Task;
use App\Services\TaskNotifier;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    public ?string $targetRoleSlug = null;

    public function mount(): void
    {
        $resolvedRole = Role::query()
            ->where('slug', request()->query('role'))
            ->where('is_active', true)
            ->first();

        abort_if($resolvedRole === null, 404);

        $this->targetRoleSlug = $resolvedRole->slug;

        parent::mount();
    }

    public function getTitle(): string
    {
        return Task::requestTypeLabelFor($this->resolveTargetRole());
    }

    public function form(Schema $schema): Schema
    {
        return TaskForm::configure($schema, $this->targetRoleSlug);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['assigned_role_id'] = $this->resolveTargetRole()?->id;
        $data['requested_by'] = Auth::id();

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var Task $task */
        $task = $this->record;

        app(TaskNotifier::class)->notifyAssignment($task);
    }

    private function resolveTargetRole(): ?Role
    {
        return Role::query()->where('slug', $this->targetRoleSlug)->first();
    }
}
