<?php

namespace App\Models;

use Database\Factories\EntityTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EntityType extends Model
{
    /** @use HasFactory<EntityTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function entities(): HasMany
    {
        return $this->hasMany(Entity::class);
    }
}
