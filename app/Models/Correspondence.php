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
        'sender_user_id',
        'recipient_user_id',
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

    public function senderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function recipientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function senderLabel(): ?string
    {
        return $this->senderUser?->name ?? $this->sender;
    }

    public function recipientLabel(): ?string
    {
        return $this->recipientUser?->name ?? $this->recipient;
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
