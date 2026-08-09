<?php

namespace App\Models\Concerns;

use App\Enums\SiteScope;
use App\Models\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * سلوك مشترك لنماذج "نوع المستند" الستة (محضر، مستند جيومكاني، مستند
 * تعاقدي، تدفق مالي، متطلب تعاقدي، تقرير دوري): تحديد المواقع المسموحة
 * وامتدادات الملفات المقبولة، والربط بسجلات الوحدة عبر عمود "type" النصي.
 */
trait IsDocumentType
{
    use IsLookupModel;

    /**
     * @return class-string<Model>
     */
    abstract public static function documentModel(): string;

    public function documents(): HasMany
    {
        return $this->hasMany(static::documentModel(), 'type', 'slug');
    }

    public function isSiteScoped(): bool
    {
        return $this->site_scope !== SiteScope::None;
    }

    /**
     * @return Collection<int, Location>|null
     */
    public function allowedLocations(): ?Collection
    {
        return match ($this->site_scope) {
            SiteScope::None => null,
            SiteScope::All => Location::cached(),
            SiteScope::Custom => Location::cached()->whereIn('slug', $this->sites ?? []),
        };
    }

    /**
     * @return array<int, string>
     */
    public function acceptedExtensions(): array
    {
        return filled($this->accepted_extensions) ? $this->accepted_extensions : ['pdf'];
    }

    public function maxFileSizeKb(): int
    {
        return $this->max_file_size ?? 20480;
    }

    public function usageCount(): int
    {
        return $this->documents()->count();
    }

    public function isInUse(): bool
    {
        return $this->usageCount() > 0;
    }

    public static function supportsGroups(): bool
    {
        return false;
    }

    /**
     * خيارات الاختيار للاستخدام في حقل Select الخاص بنوع المستند: الأنواع
     * النشطة مرتبة، بالإضافة إلى النوع الحالي إن كان غير نشط حتى لا يختفي
     * من نموذج تعديل سجل قديم يستخدمه.
     *
     * @return array<string, string>
     */
    public static function selectOptions(?string $currentSlug = null): array
    {
        $query = static::query()->ordered();

        if (filled($currentSlug)) {
            $query->where(fn ($q) => $q->active()->orWhere('slug', $currentSlug));
        } else {
            $query->active();
        }

        return $query->pluck('name', 'slug')->all();
    }
}
