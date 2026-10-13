<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Minute;
use App\Models\Task;
use App\Models\User;
use App\Notifications\CommentAddedNotification;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class NotificationLinksTest extends TestCase
{
    use RefreshDatabase;

    private function notificationUrl(User $user): string
    {
        $notification = DatabaseNotification::query()
            ->where('notifiable_id', $user->id)
            ->latest()
            ->firstOrFail();

        return $notification->data['actions'][0]['url'];
    }

    public function test_task_assigned_notification_stores_a_relative_link_that_opens_the_task(): void
    {
        $this->seedAccessControl();
        $requester = User::factory()->create();
        $assignee = User::factory()->create();
        $task = Task::factory()->create(['assigned_to' => $assignee->id, 'requested_by' => $requester->id]);

        $assignee->notify(new TaskAssignedNotification($task));

        $url = $this->notificationUrl($assignee);
        $this->assertSame("/tasks/{$task->id}", $url);

        $this->actingAs($assignee)->get($url)->assertOk()->assertSee($task->title);
    }

    public function test_task_link_does_not_depend_on_the_host_it_was_generated_on(): void
    {
        $this->seedAccessControl();
        $assignee = User::factory()->create();
        $task = Task::factory()->create(['assigned_to' => $assignee->id]);

        // يحاكي توليد المهمة من الجدولة (console) حيث يُؤخذ النطاق من APP_URL.
        URL::forceRootUrl('http://localhost');
        $assignee->notify(new TaskAssignedNotification($task));
        URL::forceRootUrl(null);

        $this->assertStringStartsNotWith('http', $this->notificationUrl($assignee));
    }

    public function test_task_assigned_email_still_uses_an_absolute_link(): void
    {
        $this->seedAccessControl();
        $assignee = User::factory()->create();
        $task = Task::factory()->create(['assigned_to' => $assignee->id]);

        $mail = (new TaskAssignedNotification($task))->toMail($assignee);

        $this->assertStringStartsWith('http', $mail->actionUrl);
        $this->assertStringEndsWith("/tasks/{$task->id}", $mail->actionUrl);
    }

    public function test_recurring_task_generated_by_the_scheduler_produces_a_working_link(): void
    {
        $this->seedAccessControl();
        $assignee = User::factory()->create();
        Task::factory()->recurringTemplate()->create([
            'assigned_to' => $assignee->id,
            'next_run_date' => today(),
        ]);

        $this->artisan('tasks:generate-recurring')->assertSuccessful();

        $url = $this->notificationUrl($assignee);
        $this->assertStringStartsWith('/tasks/', $url);

        $this->actingAs($assignee)->get($url)->assertOk();
    }

    public function test_comment_notification_links_to_the_commented_task_relatively(): void
    {
        $this->seedAccessControl();
        $author = User::factory()->create();
        $recipient = User::factory()->create();
        $task = Task::factory()->create(['assigned_to' => $recipient->id]);
        $comment = Comment::factory()->create([
            'commentable_type' => Task::class,
            'commentable_id' => $task->id,
            'user_id' => $author->id,
        ]);

        $recipient->notify(new CommentAddedNotification($comment));

        $this->assertSame("/tasks/{$task->id}", $this->notificationUrl($recipient));
    }

    public function test_comment_notification_on_an_archive_record_links_relatively(): void
    {
        $admin = $this->makeAdminUser();
        $minute = Minute::factory()->create(['sites' => null]);
        $comment = Comment::factory()->create([
            'commentable_type' => Minute::class,
            'commentable_id' => $minute->id,
            'user_id' => $admin->id,
        ]);

        $admin->notify(new CommentAddedNotification($comment));

        $url = $this->notificationUrl($admin);
        $this->assertSame("/minutes/{$minute->id}", $url);
        $this->actingAs($admin)->get($url)->assertOk();
    }

    public function test_old_admin_prefixed_links_redirect_to_the_new_path(): void
    {
        $this->get('/admin/tasks/15')->assertRedirect('/tasks/15')->assertStatus(301);
        $this->get('/admin')->assertRedirect('/');
        $this->get('/admin/minutes/3/edit')->assertRedirect('/minutes/3/edit');
    }

    public function test_old_admin_link_to_an_existing_task_ends_up_on_the_task(): void
    {
        $this->seedAccessControl();
        $assignee = User::factory()->create();
        $task = Task::factory()->create(['assigned_to' => $assignee->id]);

        $this->actingAs($assignee)
            ->followingRedirects()
            ->get("/admin/tasks/{$task->id}")
            ->assertOk()
            ->assertSee($task->title);
    }

    public function test_link_to_a_deleted_task_shows_the_friendly_not_found_page(): void
    {
        $this->seedAccessControl();
        $assignee = User::factory()->create();
        $task = Task::factory()->create(['assigned_to' => $assignee->id]);

        $assignee->notify(new TaskAssignedNotification($task));
        $url = $this->notificationUrl($assignee);
        $task->delete();

        $this->actingAs($assignee)
            ->get($url)
            ->assertNotFound()
            ->assertSee('لم نعثر على ما تبحث عنه')
            ->assertSee('تم حذف السجل')
            ->assertSee('العودة للوحة التحكم');
    }

    public function test_unknown_page_shows_the_friendly_not_found_page(): void
    {
        $this->seedAccessControl();

        $this->actingAs(User::factory()->create())
            ->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertSee('لم نعثر على ما تبحث عنه');
    }

    public function test_forbidden_page_explains_the_missing_permission(): void
    {
        $this->seedAccessControl();

        // صفحة الأدوار للمدير فقط.
        $this->actingAs(User::factory()->create())
            ->get('/roles')
            ->assertForbidden()
            ->assertSee('لا تملك صلاحية الوصول لهذه الصفحة');
    }
}
