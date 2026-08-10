<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Enums\Module;
use App\Enums\WorkflowAction;
use App\Enums\WorkflowStatus;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\Minute;
use App\Models\MinuteType;
use App\Models\User;
use App\Services\WorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use LogicException;
use Tests\TestCase;

class WorkflowServiceTest extends TestCase
{
    use RefreshDatabase;

    private WorkflowService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedAccessControl();

        $this->service = app(WorkflowService::class);
    }

    private function makeCategorizedUser(string $categorySlug): User
    {
        $user = $this->makeUserWithAccess([Module::Minutes->value => AccessLevel::Edit], ['site_a']);
        $user->update([
            'entity_id' => Entity::factory()->create([
                'entity_type_id' => EntityType::query()->where('slug', $categorySlug)->firstOrFail()->id,
            ])->id,
        ]);

        return $user->fresh();
    }

    private function workflowMinuteType(): MinuteType
    {
        $type = MinuteType::query()->where('slug', 'weekly_meeting')->firstOrFail();
        $type->update(['requires_workflow' => true]);

        return $type;
    }

    public function test_submit_starts_the_chain_and_logs_a_transition(): void
    {
        $contractor = $this->makeCategorizedUser('contractor');
        $this->actingAs($contractor);
        $consultant = $this->makeCategorizedUser('consultant');
        $type = $this->workflowMinuteType();

        $minute = Minute::factory()->create(['type' => $type->slug, 'sites' => null]);

        $this->service->submit(
            $minute,
            $contractor,
            EntityType::where('slug', 'consultant')->firstOrFail(),
            $consultant,
            'الرجاء المراجعة',
        );

        $minute->refresh();

        $this->assertSame(WorkflowStatus::Pending, $minute->workflow_status);
        $this->assertSame($consultant->id, $minute->assigned_to);
        $this->assertSame($contractor->id, $minute->created_by);
        $this->assertSame(1, $minute->transitions()->count());

        $transition = $minute->transitions()->firstOrFail();
        $this->assertSame(WorkflowAction::Submit, $transition->action);
        $this->assertNull($transition->from_status);
        $this->assertSame(WorkflowStatus::Pending, $transition->to_status);
        $this->assertSame('الرجاء المراجعة', $transition->note);
    }

    public function test_submit_twice_is_rejected(): void
    {
        $contractor = $this->makeCategorizedUser('contractor');
        $this->actingAs($contractor);
        $consultant = $this->makeCategorizedUser('consultant');
        $type = $this->workflowMinuteType();
        $minute = Minute::factory()->create(['type' => $type->slug, 'sites' => null]);

        $this->service->submit($minute, $contractor, EntityType::where('slug', 'consultant')->firstOrFail(), $consultant);

        $this->expectException(LogicException::class);
        $this->service->submit($minute->fresh(), $contractor, EntityType::where('slug', 'consultant')->firstOrFail(), $consultant);
    }

    public function test_chain_can_revisit_the_same_category_any_number_of_times(): void
    {
        $contractor = $this->makeCategorizedUser('contractor');
        $this->actingAs($contractor);
        $consultantA = $this->makeCategorizedUser('consultant');
        $consultantB = $this->makeCategorizedUser('consultant');
        $owner = $this->makeCategorizedUser('owner');
        $type = $this->workflowMinuteType();
        $minute = Minute::factory()->create(['type' => $type->slug, 'sites' => null]);

        // contractor -> consultantA -> consultantB -> contractor -> owner
        $this->service->submit($minute, $contractor, EntityType::where('slug', 'consultant')->firstOrFail(), $consultantA);
        $this->service->forward($minute->fresh(), $consultantA, EntityType::where('slug', 'consultant')->firstOrFail(), $consultantB);
        $this->service->forward($minute->fresh(), $consultantB, EntityType::where('slug', 'contractor')->firstOrFail(), $contractor);
        $this->service->forward($minute->fresh(), $contractor, EntityType::where('slug', 'owner')->firstOrFail(), $owner);

        $minute->refresh();

        $this->assertSame($owner->id, $minute->assigned_to);
        $this->assertSame(WorkflowStatus::Pending, $minute->workflow_status);
        $this->assertSame(4, $minute->transitions()->count());

        $this->service->approve($minute->fresh(), $owner);

        $this->assertSame(WorkflowStatus::Approved, $minute->fresh()->workflow_status);
    }

    public function test_forward_by_non_assignee_is_rejected(): void
    {
        $contractor = $this->makeCategorizedUser('contractor');
        $this->actingAs($contractor);
        $consultant = $this->makeCategorizedUser('consultant');
        $stranger = $this->makeCategorizedUser('consultant');
        $type = $this->workflowMinuteType();
        $minute = Minute::factory()->create(['type' => $type->slug, 'sites' => null]);

        $this->service->submit($minute, $contractor, EntityType::where('slug', 'consultant')->firstOrFail(), $consultant);

        $this->expectException(LogicException::class);
        $this->service->forward($minute->fresh(), $stranger, EntityType::where('slug', 'consultant')->firstOrFail(), $consultant);
    }

    public function test_return_targets_whoever_sent_it_to_the_current_holder(): void
    {
        $contractor = $this->makeCategorizedUser('contractor');
        $this->actingAs($contractor);
        $consultant = $this->makeCategorizedUser('consultant');
        $owner = $this->makeCategorizedUser('owner');
        $type = $this->workflowMinuteType();
        $minute = Minute::factory()->create(['type' => $type->slug, 'sites' => null]);

        $this->service->submit($minute, $contractor, EntityType::where('slug', 'consultant')->firstOrFail(), $consultant);
        $this->service->forward($minute->fresh(), $consultant, EntityType::where('slug', 'owner')->firstOrFail(), $owner);
        $this->service->returnToPrevious($minute->fresh(), $owner, 'يحتاج تعديل');

        $minute->refresh();

        $this->assertSame($consultant->id, $minute->assigned_to);
        $this->assertSame(WorkflowStatus::Pending, $minute->workflow_status);

        $lastTransition = $minute->transitions()->firstOrFail();
        $this->assertSame(WorkflowAction::Return, $lastTransition->action);
    }

    public function test_return_with_no_prior_transition_is_rejected(): void
    {
        // Structurally impossible via submit()/forward() alone, but the service still guards it directly.
        $contractor = $this->makeCategorizedUser('contractor');
        $this->actingAs($contractor);
        $type = $this->workflowMinuteType();
        $minute = Minute::factory()->create([
            'type' => $type->slug,
            'sites' => null,
            'workflow_status' => WorkflowStatus::Pending,
            'assigned_to' => $contractor->id,
        ]);

        $this->expectException(LogicException::class);
        $this->service->returnToPrevious($minute, $contractor);
    }

    public function test_only_owner_category_can_approve(): void
    {
        $contractor = $this->makeCategorizedUser('contractor');
        $this->actingAs($contractor);
        $consultant = $this->makeCategorizedUser('consultant');
        $type = $this->workflowMinuteType();
        $minute = Minute::factory()->create(['type' => $type->slug, 'sites' => null]);

        $this->service->submit($minute, $contractor, EntityType::where('slug', 'consultant')->firstOrFail(), $consultant);

        $this->expectException(LogicException::class);
        $this->service->approve($minute->fresh(), $consultant);
    }

    public function test_approve_by_the_assigned_owner_completes_the_record(): void
    {
        $contractor = $this->makeCategorizedUser('contractor');
        $this->actingAs($contractor);
        $owner = $this->makeCategorizedUser('owner');
        $type = $this->workflowMinuteType();
        $minute = Minute::factory()->create(['type' => $type->slug, 'sites' => null]);

        $this->service->submit($minute, $contractor, EntityType::where('slug', 'owner')->firstOrFail(), $owner);
        $this->service->approve($minute->fresh(), $owner, 'معتمد');

        $minute->refresh();

        $this->assertSame(WorkflowStatus::Approved, $minute->workflow_status);
        $this->assertNull($minute->assigned_to);
        $this->assertNull($minute->assigned_entity_type_id);
        $this->assertNotNull($minute->completed_at);

        $lastTransition = $minute->transitions()->firstOrFail();
        $this->assertSame(WorkflowAction::Approve, $lastTransition->action);
        $this->assertSame(WorkflowStatus::Approved, $lastTransition->to_status);
    }

    public function test_cannot_act_on_an_approved_record(): void
    {
        $contractor = $this->makeCategorizedUser('contractor');
        $this->actingAs($contractor);
        $owner = $this->makeCategorizedUser('owner');
        $type = $this->workflowMinuteType();
        $minute = Minute::factory()->create(['type' => $type->slug, 'sites' => null]);

        $this->service->submit($minute, $contractor, EntityType::where('slug', 'owner')->firstOrFail(), $owner);
        $this->service->approve($minute->fresh(), $owner);

        $this->expectException(LogicException::class);
        $this->service->forward($minute->fresh(), $owner, EntityType::where('slug', 'contractor')->firstOrFail(), $contractor);
    }

    public function test_each_transition_notifies_the_new_holder(): void
    {
        $contractor = $this->makeCategorizedUser('contractor');
        $this->actingAs($contractor);
        $consultant = $this->makeCategorizedUser('consultant');
        $type = $this->workflowMinuteType();
        $minute = Minute::factory()->create(['type' => $type->slug, 'sites' => null]);

        $this->service->submit($minute, $contractor, EntityType::where('slug', 'consultant')->firstOrFail(), $consultant);

        $this->assertSame(1, DatabaseNotification::query()->where('notifiable_id', $consultant->id)->count());

        $notification = DatabaseNotification::query()->where('notifiable_id', $consultant->id)->firstOrFail();
        $this->assertSame('إرسال', $notification->data['title']);
        $this->assertStringContainsString($minute->reference_number, $notification->data['body']);
    }

    public function test_forward_and_return_each_notify_only_the_new_holder(): void
    {
        $contractor = $this->makeCategorizedUser('contractor');
        $this->actingAs($contractor);
        $consultant = $this->makeCategorizedUser('consultant');
        $owner = $this->makeCategorizedUser('owner');
        $type = $this->workflowMinuteType();
        $minute = Minute::factory()->create(['type' => $type->slug, 'sites' => null]);

        $this->service->submit($minute, $contractor, EntityType::where('slug', 'consultant')->firstOrFail(), $consultant);
        $this->service->forward($minute->fresh(), $consultant, EntityType::where('slug', 'owner')->firstOrFail(), $owner);

        $this->assertSame(1, DatabaseNotification::query()->where('notifiable_id', $owner->id)->count());
        $this->assertSame(1, DatabaseNotification::query()->where('notifiable_id', $consultant->id)->count());

        $this->service->returnToPrevious($minute->fresh(), $owner);

        // consultant gets a 2nd notification (the return); owner gets no additional one.
        $this->assertSame(2, DatabaseNotification::query()->where('notifiable_id', $consultant->id)->count());
        $this->assertSame(1, DatabaseNotification::query()->where('notifiable_id', $owner->id)->count());
    }

    public function test_approve_notifies_the_original_creator(): void
    {
        $contractor = $this->makeCategorizedUser('contractor');
        $this->actingAs($contractor);
        $owner = $this->makeCategorizedUser('owner');
        $type = $this->workflowMinuteType();
        $minute = Minute::factory()->create(['type' => $type->slug, 'sites' => null]);

        $this->service->submit($minute, $contractor, EntityType::where('slug', 'owner')->firstOrFail(), $owner);
        $this->service->approve($minute->fresh(), $owner);

        $this->assertSame(1, DatabaseNotification::query()->where('notifiable_id', $contractor->id)->count());
    }
}
