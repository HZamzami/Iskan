<?php

namespace App\Models;

use App\Enums\MinuteType;
use App\Enums\Site;
use App\Models\Concerns\HasReferenceNumber;
use Database\Factories\MinuteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Minute extends Model
{
    /** @use HasFactory<MinuteFactory> */
    use HasFactory;

    use HasReferenceNumber;

    protected $fillable = [
        'reference_number',
        'type',
        'site',
        'title',
        'parties',
        'document_date',
        'file_path',
        'notes',
    ];

    public static function referencePrefix(): string
    {
        return 'محضر';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MinuteType::class,
            'site' => Site::class,
            'document_date' => 'date',
        ];
    }
}
