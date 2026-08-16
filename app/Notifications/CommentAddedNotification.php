<?php

namespace App\Notifications;

use App\Enums\Module;
use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Comment;
use App\Models\Task;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class CommentAddedNotification extends Notification
{
    public function __construct(protected Comment $comment) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $notification = FilamentNotification::make()
            ->title('تعليق جديد من '.$this->comment->author->name)
            ->body(str($this->comment->body)->limit(120));

        if ($url = $this->commentableUrl()) {
            $notification->actions([
                Action::make('view')
                    ->label('عرض')
                    ->url($url)
                    ->markAsRead(),
            ]);
        }

        return $notification->getDatabaseMessage();
    }

    private function commentableUrl(): ?string
    {
        $commentable = $this->comment->commentable;

        if ($commentable instanceof Task) {
            return TaskResource::getUrl('view', ['record' => $commentable->id]);
        }

        $module = Module::fromModelClass($commentable::class);

        return $module?->resourceClass()::getUrl('view', ['record' => $commentable->id]);
    }
}
