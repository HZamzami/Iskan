<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * توليد رقم مرجعي تلقائي بنمط {بادئة}-{سنة}-{تسلسل} عند الإنشاء،
 * على أن يعرّف النموذج المستخدم البادئة عبر referencePrefix().
 */
trait HasReferenceNumber
{
    abstract public static function referencePrefix(): string;

    protected static function bootHasReferenceNumber(): void
    {
        static::creating(function (Model $model): void {
            if (blank($model->reference_number)) {
                $model->reference_number = self::generateReferenceNumber();
            }
        });
    }

    public static function generateReferenceNumber(): string
    {
        $year = now()->year;

        $sequence = self::query()
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('%s-%d-%04d', static::referencePrefix(), $year, $sequence);
    }
}
