<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class TaskRecurrenceGenerator
{
    public function run(): int
    {
        $generated = 0;

        Task::query()
            ->where('is_template', true)
            ->where('is_active', true)
            ->whereDate('next_run_date', '<=', today())
            ->chunkById(50, function ($templates) use (&$generated): void {
                foreach ($templates as $template) {
                    $instance = DB::transaction(function () use ($template): Task {
                        $instance = Task::create([
                            'title' => $template->title,
                            'description' => $template->description,
                            'due_date' => $template->next_run_date,
                            'assigned_to' => $template->assigned_to,
                            'assigned_role_id' => $template->assigned_role_id,
                            'requested_by' => $template->requested_by,
                            'priority' => $template->priority,
                            'status' => TaskStatus::Pending,
                            'recurrence' => $template->recurrence,
                            'is_template' => false,
                            'parent_task_id' => $template->id,
                            'file_path' => $template->file_path,
                            'notify_by_email' => $template->notify_by_email,
                        ]);

                        $template->update([
                            'next_run_date' => $template->nextOccurrenceDate($template->next_run_date),
                        ]);

                        return $instance;
                    });

                    app(TaskNotifier::class)->notifyAssignment($instance);
                    $generated++;
                }
            });

        return $generated;
    }
}
