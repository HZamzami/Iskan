<?php

namespace App\Console\Commands;

use App\Services\ExpiringContractFollowUpGenerator;
use Illuminate\Console\Command;

class GenerateContractFollowUpTasks extends Command
{
    protected $signature = 'tasks:generate-contract-followups';

    protected $description = 'توليد مهام تجديد للعقود التي تقارب نهايتها';

    public function handle(ExpiringContractFollowUpGenerator $generator): int
    {
        $count = $generator->run();

        $this->info("تم إنشاء {$count} مهمة تجديد عقد.");

        return self::SUCCESS;
    }
}
