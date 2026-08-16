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

        $assetManagerRole = Role::where('slug', 'asset_manager')->firstOrFail();
        $assetManager = User::factory()->create(['role_id' => $assetManagerRole->id]);

        Livewire::test(CreateTask::class)
            ->fillForm([
                'title' => 'مراجعة المستندات',
                'assigned_role_id' => $assetManagerRole->id,
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

    public function test_can_create_task_targeting_consultant(): void
    {
        $consultantRole = Role::where('slug', 'consultant')->firstOrFail();
        $consultant = User::factory()->create(['role_id' => $consultantRole->id]);

        Livewire::test(CreateTask::class)
            ->fillForm([
                'title' => 'طلب تقرير',
                'assigned_role_id' => $consultantRole->id,
                'assigned_to' => $consultant->id,
                'due_date' => now()->addDays(3)->format('Y-m-d'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $task = Task::query()->where('title', 'طلب تقرير')->firstOrFail();

        $this->assertSame('consultant', $task->assignedRole->slug);
    }

    public function test_can_create_task_targeting_contractor(): void
    {
        $contractorRole = Role::where('slug', 'contractor')->firstOrFail();
        $contractor = User::factory()->create(['role_id' => $contractorRole->id]);

        Livewire::test(CreateTask::class)
            ->fillForm([
                'title' => 'طلب صيانة',
                'assigned_role_id' => $contractorRole->id,
                'assigned_to' => $contractor->id,
                'due_date' => now()->addDays(3)->format('Y-m-d'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $task = Task::query()->where('title', 'طلب صيانة')->firstOrFail();

        $this->assertSame('contractor', $task->assignedRole->slug);
    }

    public function test_list_page_hides_template_rows(): void
    {
        $instance = Task::factory()->create();
        $template = Task::factory()->recurringTemplate()->create();

        Livewire::test(ListTasks::class)
            ->assertCanSeeTableRecords([$instance])
            ->assertCanNotSeeTableRecords([$template]);
    }

    public function test_list_page_shows_single_create_button_regardless_of_role_count(): void
    {
        Role::create(['name' => 'المورد', 'is_active' => true, 'sort_order' => 10]);

        Livewire::test(ListTasks::class)
            ->assertSee('إضافة مهمة');
    }

    public function test_request_type_label_strips_redundant_definite_article(): void
    {
        $role = Role::create(['name' => 'الموزع', 'is_active' => true]);

        $this->assertSame('طلب مهمة من مدير الأصل للموزع', Task::requestTypeLabelFor($role));
    }
}
