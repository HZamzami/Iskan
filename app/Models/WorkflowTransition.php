<?php

namespace App\Models;

use App\Enums\WorkflowAction;
use App\Enums\WorkflowStatus;
use Database\Factories\WorkflowTransitionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WorkflowTransition extends Model
{
    /** @use HasFactory<WorkflowTransitionFactory> */
    use HasFactory;

    protected $fillable = [
        'transitionable_type',
        'transitionable_id',
        'from_status',
        'to_status',
        'action',
        'actor_id',
        'entity_type_id',
        'assigned_to_id',
        'note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'from_status' => WorkflowStatus::class,
            'to_status' => WorkflowStatus::class,
            'action' => WorkflowAction::class,
        ];
    }

    public function transitionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function entityType(): BelongsTo
    {
        return $this->belongsTo(EntityType::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }
}
