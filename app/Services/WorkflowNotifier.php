<?php

namespace App\Services;

use App\Enums\Module;
use App\Enums\WorkflowAction;
use App\Models\User;
use Filament\Actions\Action;
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
        $notification = Notification::make()
            ->title($action->getLabel())
            ->body("{$document->reference_number}: {$document->title}")
            ->icon($action->getIcon())
            ->color($action->getColor());

        if ($url = $this->documentUrl($document)) {
            $notification->actions([
                Action::make('view')
                    ->label('عرض السجل')
                    ->url($url)
                    ->markAsRead(),
            ]);
        }

        $notification->sendToDatabase($recipient);
    }

    /**
     * رابط نسبي (بدون النطاق) كي يبقى صالحاً أياً كان العنوان الذي يُفتح منه النظام.
     */
    private function documentUrl(Model $document): ?string
    {
        return Module::fromModelClass($document::class)
            ?->resourceClass()::getUrl('view', ['record' => $document->getKey()], isAbsolute: false);
    }
}
