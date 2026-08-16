<?php

namespace Tests\Feature;

use App\Enums\Module;
use App\Enums\TaskStatus;
use App\Filament\Resources\Tasks\Pages\CreateTask;
use App\Filament\Resources\Tasks\Pages\ListTasks;
use App\Filament\Resources\Tasks\Pages\ViewTask;
use App\Filament\Resources\Tasks\RelationManagers\SubtasksRelationManager;
use App\Models\Minute;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class TaskResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

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

    public function test_create_rejects_a_disallowed_attachment_extension(): void
    {
        $role = Role::where('slug', 'asset_manager')->firstOrFail();
        $assignee = User::factory()->create(['role_id' => $role->id]);

        Livewire::test(CreateTask::class)
            ->fillForm([
                'title' => 'مهمة بمرفق غير مسموح',
                'assigned_role_id' => $role->id,
                'assigned_to' => $assignee->id,
                'due_date' => now()->addDays(3)->format('Y-m-d'),
                'file_path' => UploadedFile::fake()->create('malware.exe', 100),
            ])
            ->call('create')
            ->assertHasFormErrors(['file_path']);
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

    public function test_can_create_task_linked_to_an_existing_archive_record(): void
    {
        $role = Role::where('slug', 'asset_manager')->firstOrFail();
        $assignee = User::factory()->create(['role_id' => $role->id]);
        $minute = Minute::factory()->create();

        Livewire::test(CreateTask::class)
            ->fillForm([
                'title' => 'متابعة محضر الاجتماع',
                'assigned_role_id' => $role->id,
                'assigned_to' => $assignee->id,
                'due_date' => now()->addDays(3)->format('Y-m-d'),
                'linkable_type' => Minute::class,
                'linkable_id' => $minute->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $task = Task::query()->where('title', 'متابعة محضر الاجتماع')->firstOrFail();

        $this->assertTrue($task->linkable->is($minute));
        $this->assertNull($task->requested_module);
    }

    public function test_can_create_task_requesting_a_new_archive_record(): void
    {
        $role = Role::where('slug', 'asset_manager')->firstOrFail();
        $assignee = User::factory()->create(['role_id' => $role->id]);

        Livewire::test(CreateTask::class)
            ->fillForm([
                'title' => 'إنشاء محضر جديد',
                'assigned_role_id' => $role->id,
                'assigned_to' => $assignee->id,
                'due_date' => now()->addDays(3)->format('Y-m-d'),
                'requested_module' => 'minutes',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $task = Task::query()->where('title', 'إنشاء محضر جديد')->firstOrFail();

        $this->assertSame(Module::Minutes, $task->requested_module);
        $this->assertNull($task->linkable_id);
    }

    public function test_fulfilling_a_requested_module_clears_the_request_and_links_the_record(): void
    {
        $task = Task::factory()->create(['requested_module' => Module::Minutes]);
        $minute = Minute::factory()->create();

        $task->fulfillRequestWith($minute);

        $this->assertTrue($task->fresh()->linkable->is($minute));
        $this->assertNull($task->fresh()->requested_module);
    }

    public function test_can_create_a_subtask_of_an_existing_task(): void
    {
        $role = Role::where('slug', 'asset_manager')->firstOrFail();
        $assignee = User::factory()->create(['role_id' => $role->id]);
        $parent = Task::factory()->create();

        Livewire::test(CreateTask::class)
            ->fillForm([
                'title' => 'مهمة فرعية',
                'subtask_of_id' => $parent->id,
                'assigned_role_id' => $role->id,
                'assigned_to' => $assignee->id,
                'due_date' => now()->addDays(3)->format('Y-m-d'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $subtask = Task::query()->where('title', 'مهمة فرعية')->firstOrFail();

        $this->assertTrue($subtask->subtaskOf->is($parent));
        $this->assertTrue($parent->subtasks->contains($subtask));
    }

    public function test_subtasks_progress_label_counts_completed_subtasks(): void
    {
        $parent = Task::factory()->create();

        $this->assertNull($parent->subtasksProgressLabel());

        Task::factory()->create(['subtask_of_id' => $parent->id, 'status' => TaskStatus::Completed]);
        Task::factory()->create(['subtask_of_id' => $parent->id]);

        $this->assertSame('1 / 2', $parent->fresh()->subtasksProgressLabel());
    }

    public function test_subtask_select_options_exclude_tasks_that_are_themselves_subtasks(): void
    {
        $topLevel = Task::factory()->create(['title' => 'مهمة رئيسية']);
        $existingSubtask = Task::factory()->create(['title' => 'مهمة فرعية موجودة', 'subtask_of_id' => $topLevel->id]);

        Livewire::test(CreateTask::class)
            ->assertSee('مهمة رئيسية')
            ->assertDontSee('مهمة فرعية موجودة');
    }

    public function test_subtasks_relation_manager_lists_and_creates_subtasks(): void
    {
        $role = Role::where('slug', 'asset_manager')->firstOrFail();
        $assignee = User::factory()->create(['role_id' => $role->id]);
        $parent = Task::factory()->create();
        $existingSubtask = Task::factory()->create(['subtask_of_id' => $parent->id, 'title' => 'مهمة فرعية سابقة']);

        Livewire::test(SubtasksRelationManager::class, ['ownerRecord' => $parent, 'pageClass' => ViewTask::class])
            ->assertCanSeeTableRecords([$existingSubtask])
            ->callAction(TestAction::make(CreateAction::class)->table(), [
                'title' => 'مهمة فرعية جديدة',
                'assigned_to' => $assignee->id,
                'priority' => 'normal',
            ])
            ->assertHasNoActionErrors();

        $this->assertTrue($parent->subtasks()->where('title', 'مهمة فرعية جديدة')->exists());
    }
}
