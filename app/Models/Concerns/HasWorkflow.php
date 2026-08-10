<?php

namespace App\Models\Concerns;

use App\Enums\WorkflowStatus;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkflowTransition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

/**
 * سلوك مشترك لسير الاعتماد (مقاول ← استشاري ← مدير الأصل) عبر وحدات المستندات
 * الستة. لا يُفعَّل فعلياً إلا للسجلات التي نوعها يتطلب ذلك
 * (requiresWorkflow())؛ باقي السجلات تبقى workflow_status = null دون أي
 * تأثير. القرارات الفعلية (هل الانتقال مسموح؟) تُتخذ في WorkflowService، لا
 * هنا — هذا الـ trait يوفر فقط العلاقات والبوابات الأساسية.
 */
trait HasWorkflow
{
    protected static function bootHasWorkflow(): void
    {
        static::creating(function (self $model): void {
            if (blank($model->created_by)) {
                $model->created_by = Auth::id();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'assigned_role_id');
    }

    public function transitions(): MorphMany
    {
        // ترتيب بالمعرّف تنازلياً لا بالتاريخ فقط: قد يقع أكثر من انتقال ضمن
        // نفس الثانية (كما في الاختبارات أو الاستخدام السريع)، ولا يضمن
        // created_at وحده ترتيباً مستقراً عند التعادل.
        return $this->morphMany(WorkflowTransition::class, 'transitionable')->latest('id');
    }

    public function requiresWorkflow(): bool
    {
        return $this->documentType?->requiresWorkflow() ?? false;
    }

    public function canBeActedOnBy(User $user): bool
    {
        return ($user->isAdmin() || $this->assigned_to === $user->id) && $user->can('update', $this);
    }

    public function canApprove(User $user): bool
    {
        return $user->isAdmin() || ($this->assigned_to === $user->id && $user->category() === 'asset_manager');
    }

    public function scopeAssignedTo(Builder $query, User $user): Builder
    {
        return $query->where('assigned_to', $user->id);
    }

    public function scopePendingWorkflow(Builder $query): Builder
    {
        return $query->whereNotNull('workflow_status')
            ->where('workflow_status', '!=', WorkflowStatus::Approved->value);
    }
}
