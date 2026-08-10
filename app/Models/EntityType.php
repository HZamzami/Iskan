<?php

namespace App\Models;

use App\Models\Concerns\IsLookupModel;
use Database\Factories\EntityTypeFactory;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EntityType extends Model implements HasLabel
{
    /** @use HasFactory<EntityTypeFactory> */
    use HasFactory, IsLookupModel;

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function entities(): HasMany
    {
        return $this->hasMany(Entity::class);
    }

    public function usageCount(): int
    {
        return $this->entities()->count();
    }

    public function isInUse(): bool
    {
        return $this->usageCount() > 0;
    }
}
