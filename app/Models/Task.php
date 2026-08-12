<?php

namespace App\Models;

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

    public function requestTypeLabel(): string
    {
        return match ($this->assignedRole?->slug) {
            'asset_manager' => 'طلب مهمة من داخلية',
            'consultant' => 'طلب مهمة من المالك للإستشاري',
            'contractor' => 'طلب مهمة من المالك للمقاول',
            default => '—',
        };
    }

    public function canBeCompletedBy(User $user): bool
    {
        return $user->isAdmin() || $this->assigned_to === $user->id;
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
        ];
    }
}
