<?php

namespace App\Services;

use App\Enums\WorkflowAction;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

/**
 * إشعار قاعدة بيانات Filament بسيط عند كل انتقال في سير الاعتماد؛ يعتمد على
 * سمة Notifiable الموجودة أصلاً (وغير المُستخدَمة سابقاً) على User، دون أي
 * حزمة جديدة.
 */
class WorkflowNotifier
{
    public function notify(Model $document, User $recipient, WorkflowAction $action): void
    {
        Notification::make()
            ->title($action->getLabel())
            ->body("{$document->reference_number}: {$document->title}")
            ->icon($action->getIcon())
            ->color($action->getColor())
            ->sendToDatabase($recipient);
    }
}
