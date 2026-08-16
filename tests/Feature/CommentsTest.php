<?php

namespace Tests\Feature;

use App\Filament\Resources\Correspondences\Pages\ViewCorrespondence;
use App\Filament\Resources\Minutes\Pages\ViewMinute;
use App\Filament\Resources\Tasks\Pages\ViewTask;
use App\Filament\Resources\Tasks\RelationManagers\CommentsRelationManager;
use App\Models\Comment;
use App\Models\Correspondence;
use App\Models\Minute;
use App\Models\Task;
use App\Models\User;
use App\Notifications\CommentAddedNotification;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class CommentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_add_a_comment_to_a_task_and_notifies_assignee_and_requester(): void
    {
        Notification::fake();

        $assignee = User::factory()->create();
        $requester = User::factory()->create();
        $commenter = User::factory()->create();
        $task = Task::factory()->create(['assigned_to' => $assignee->id, 'requested_by' => $requester->id]);

        $this->actingAs($commenter);

        Livewire::test(CommentsRelationManager::class, ['ownerRecord' => $task, 'pageClass' => ViewTask::class])
            ->callAction(TestAction::make(CreateAction::class)->table(), [
                'body' => 'الرجاء تحديث الحالة',
            ])
            ->assertHasNoActionErrors();

        $comment = Comment::query()->where('body', 'الرجاء تحديث الحالة')->firstOrFail();

        $this->assertSame($commenter->id, $comment->user_id);
        $this->assertTrue($comment->commentable->is($task));

        Notification::assertSentTo($assignee, CommentAddedNotification::class);
        Notification::assertSentTo($requester, CommentAddedNotification::class);
        Notification::assertNotSentTo($commenter, CommentAddedNotification::class);
    }

    public function test_can_comment_on_an_archive_record_and_notifies_its_assignee_and_creator(): void
    {
        Notification::fake();

        $assignee = User::factory()->create();
        $creator = User::factory()->create();
        $this->actingAs($this->makeAdminUser());
        $minute = Minute::factory()->create(['assigned_to' => $assignee->id, 'created_by' => $creator->id]);

        Livewire::test(CommentsRelationManager::class, ['ownerRecord' => $minute, 'pageClass' => ViewMinute::class])
            ->callAction(TestAction::make(CreateAction::class)->table(), [
                'body' => 'يرجى المراجعة',
            ])
            ->assertHasNoActionErrors();

        Notification::assertSentTo($assignee, CommentAddedNotification::class);
        Notification::assertSentTo($creator, CommentAddedNotification::class);
    }

    public function test_commenting_on_a_correspondence_does_not_error_and_sends_no_notification(): void
    {
        Notification::fake();

        $this->actingAs($this->makeAdminUser());
        $correspondence = Correspondence::factory()->create();

        Livewire::test(CommentsRelationManager::class, ['ownerRecord' => $correspondence, 'pageClass' => ViewCorrespondence::class])
            ->callAction(TestAction::make(CreateAction::class)->table(), [
                'body' => 'ملاحظة',
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas(Comment::class, ['body' => 'ملاحظة']);
        Notification::assertNothingSent();
    }

    public function test_only_author_or_admin_can_edit_or_delete_a_comment(): void
    {
        $author = User::factory()->create();
        $bystander = User::factory()->create();
        $admin = $this->makeAdminUser();
        $task = Task::factory()->create();
        $comment = Comment::factory()->create(['commentable_type' => Task::class, 'commentable_id' => $task->id, 'user_id' => $author->id]);

        $this->assertTrue($comment->canBeManagedBy($author));
        $this->assertTrue($comment->canBeManagedBy($admin));
        $this->assertFalse($comment->canBeManagedBy($bystander));
    }
}
