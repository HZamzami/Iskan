<?php

namespace App\Models;

use App\Enums\CorrespondenceDirection;
use App\Enums\CorrespondenceStatus;
use App\Models\Concerns\HasComments;
use App\Models\Concerns\LogsArchiveActivity;
use Database\Factories\CorrespondenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Correspondence extends Model
{
    use HasComments;

    /** @use HasFactory<CorrespondenceFactory> */
    use HasFactory;

    use LogsArchiveActivity;

    protected $fillable = [
        'reference_number',
        'subject',
        'direction',
        'status',
        'sender',
        'recipient',
        'entity_id',
        'document_date',
        'file_path',
        'notes',
    ];

    protected static function booted(): void
    {
        static::creating(function (Correspondence $correspondence): void {
            if (blank($correspondence->reference_number)) {
                $correspondence->reference_number = self::generateReferenceNumber($correspondence->direction);
            }
        });
    }

    public static function generateReferenceNumber(CorrespondenceDirection $direction): string
    {
        $year = now()->year;

        $sequence = self::query()
            ->where('direction', $direction)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('%s-%d-%04d', $direction->referencePrefix(), $year, $sequence);
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'direction' => CorrespondenceDirection::class,
            'status' => CorrespondenceStatus::class,
            'document_date' => 'date',
        ];
    }
}
