<?php

namespace Tests\Feature;

use App\Filament\Resources\Tasks\Pages\CreateTask;
use App\Filament\Resources\Tasks\Pages\ListTasks;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class TaskResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->makeAdminUser());
    }

    public function test_can_create_internal_task_and_notifies_assignee(): void
    {
        Notification::fake();

        $assetManager = User::factory()->create(['role_id' => Role::where('slug', 'asset_manager')->value('id')]);

        Livewire::withQueryParams(['role' => 'asset_manager'])
            ->test(CreateTask::class)
            ->fillForm([
                'title' => 'مراجعة المستندات',
                'assigned_to' => $assetManager->id,
                'due_date' => now()->addDays(3)->format('Y-m-d'),
                'priority' => 'urgent',
                'recurrence' => 'once',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $task = Task::query()->where('title', 'مراجعة المستندات')->firstOrFail();

        $this->assertSame('asset_manager', $task->assignedRole->slug);
        $this->assertSame($assetManager->id, $task->assigned_to);
        $this->assertNotNull($task->requested_by);

        Notification::assertSentTo($assetManager, TaskAssignedNotification::class);
    }

    public function test_owner_consultant_task_locks_role_to_consultant(): void
    {
        $consultant = User::factory()->create(['role_id' => Role::where('slug', 'consultant')->value('id')]);

        Livewire::withQueryParams(['role' => 'consultant'])
            ->test(CreateTask::class)
            ->fillForm([
                'title' => 'طلب تقرير',
                'assigned_to' => $consultant->id,
                'due_date' => now()->addDays(3)->format('Y-m-d'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $task = Task::query()->where('title', 'طلب تقرير')->firstOrFail();

        $this->assertSame('consultant', $task->assignedRole->slug);
    }

    public function test_owner_contractor_task_locks_role_to_contractor(): void
    {
        $contractor = User::factory()->create(['role_id' => Role::where('slug', 'contractor')->value('id')]);

        Livewire::withQueryParams(['role' => 'contractor'])
            ->test(CreateTask::class)
            ->fillForm([
                'title' => 'طلب صيانة',
                'assigned_to' => $contractor->id,
                'due_date' => now()->addDays(3)->format('Y-m-d'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $task = Task::query()->where('title', 'طلب صيانة')->firstOrFail();

        $this->assertSame('contractor', $task->assignedRole->slug);
    }

    public function test_create_task_rejects_unknown_role_slug(): void
    {
        Livewire::withQueryParams(['role' => 'not-a-real-role'])
            ->test(CreateTask::class)
            ->assertStatus(404);
    }

    public function test_list_page_hides_template_rows(): void
    {
        $instance = Task::factory()->create();
        $template = Task::factory()->recurringTemplate()->create();

        Livewire::test(ListTasks::class)
            ->assertCanSeeTableRecords([$instance])
            ->assertCanNotSeeTableRecords([$template]);
    }

    public function test_adding_a_role_automatically_adds_a_request_button(): void
    {
        Role::create(['name' => 'المورد', 'is_active' => true, 'sort_order' => 10]);

        Livewire::test(ListTasks::class)
            ->assertSee('طلب مهمة من مدير الأصل للمورد');
    }

    public function test_request_type_label_strips_redundant_definite_article(): void
    {
        $role = Role::create(['name' => 'الموزع', 'is_active' => true]);

        $this->assertSame('طلب مهمة من مدير الأصل للموزع', Task::requestTypeLabelFor($role));
    }
}
