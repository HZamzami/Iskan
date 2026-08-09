<?php

namespace App\Models;

use App\Models\Concerns\BelongsToDocumentType;
use App\Models\Concerns\HasReferenceNumber;
use App\Models\Concerns\LogsArchiveActivity;
use Database\Factories\ContractDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractDocument extends Model
{
    use BelongsToDocumentType;

    /** @use HasFactory<ContractDocumentFactory> */
    use HasFactory;

    use HasReferenceNumber;
    use LogsArchiveActivity;

    protected $fillable = [
        'reference_number',
        'type',
        'sites',
        'title',
        'contract_number',
        'contracting_party',
        'start_date',
        'end_date',
        'document_date',
        'file_path',
        'notes',
    ];

    public static function referencePrefix(): string
    {
        return 'عقد';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sites' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'document_date' => 'date',
        ];
    }

    public static function documentTypeClass(): string
    {
        return ContractDocumentType::class;
    }
}
