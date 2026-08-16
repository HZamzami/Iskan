<?php

namespace App\Filament\Support;

use App\Models\Task;
use Filament\Notifications\Notification;

/**
 * تُستخدَم في صفحات الإنشاء (CreateRecord) لوحدات الأرشيف: إذا فُتحت الصفحة
 * عبر زر "إنشاء السجل الآن" في مهمة تطلب إنشاء سجل من هذا النوع (رابط يحمل
 * from_task=)، تربط السجل الجديد بتلك المهمة تلقائياً بعد إنشائه وتُسقط
 * طلب الإنشاء المعلَّق بما أنه تحقق الآن. تُقرأ from_task في mount() لأن
 * afterCreate() يُستدعى ضمن طلب Livewire لاحق لا يحمل معه سلسلة استعلام
 * الصفحة الأصلية.
 */
trait LinksCreatedRecordToTask
{
    public ?int $fromTaskId = null;

    public function mount(): void
    {
        parent::mount();

        $this->fromTaskId = request()->integer('from_task') ?: null;
    }

    protected function afterCreate(): void
    {
        if ($this->fromTaskId === null) {
            return;
        }

        $task = Task::query()->find($this->fromTaskId);

        if ($task === null) {
            return;
        }

        $task->fulfillRequestWith($this->record);

        Notification::make()
            ->success()
            ->title('تم ربط السجل بالمهمة')
            ->send();
    }
}
