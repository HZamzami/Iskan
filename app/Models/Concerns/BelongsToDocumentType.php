<?php

namespace App\Models\Concerns;

use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * سلوك مشترك لنماذج سجلات الأرشيف الستة: ربط عمود "type" النصي بنموذج نوع
 * المستند المقابل عبر slug (وليس مفتاحاً أساسياً)، وتحويل عمود "sites"
 * (مصفوفة من slugs) إلى نماذج المواقع الفعلية عند الحاجة لعرضها.
 */
trait BelongsToDocumentType
{
    /**
     * @return class-string<Model>
     */
    abstract public static function documentTypeClass(): string;

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(static::documentTypeClass(), 'type', 'slug');
    }

    /**
     * @return Collection<int, Location>
     */
    public function siteLocations(): Collection
    {
        return Location::cached()->whereIn('slug', $this->sites ?? []);
    }

    public function scopeOfType(Builder $query, string $slug): Builder
    {
        return $query->where('type', $slug);
    }
}
