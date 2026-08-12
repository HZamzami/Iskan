<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_requester_can_update_their_own_task(): void
    {
        $requester = User::factory()->create();
        $task = Task::factory()->create(['requested_by' => $requester->id]);

        $this->assertTrue($requester->can('update', $task));
    }

    public function test_assignee_can_update_task_assigned_to_them(): void
    {
        $assignee = User::factory()->create();
        $task = Task::factory()->create(['assigned_to' => $assignee->id]);

        $this->assertTrue($assignee->can('update', $task));
    }

    public function test_unrelated_user_cannot_update_task(): void
    {
        $bystander = User::factory()->create();
        $task = Task::factory()->create();

        $this->assertFalse($bystander->can('update', $task));
    }

    public function test_only_admin_can_delete_task(): void
    {
        $admin = $this->makeAdminUser();
        $bystander = User::factory()->create();
        $task = Task::factory()->create();

        $this->assertTrue($admin->can('delete', $task));
        $this->assertFalse($bystander->can('delete', $task));
    }
}
