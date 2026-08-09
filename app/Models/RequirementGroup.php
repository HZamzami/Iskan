<?php

namespace App\Models;

use App\Models\Concerns\IsLookupModel;
use Database\Factories\RequirementGroupFactory;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequirementGroup extends Model implements HasColor, HasLabel
{
    /** @use HasFactory<RequirementGroupFactory> */
    use HasFactory, IsLookupModel;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'is_active',
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

    public function types(): HasMany
    {
        return $this->hasMany(ContractualRequirementType::class);
    }

    public function isInUse(): bool
    {
        return $this->types()->exists();
    }
}
