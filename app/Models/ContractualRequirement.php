<?php

namespace App\Models;

use App\Models\Concerns\BelongsToDocumentType;
use App\Models\Concerns\HasReferenceNumber;
use App\Models\Concerns\LogsArchiveActivity;
use Database\Factories\ContractualRequirementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractualRequirement extends Model
{
    use BelongsToDocumentType;

    /** @use HasFactory<ContractualRequirementFactory> */
    use HasFactory;

    use HasReferenceNumber;
    use LogsArchiveActivity;

    protected $fillable = [
        'reference_number',
        'type',
        'sites',
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
            'sites' => 'array',
            'period' => 'date',
            'document_date' => 'date',
        ];
    }

    public static function documentTypeClass(): string
    {
        return ContractualRequirementType::class;
    }
}
