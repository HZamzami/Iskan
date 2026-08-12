<?php

namespace App\Console\Commands;

use App\Services\TaskRecurrenceGenerator;
use Illuminate\Console\Command;

class GenerateRecurringTasks extends Command
{
    protected $signature = 'tasks:generate-recurring';

    protected $description = 'توليد نسخ جديدة من المهام المتكررة المستحقة';

    public function handle(TaskRecurrenceGenerator $generator): int
    {
        $count = $generator->run();

        $this->info("تم إنشاء {$count} مهمة متكررة.");

        return self::SUCCESS;
    }
}
