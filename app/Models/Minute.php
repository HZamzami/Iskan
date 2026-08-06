<?php

namespace App\Models;

use App\Enums\MinuteType;
use App\Enums\Site;
use App\Models\Concerns\HasReferenceNumber;
use App\Models\Concerns\LogsArchiveActivity;
use Database\Factories\MinuteFactory;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Minute extends Model
{
    /** @use HasFactory<MinuteFactory> */
    use HasFactory;

    use HasReferenceNumber;
    use LogsArchiveActivity;

    protected $fillable = [
        'reference_number',
        'type',
        'sites',
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
            'sites' => AsEnumCollection::of(Site::class),
            'document_date' => 'date',
        ];
    }
}
