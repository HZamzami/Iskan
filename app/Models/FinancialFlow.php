<?php

namespace App\Models;

use App\Models\Concerns\BelongsToDocumentType;
use App\Models\Concerns\HasReferenceNumber;
use App\Models\Concerns\LogsArchiveActivity;
use Database\Factories\FinancialFlowFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialFlow extends Model
{
    use BelongsToDocumentType;

    /** @use HasFactory<FinancialFlowFactory> */
    use HasFactory;

    use HasReferenceNumber;
    use LogsArchiveActivity;

    protected $fillable = [
        'reference_number',
        'type',
        'sites',
        'title',
        'period_month',
        'amount',
        'document_date',
        'file_path',
        'notes',
    ];

    public static function referencePrefix(): string
    {
        return 'تدفق';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sites' => 'array',
            'period_month' => 'date',
            'document_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public static function documentTypeClass(): string
    {
        return FinancialFlowType::class;
    }
}
