<?php

namespace App\Models;

use App\Models\Concerns\BelongsToDocumentType;
use App\Models\Concerns\HasReferenceNumber;
use App\Models\Concerns\LogsArchiveActivity;
use Database\Factories\GeoDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeoDocument extends Model
{
    use BelongsToDocumentType;

    /** @use HasFactory<GeoDocumentFactory> */
    use HasFactory;

    use HasReferenceNumber;
    use LogsArchiveActivity;

    protected $fillable = [
        'reference_number',
        'type',
        'sites',
        'title',
        'drawing_number',
        'document_date',
        'file_path',
        'notes',
    ];

    public static function referencePrefix(): string
    {
        return 'خريطة';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sites' => 'array',
            'document_date' => 'date',
        ];
    }

    public static function documentTypeClass(): string
    {
        return GeoDocumentType::class;
    }
}
