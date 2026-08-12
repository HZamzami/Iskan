<?php

namespace App\Notifications;

use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Task;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification
{
    public function __construct(protected Task $task) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->task->notify_by_email ? ['database', 'mail'] : ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('تكليف بمهمة جديدة')
            ->body($this->task->title)
            ->icon($this->task->priority->getIcon())
            ->color($this->task->priority->getColor())
            ->actions([
                Action::make('view')
                    ->label('عرض المهمة')
                    ->url(TaskResource::getUrl('view', ['record' => $this->task]))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('تكليف بمهمة جديدة: '.$this->task->title)
            ->greeting('مرحباً '.$notifiable->name)
            ->line('تم تكليفك بمهمة جديدة: '.$this->task->title)
            ->when($this->task->due_date, fn (MailMessage $mail) => $mail->line('تاريخ الانتهاء: '.$this->task->due_date->format('Y/m/d')))
            ->action('عرض المهمة', TaskResource::getUrl('view', ['record' => $this->task]));
    }
}
