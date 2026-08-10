<?php

namespace Tests\Feature;

use App\Enums\AccessLevel;
use App\Enums\Module;
use App\Enums\WorkflowStatus;
use App\Filament\Resources\FinancialFlows\Pages\CreateFinancialFlow;
use App\Filament\Resources\FinancialFlows\Pages\ListFinancialFlows;
use App\Filament\Support\WorkflowFormFields;
use App\Models\FinancialFlow;
use App\Models\FinancialFlowType;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class WorkflowResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    private function editUser(array $sites = ['site_a']): User
    {
        $user = $this->makeUserWithAccess([Module::FinancialFlows->value => AccessLevel::Edit], $sites);
        $user->givePermissionTo(Module::FinancialFlows->permission(AccessLevel::Edit));

        return $user;
    }

    private function workflowType(): FinancialFlowType
    {
        $type = FinancialFlowType::first();
        $type->update(['requires_workflow' => true]);

        return $type;
    }

    public function test_end_to_end_submit_forward_approve_through_the_real_ui(): void
    {
        $contractor = $this->editUser(['site_a']);
        $consultant = $this->editUser(['site_a']);
        $owner = $this->editUser(['site_a']);
        $consultant->update(['role_id' => Role::where('slug', 'consultant')->first()->id]);
        $owner->update(['role_id' => Role::where('slug', 'owner')->first()->id]);

        $type = $this->workflowType();

        $this->actingAs($contractor);

        Livewire::test(CreateFinancialFlow::class)
            ->fillForm([
                'type' => $type->slug,
                'sites' => ['site_a'],
                'title' => 'طلب دفعة',
                'period_month' => '2026-07-01',
                'document_date' => '2026-07-20',
                'file_path' => UploadedFile::fake()->create('flow.pdf', 100, 'application/pdf'),
                'workflow_role_id' => Role::where('slug', 'consultant')->first()->id,
                'workflow_assigned_to' => $consultant->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $flow = FinancialFlow::query()->where('title', 'طلب دفعة')->firstOrFail();
        $this->assertSame(WorkflowStatus::Pending, $flow->workflow_status);
        $this->assertSame($consultant->id, $flow->assigned_to);

        $this->actingAs($consultant);

        Livewire::test(ListFinancialFlows::class)
            ->callAction(TestAction::make('workflowForward')->table($flow), data: [
                'role_id' => Role::where('slug', 'owner')->first()->id,
                'assigned_to' => $owner->id,
            ])
            ->assertNotified();

        $this->assertSame($owner->id, $flow->fresh()->assigned_to);

        $this->actingAs($owner);

        Livewire::test(ListFinancialFlows::class)
            ->callAction(TestAction::make('workflowApprove')->table($flow))
            ->assertNotified();

        $this->assertSame(WorkflowStatus::Approved, $flow->fresh()->workflow_status);
    }

    public function test_consultant_picker_only_lists_consultant_category_users(): void
    {
        $contractor = $this->editUser();
        $consultant = $this->editUser();
        $nonConsultant = $this->editUser();
        $consultant->update(['role_id' => Role::where('slug', 'consultant')->first()->id]);
        // $nonConsultant is deliberately left without a role/category.

        $type = $this->workflowType();

        $this->actingAs($contractor);

        $eligible = WorkflowFormFields::eligibleUsers(
            Role::where('slug', 'consultant')->first()->id,
            ['site_a'],
        );

        $this->assertArrayHasKey($consultant->id, $eligible);
        $this->assertArrayNotHasKey($nonConsultant->id, $eligible);
        $this->assertArrayNotHasKey($contractor->id, $eligible);
    }

    public function test_consultant_picker_excludes_users_without_site_access(): void
    {
        $consultantWithAccess = $this->editUser(['site_a']);
        $consultantWithoutAccess = $this->editUser(['site_b']);
        $consultantWithAccess->update(['role_id' => Role::where('slug', 'consultant')->first()->id]);
        $consultantWithoutAccess->update(['role_id' => Role::where('slug', 'consultant')->first()->id]);

        $eligible = WorkflowFormFields::eligibleUsers(
            Role::where('slug', 'consultant')->first()->id,
            ['site_a'],
        );

        $this->assertArrayHasKey($consultantWithAccess->id, $eligible);
        $this->assertArrayNotHasKey($consultantWithoutAccess->id, $eligible);
    }

    public function test_workflow_actions_are_hidden_from_users_who_are_not_the_current_assignee(): void
    {
        $contractor = $this->editUser();
        $consultant = $this->editUser();
        $bystander = $this->editUser();
        $consultant->update(['role_id' => Role::where('slug', 'consultant')->first()->id]);

        $type = $this->workflowType();
        $flow = FinancialFlow::factory()->create([
            'type' => $type->slug,
            'sites' => ['site_a'],
            'workflow_status' => WorkflowStatus::Pending,
            'assigned_to' => $consultant->id,
        ]);

        $this->actingAs($bystander);

        Livewire::test(ListFinancialFlows::class)
            ->assertActionHidden(TestAction::make('workflowForward')->table($flow))
            ->assertActionHidden(TestAction::make('workflowApprove')->table($flow));

        $this->actingAs($consultant);

        Livewire::test(ListFinancialFlows::class)
            ->assertActionVisible(TestAction::make('workflowForward')->table($flow));
    }

    public function test_approve_action_only_visible_to_owner_category_assignee(): void
    {
        $consultant = $this->editUser();
        $consultant->update(['role_id' => Role::where('slug', 'consultant')->first()->id]);

        $type = $this->workflowType();
        $flow = FinancialFlow::factory()->create([
            'type' => $type->slug,
            'sites' => ['site_a'],
            'workflow_status' => WorkflowStatus::Pending,
            'assigned_to' => $consultant->id,
        ]);

        $this->actingAs($consultant);

        Livewire::test(ListFinancialFlows::class)
            ->assertActionHidden(TestAction::make('workflowApprove')->table($flow))
            ->assertActionVisible(TestAction::make('workflowForward')->table($flow));
    }

    public function test_return_action_hidden_when_no_prior_transition_exists(): void
    {
        $consultant = $this->editUser();
        $consultant->update(['role_id' => Role::where('slug', 'consultant')->first()->id]);

        $type = $this->workflowType();
        $flow = FinancialFlow::factory()->create([
            'type' => $type->slug,
            'sites' => ['site_a'],
            'workflow_status' => WorkflowStatus::Pending,
            'assigned_to' => $consultant->id,
        ]);

        $this->actingAs($consultant);

        Livewire::test(ListFinancialFlows::class)
            ->assertActionHidden(TestAction::make('workflowReturn')->table($flow));
    }

    public function test_mine_tab_filters_to_records_assigned_to_current_user(): void
    {
        $user = $this->editUser();
        $type = $this->workflowType();

        $assignedToMe = FinancialFlow::factory()->create([
            'type' => $type->slug,
            'sites' => ['site_a'],
            'workflow_status' => WorkflowStatus::Pending,
            'assigned_to' => $user->id,
        ]);
        $assignedToSomeoneElse = FinancialFlow::factory()->create([
            'type' => $type->slug,
            'sites' => ['site_a'],
            'workflow_status' => WorkflowStatus::Pending,
            'assigned_to' => User::factory()->create()->id,
        ]);

        $this->actingAs($user);

        Livewire::test(ListFinancialFlows::class)
            ->set('activeTab', 'mine')
            ->assertCanSeeTableRecords([$assignedToMe])
            ->assertCanNotSeeTableRecords([$assignedToSomeoneElse]);
    }

    public function test_non_workflow_type_records_never_show_workflow_actions(): void
    {
        $user = $this->editUser();
        $type = FinancialFlowType::first();
        $type->update(['requires_workflow' => false]);

        $flow = FinancialFlow::factory()->create([
            'type' => $type->slug,
            'sites' => ['site_a'],
        ]);

        $this->actingAs($user);

        Livewire::test(ListFinancialFlows::class)
            ->assertActionHidden(TestAction::make('workflowForward')->table($flow))
            ->assertActionHidden(TestAction::make('workflowReturn')->table($flow))
            ->assertActionHidden(TestAction::make('workflowApprove')->table($flow));
    }
}
