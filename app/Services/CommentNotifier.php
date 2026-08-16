<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use App\Notifications\CommentAddedNotification;
use Illuminate\Support\Collection;

class CommentNotifier
{
    /**
     * تُخطر أصحاب السجل المرتبط (المكلَّف وصاحب الطلب/المُنشئ) بتعليق جديد،
     * باستثناء كاتب التعليق نفسه. المراسلات لا تملك مفهوم "مكلَّف" فلا يصلها
     * إشعار.
     */
    public function notifyNewComment(Comment $comment): void
    {
        $commentable = $comment->commentable;

        $recipients = match (true) {
            $commentable instanceof Task => collect([$commentable->assignee, $commentable->requester]),
            method_exists($commentable, 'assignee') => collect([$commentable->assignee, $commentable->creator]),
            default => collect(),
        };

        /** @var Collection<int, User> $recipients */
        $recipients
            ->filter()
            ->unique('id')
            ->reject(fn (User $user): bool => $user->id === $comment->user_id)
            ->each(fn (User $user) => $user->notify(new CommentAddedNotification($comment)));
    }
}
