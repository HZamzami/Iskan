<?php

namespace App\Services;

use App\Models\Task;
use App\Notifications\TaskAssignedNotification;

class TaskNotifier
{
    public function notifyAssignment(Task $task): void
    {
        $task->assignee?->notify(new TaskAssignedNotification($task));
    }
}
