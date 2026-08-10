<?php

namespace App\Models;

use App\Enums\SiteScope;
use App\Models\Concerns\IsDocumentType;
use Database\Factories\ContractualRequirementTypeFactory;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractualRequirementType extends Model implements HasColor, HasLabel
{
    /** @use HasFactory<ContractualRequirementTypeFactory> */
    use HasFactory, IsDocumentType;

    protected $fillable = [
        'name',
        'slug',
        'short_label',
        'description',
        'color',
        'requirement_group_id',
        'site_scope',
        'sites',
        'accepted_extensions',
        'max_file_size',
        'is_active',
        'sort_order',
        'requires_workflow',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'site_scope' => SiteScope::class,
            'sites' => 'array',
            'accepted_extensions' => 'array',
            'is_active' => 'boolean',
            'requires_workflow' => 'boolean',
        ];
    }

    public static function documentModel(): string
    {
        return ContractualRequirement::class;
    }

    public static function supportsGroups(): bool
    {
        return true;
    }

    public function requirementGroup(): BelongsTo
    {
        return $this->belongsTo(RequirementGroup::class);
    }

    /**
     * لون النوع، أو لون المجموعة إن لم يُحدَّد لون خاص بالنوع.
     */
    public function getColor(): ?string
    {
        return $this->color ?? $this->requirementGroup?->color;
    }
}
