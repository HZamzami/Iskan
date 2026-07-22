<?php

namespace App\Models;

use App\Enums\GeoDocumentType;
use App\Enums\Site;
use App\Models\Concerns\HasReferenceNumber;
use Database\Factories\GeoDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeoDocument extends Model
{
    /** @use HasFactory<GeoDocumentFactory> */
    use HasFactory;

    use HasReferenceNumber;

    protected $fillable = [
        'reference_number',
        'type',
        'site',
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
            'type' => GeoDocumentType::class,
            'site' => Site::class,
            'document_date' => 'date',
        ];
    }
}
