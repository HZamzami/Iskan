<?php

namespace App\Models;

use Database\Factories\EntityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entity extends Model
{
    /** @use HasFactory<EntityFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function correspondences(): HasMany
    {
        return $this->hasMany(Correspondence::class);
    }

    public function usageCount(): int
    {
        return $this->correspondences()->count();
    }

    public function isInUse(): bool
    {
        return $this->usageCount() > 0;
    }
}
