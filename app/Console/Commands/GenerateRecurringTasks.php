<?php

namespace App\Console\Commands;

use App\Services\TaskRecurrenceGenerator;
use Illuminate\Console\Command;

class GenerateRecurringTasks extends Command
{
    protected $signature = 'tasks:generate-recurring';

    protected $description = 'إنشاء نسخة جديدة من كل مهمة متكررة حان موعد تكرارها التالي';

    public function handle(TaskRecurrenceGenerator $generator): int
    {
        $count = $generator->run();

        $this->info("تم إنشاء {$count} مهمة متكررة.");

        return self::SUCCESS;
    }
}
