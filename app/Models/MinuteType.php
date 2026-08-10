<?php

namespace App\Models;

use App\Enums\SiteScope;
use App\Models\Concerns\IsDocumentType;
use Database\Factories\MinuteTypeFactory;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MinuteType extends Model implements HasColor, HasLabel
{
    /** @use HasFactory<MinuteTypeFactory> */
    use HasFactory, IsDocumentType;

    protected $fillable = [
        'name',
        'slug',
        'short_label',
        'description',
        'color',
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
        return Minute::class;
    }
}
