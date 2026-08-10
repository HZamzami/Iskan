<?php

namespace App\Models;

use App\Models\Concerns\IsLookupModel;
use Database\Factories\RoleFactory;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * دور المستخدم في سير الاعتماد (مقاول/استشاري/مدير الأصل، بالإضافة لأي أدوار
 * أخرى يضيفها المدير). مستقل تماماً عن Entity (المستخدمة حصراً لجهات
 * المراسلات) — لا علاقة بينهما إطلاقاً، ويتصل المستخدم بهذا النموذج مباشرة
 * دون طبقة "جهة" وسيطة. الجدول باسم `roles_lookup` لتفادي التعارض مع جدول
 * `roles` الخاص بحزمة spatie/laravel-permission.
 */
class Role extends Model implements HasLabel
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory, IsLookupModel;

    /**
     * الأدوار الثلاثة الأساسية لسير الاعتماد — لا يمكن حذفها مهما كانت غير
     * مستخدَمة، لأن مدير الأصل تحديداً مطلوب دوماً للاعتماد النهائي
     * (انظر HasWorkflow::canApprove()). تُزرع تلقائياً عبر الترحيلات، لا
     * الـ Seeder وحده، لضمان وجودها في الإنتاج أيضاً دون تدخل يدوي.
     *
     * @var array<int, string>
     */
    public const CORE_SLUGS = ['contractor', 'consultant', 'asset_manager'];

    protected $table = 'roles_lookup';

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function usageCount(): int
    {
        return $this->users()->count();
    }

    public function isCore(): bool
    {
        return in_array($this->slug, self::CORE_SLUGS, true);
    }

    public function isInUse(): bool
    {
        return $this->isCore() || $this->usageCount() > 0;
    }
}
