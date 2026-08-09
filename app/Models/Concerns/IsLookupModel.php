<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * سلوك مشترك لجداول القيم الإدارية القابلة للتخصيص (المواقع، المجموعات،
 * وأنواع المستندات): توليد slug فريد وثابت من الاسم عند الإنشاء، حمايته من
 * التعديل لاحقاً (لأنه المفتاح المستخدم في الأعمدة النصية القديمة)، ترتيب
 * وتفعيل قياسيان، وتخزين مؤقت على مستوى الطلب لتفادي استعلامات متكررة.
 */
trait IsLookupModel
{
    protected static ?Collection $lookupCache = null;

    public static function bootIsLookupModel(): void
    {
        static::creating(function (self $model): void {
            if (blank($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->name);
            }
        });

        static::updating(function (self $model): void {
            if ($model->isDirty('slug')) {
                $model->slug = $model->getOriginal('slug');
            }
        });

        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
    }

    protected static function generateUniqueSlug(?string $name): string
    {
        $base = Str::slug((string) $name, '_');

        if (blank($base)) {
            $base = Str::snake(class_basename(static::class));
        }

        $base = Str::limit($base, 90, '');

        $slug = $base;
        $suffix = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}_{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return Collection<int, static>
     */
    public static function cached(): Collection
    {
        return static::$lookupCache ??= static::query()->ordered()->get();
    }

    public static function forgetCache(): void
    {
        static::$lookupCache = null;
    }

    public function getLabel(): string
    {
        return $this->name;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }
}
