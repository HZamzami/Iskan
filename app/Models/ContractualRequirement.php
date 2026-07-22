<?php

namespace App\Models;

use App\Enums\ContractualRequirementType;
use App\Enums\Site;
use App\Models\Concerns\HasReferenceNumber;
use Database\Factories\ContractualRequirementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractualRequirement extends Model
{
    /** @use HasFactory<ContractualRequirementFactory> */
    use HasFactory;

    use HasReferenceNumber;

    protected $fillable = [
        'reference_number',
        'type',
        'site',
        'title',
        'period',
        'document_date',
        'file_path',
        'notes',
    ];

    public static function referencePrefix(): string
    {
        return 'متطلب';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ContractualRequirementType::class,
            'site' => Site::class,
            'period' => 'date',
            'document_date' => 'date',
        ];
    }
}
