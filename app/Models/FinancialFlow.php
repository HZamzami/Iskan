<?php

namespace App\Models;

use App\Enums\FinancialFlowType;
use App\Enums\Site;
use App\Models\Concerns\HasReferenceNumber;
use Database\Factories\FinancialFlowFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialFlow extends Model
{
    /** @use HasFactory<FinancialFlowFactory> */
    use HasFactory;

    use HasReferenceNumber;

    protected $fillable = [
        'reference_number',
        'type',
        'site',
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
            'type' => FinancialFlowType::class,
            'site' => Site::class,
            'period_month' => 'date',
            'document_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }
}
