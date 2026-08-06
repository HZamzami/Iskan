<?php

namespace App\Models;

use Database\Factories\EntityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entity extends Model
{
    /** @use HasFactory<EntityFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'entity_type_id',
    ];

    public function correspondences(): HasMany
    {
        return $this->hasMany(Correspondence::class);
    }

    public function entityType(): BelongsTo
    {
        return $this->belongsTo(EntityType::class);
    }
}
