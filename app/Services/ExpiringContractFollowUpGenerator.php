<?php

namespace App\Services;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\ContractDocument;
use App\Models\Task;

class ExpiringContractFollowUpGenerator
{
    /**
     * ينشئ مهمة "تجديد العقد" لكل عقد سارٍ ينتهي خلال ٣٠ يوماً، طالما لديه
     * مكلَّف ولا توجد له مهمة مفتوحة مرتبطة به مسبقاً. العقود بلا مكلَّف
     * تُستثنى لعدم وجود جهة واضحة تُسنَد إليها المهمة.
     */
    public function run(): int
    {
        $generated = 0;

        ContractDocument::query()
            ->whereNotNull('assigned_to')
            ->whereDate('end_date', '>=', today())
            ->whereDate('end_date', '<=', today()->addDays(30))
            ->chunkById(50, function ($contracts) use (&$generated): void {
                foreach ($contracts as $contract) {
                    if ($this->hasOpenFollowUpTask($contract)) {
                        continue;
                    }

                    $task = Task::create([
                        'title' => "تجديد العقد: {$contract->reference_number}",
                        'due_date' => $contract->end_date,
                        'assigned_to' => $contract->assigned_to,
                        'assigned_role_id' => $contract->assigned_role_id,
                        'priority' => TaskPriority::Urgent,
                        'status' => TaskStatus::Pending,
                    ]);

                    $task->linkable()->associate($contract);
                    $task->save();

                    app(TaskNotifier::class)->notifyAssignment($task);
                    $generated++;
                }
            });

        return $generated;
    }

    private function hasOpenFollowUpTask(ContractDocument $contract): bool
    {
        return Task::query()
            ->where('linkable_type', ContractDocument::class)
            ->where('linkable_id', $contract->id)
            ->where('status', '!=', TaskStatus::Completed)
            ->exists();
    }
}
