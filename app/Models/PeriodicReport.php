<?php

namespace App\Models;

use App\Enums\PeriodicReportType;
use App\Enums\Site;
use App\Models\Concerns\HasReferenceNumber;
use App\Models\Concerns\LogsArchiveActivity;
use Database\Factories\PeriodicReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodicReport extends Model
{
    /** @use HasFactory<PeriodicReportFactory> */
    use HasFactory;

    use HasReferenceNumber;
    use LogsArchiveActivity;

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
        return 'تقرير';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PeriodicReportType::class,
            'site' => Site::class,
            'period' => 'date',
            'document_date' => 'date',
        ];
    }
}
