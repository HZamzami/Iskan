<?php

namespace App\Filament\Resources\Tasks\Concerns;

use App\Filament\Resources\Tasks\Schemas\TaskForm;
use App\Models\Role;
use App\Models\Task;
use App\Services\TaskNotifier;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

trait CreatesTaskForRole
{
    abstract protected static function targetRoleSlug(): string;

    public function form(Schema $schema): Schema
    {
        return TaskForm::configure($schema, static::targetRoleSlug());
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['assigned_role_id'] = Role::where('slug', static::targetRoleSlug())->value('id');
        $data['requested_by'] = Auth::id();

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var Task $task */
        $task = $this->record;

        app(TaskNotifier::class)->notifyAssignment($task);
    }
}
