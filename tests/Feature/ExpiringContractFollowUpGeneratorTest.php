<?php

namespace Tests\Feature;

use App\Models\ContractDocument;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Services\ExpiringContractFollowUpGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ExpiringContractFollowUpGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_a_linked_task_for_a_contract_expiring_within_30_days(): void
    {
        Notification::fake();

        $assignee = User::factory()->create();
        $contract = ContractDocument::factory()->create([
            'assigned_to' => $assignee->id,
            'end_date' => today()->addDays(10),
        ]);

        $count = app(ExpiringContractFollowUpGenerator::class)->run();

        $this->assertSame(1, $count);

        $task = Task::query()->where('linkable_type', ContractDocument::class)->where('linkable_id', $contract->id)->firstOrFail();

        $this->assertSame($assignee->id, $task->assigned_to);
        $this->assertTrue($task->due_date->isSameDay($contract->end_date));
        Notification::assertSentTo($assignee, TaskAssignedNotification::class);
    }

    public function test_skips_contracts_without_an_assignee(): void
    {
        ContractDocument::factory()->create([
            'assigned_to' => null,
            'end_date' => today()->addDays(10),
        ]);

        $count = app(ExpiringContractFollowUpGenerator::class)->run();

        $this->assertSame(0, $count);
        $this->assertSame(0, Task::query()->count());
    }

    public function test_skips_contracts_outside_the_30_day_window(): void
    {
        $assignee = User::factory()->create();
        ContractDocument::factory()->create(['assigned_to' => $assignee->id, 'end_date' => today()->addDays(60)]);
        ContractDocument::factory()->create(['assigned_to' => $assignee->id, 'end_date' => today()->subDay()]);

        $count = app(ExpiringContractFollowUpGenerator::class)->run();

        $this->assertSame(0, $count);
    }

    public function test_is_idempotent_across_repeated_runs(): void
    {
        $assignee = User::factory()->create();
        ContractDocument::factory()->create(['assigned_to' => $assignee->id, 'end_date' => today()->addDays(10)]);

        $generator = app(ExpiringContractFollowUpGenerator::class);
        $first = $generator->run();
        $second = $generator->run();

        $this->assertSame(1, $first);
        $this->assertSame(0, $second);
        $this->assertSame(1, Task::query()->count());
    }

    public function test_generates_a_new_task_if_the_previous_follow_up_was_already_completed(): void
    {
        $assignee = User::factory()->create();
        $contract = ContractDocument::factory()->create(['assigned_to' => $assignee->id, 'end_date' => today()->addDays(10)]);

        Task::factory()->completed()->create([
            'linkable_type' => ContractDocument::class,
            'linkable_id' => $contract->id,
        ]);

        $count = app(ExpiringContractFollowUpGenerator::class)->run();

        $this->assertSame(1, $count);
    }

    public function test_console_command_runs_the_generator(): void
    {
        $assignee = User::factory()->create();
        ContractDocument::factory()->create(['assigned_to' => $assignee->id, 'end_date' => today()->addDays(10)]);

        $this->artisan('tasks:generate-contract-followups')->assertSuccessful();

        $this->assertSame(1, Task::query()->count());
    }
}
