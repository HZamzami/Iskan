<?php

namespace App\Models;

use App\Enums\Module;
use App\Enums\TaskPriority;
use App\Enums\TaskRecurrence;
use App\Enums\TaskStatus;
use App\Models\Concerns\LogsArchiveActivity;
use Carbon\Carbon;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    use LogsArchiveActivity;

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'due_time',
        'assigned_to',
        'assigned_role_id',
        'requested_by',
        'priority',
        'status',
        'recurrence',
        'is_template',
        'is_active',
        'next_run_date',
        'parent_task_id',
        'file_path',
        'notify_by_email',
        'completed_at',
        'linkable_type',
        'linkable_id',
        'requested_module',
        'subtask_of_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (Task $task): void {
            if (blank($task->requested_by)) {
                $task->requested_by = Auth::id();
            }
        });
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function assignedRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'assigned_role_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function occurrences(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function subtaskOf(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'subtask_of_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'subtask_of_id');
    }

    /**
     * نسبة إنجاز المهام الفرعية كنص "٢ / ٥"، أو null إن لم تكن هناك مهام فرعية.
     */
    public function subtasksProgressLabel(): ?string
    {
        $total = $this->subtasks()->count();

        if ($total === 0) {
            return null;
        }

        $completed = $this->subtasks()->where('status', TaskStatus::Completed)->count();

        return "{$completed} / {$total}";
    }

    /**
     * تربط المهمة بسجل تم إنشاؤه تلبيةً لطلبها (مثلاً محضر طُلب إنشاؤه)،
     * وتُسقط طلب الإنشاء المعلَّق بما أنه تحقق الآن.
     */
    public function fulfillRequestWith(Model $record): void
    {
        $this->linkable()->associate($record);
        $this->requested_module = null;
        $this->save();
    }

    /**
     * التسمية المعروضة للسجل المرتبط، أو لطلب إنشائه إن لم يُنشأ بعد.
     */
    public function linkedRecordLabel(): ?string
    {
        if ($this->linkable !== null) {
            $module = Module::fromModelClass($this->linkable_type);

            return $module === Module::Correspondences
                ? "{$this->linkable->reference_number} — {$this->linkable->subject}"
                : "{$this->linkable->reference_number} — {$this->linkable->title}";
        }

        if ($this->requested_module instanceof Module) {
            return "مطلوب: {$this->requested_module->getLabel()}";
        }

        return null;
    }

    public function linkedRecordUrl(): ?string
    {
        if ($this->linkable === null) {
            return null;
        }

        $module = Module::fromModelClass($this->linkable_type);

        return $module?->resourceClass()::getUrl('view', ['record' => $this->linkable_id]);
    }

    public function requestTypeLabel(): string
    {
        return self::requestTypeLabelFor($this->assignedRole);
    }

    /**
     * Derives the request-type label from the target role's own name, so
     * adding a role in الأدوار automatically gets a matching task-request
     * label with no code change. مدير الأصل is special-cased as "داخلية"
     * since it's the requester's own organization, not an external party.
     */
    public static function requestTypeLabelFor(?Role $role): string
    {
        if ($role === null) {
            return '—';
        }

        if ($role->slug === 'asset_manager') {
            return 'طلب مهمة داخلية';
        }

        // "لل" already carries the definite article (لـ + ال)، فنزيل "ال" من
        // اسم الدور أولاً كي لا تتكرر إن كان الاسم مكتوباً بأداة التعريف أصلاً.
        $name = preg_replace('/^ال/u', '', $role->name);

        return "طلب مهمة من مدير الأصل لل{$name}";
    }

    public function canBeCompletedBy(User $user): bool
    {
        return $user->isAdmin() || $this->assigned_to === $user->id || $this->requested_by === $user->id;
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isPast()
            && $this->status !== TaskStatus::Completed;
    }

    public function nextOccurrenceDate(Carbon $from): Carbon
    {
        return match ($this->recurrence) {
            TaskRecurrence::Daily => $from->copy()->addDay(),
            TaskRecurrence::Weekly => $from->copy()->addWeek(),
            TaskRecurrence::Monthly => $from->copy()->addMonthNoOverflow(),
            default => $from->copy(),
        };
    }

    public function scopeInstances(Builder $query): Builder
    {
        return $query->where('is_template', false);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'priority' => TaskPriority::class,
            'status' => TaskStatus::class,
            'recurrence' => TaskRecurrence::class,
            'due_date' => 'date',
            'next_run_date' => 'date',
            'is_template' => 'boolean',
            'is_active' => 'boolean',
            'notify_by_email' => 'boolean',
            'completed_at' => 'datetime',
            'requested_module' => Module::class,
        ];
    }
}
