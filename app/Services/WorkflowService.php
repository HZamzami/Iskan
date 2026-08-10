<?php

namespace App\Services;

use App\Enums\WorkflowAction;
use App\Enums\WorkflowStatus;
use App\Models\EntityType;
use App\Models\User;
use App\Models\WorkflowTransition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * السلطة الوحيدة لقواعد الانتقال في سير الاعتماد (مصدر واحد للحقيقة، بدلاً
 * من تكرار منطق الانتقال داخل كل إجراء Filament عبر الوحدات الست). كل طريقة
 * تتحقق من شرعية الانتقال، تحدّث السجل، وتُسجّل صفاً في WorkflowTransition،
 * ثم تُرسل إشعاراً لمن أصبح السجل بعهدته (أو لمنشئ السجل عند الاعتماد
 * النهائي).
 */
class WorkflowService
{
    public function submit(Model $document, User $actor, EntityType $category, User $target, ?string $note = null): void
    {
        if ($document->workflow_status !== null) {
            throw new LogicException('لا يمكن إرسال سجل بدأ سير اعتماده بالفعل.');
        }

        DB::transaction(function () use ($document, $actor, $category, $target, $note): void {
            $document->forceFill([
                'workflow_status' => WorkflowStatus::Pending,
                'assigned_to' => $target->id,
                'assigned_entity_type_id' => $category->id,
            ])->save();

            $this->recordTransition(
                document: $document,
                fromStatus: null,
                toStatus: WorkflowStatus::Pending,
                action: WorkflowAction::Submit,
                actor: $actor,
                category: $category,
                assignedTo: $target,
                note: $note,
            );
        });

        app(WorkflowNotifier::class)->notify($document, $target, WorkflowAction::Submit);
    }

    public function forward(Model $document, User $actor, EntityType $category, User $target, ?string $note = null): void
    {
        $this->assertCanAct($document, $actor);

        DB::transaction(function () use ($document, $actor, $category, $target, $note): void {
            $document->forceFill([
                'assigned_to' => $target->id,
                'assigned_entity_type_id' => $category->id,
            ])->save();

            $this->recordTransition(
                document: $document,
                fromStatus: WorkflowStatus::Pending,
                toStatus: WorkflowStatus::Pending,
                action: WorkflowAction::Forward,
                actor: $actor,
                category: $category,
                assignedTo: $target,
                note: $note,
            );
        });

        app(WorkflowNotifier::class)->notify($document, $target, WorkflowAction::Forward);
    }

    public function returnToPrevious(Model $document, User $actor, ?string $note = null): void
    {
        $this->assertCanAct($document, $actor);

        /** @var WorkflowTransition|null $lastTransition */
        $lastTransition = $document->transitions()->first();

        if ($lastTransition === null) {
            throw new LogicException('لا توجد جهة سابقة لإعادة السجل إليها.');
        }

        $previousHolder = $lastTransition->actor;
        $category = $previousHolder->category() !== null
            ? EntityType::query()->where('slug', $previousHolder->category())->first()
            : null;

        DB::transaction(function () use ($document, $actor, $category, $previousHolder, $note): void {
            $document->forceFill([
                'assigned_to' => $previousHolder->id,
                'assigned_entity_type_id' => $category?->id,
            ])->save();

            $this->recordTransition(
                document: $document,
                fromStatus: WorkflowStatus::Pending,
                toStatus: WorkflowStatus::Pending,
                action: WorkflowAction::Return,
                actor: $actor,
                category: $category,
                assignedTo: $previousHolder,
                note: $note,
            );
        });

        app(WorkflowNotifier::class)->notify($document, $previousHolder, WorkflowAction::Return);
    }

    public function approve(Model $document, User $actor, ?string $note = null): void
    {
        if (! $document->canApprove($actor)) {
            throw new LogicException('لا يملك هذا المستخدم صلاحية الاعتماد النهائي لهذا السجل.');
        }

        if ($document->workflow_status !== WorkflowStatus::Pending) {
            throw new LogicException('لا يمكن اعتماد سجل ليس قيد المراجعة.');
        }

        DB::transaction(function () use ($document, $actor, $note): void {
            $document->forceFill([
                'workflow_status' => WorkflowStatus::Approved,
                'assigned_to' => null,
                'assigned_entity_type_id' => null,
                'completed_at' => now(),
            ])->save();

            $this->recordTransition(
                document: $document,
                fromStatus: WorkflowStatus::Pending,
                toStatus: WorkflowStatus::Approved,
                action: WorkflowAction::Approve,
                actor: $actor,
                category: null,
                assignedTo: null,
                note: $note,
            );
        });

        if ($document->creator) {
            app(WorkflowNotifier::class)->notify($document, $document->creator, WorkflowAction::Approve);
        }
    }

    private function assertCanAct(Model $document, User $actor): void
    {
        if (! $document->canBeActedOnBy($actor)) {
            throw new LogicException('لا يملك هذا المستخدم صلاحية التصرف في هذا السجل حالياً.');
        }

        if ($document->workflow_status !== WorkflowStatus::Pending) {
            throw new LogicException('لا يمكن التصرف في سجل ليس قيد المراجعة.');
        }
    }

    private function recordTransition(
        Model $document,
        ?WorkflowStatus $fromStatus,
        WorkflowStatus $toStatus,
        WorkflowAction $action,
        User $actor,
        ?EntityType $category,
        ?User $assignedTo,
        ?string $note,
    ): void {
        $document->transitions()->create([
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'action' => $action,
            'actor_id' => $actor->id,
            'entity_type_id' => $category?->id,
            'assigned_to_id' => $assignedTo?->id,
            'note' => $note,
        ]);
    }
}
