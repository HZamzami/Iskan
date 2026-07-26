<?php

namespace App\Models;

use App\Enums\ContractDocumentType;
use App\Enums\Site;
use App\Models\Concerns\HasReferenceNumber;
use App\Models\Concerns\LogsArchiveActivity;
use Database\Factories\ContractDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractDocument extends Model
{
    /** @use HasFactory<ContractDocumentFactory> */
    use HasFactory;

    use HasReferenceNumber;
    use LogsArchiveActivity;

    protected $fillable = [
        'reference_number',
        'type',
        'site',
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
            'type' => ContractDocumentType::class,
            'site' => Site::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'document_date' => 'date',
        ];
    }
}
