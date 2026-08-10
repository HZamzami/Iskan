<?php

namespace App\Models;

use App\Models\Concerns\IsLookupModel;
use Database\Factories\RoleFactory;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * دور المستخدم في سير الاعتماد (مقاول/استشاري/مالك...، قابل للتوسعة من قِبل
 * المدير). مستقل تماماً عن Entity/EntityType (المستخدمتان حصراً لجهات
 * المراسلات) — لا علاقة بينهما إطلاقاً، ويتصل المستخدم بهذا النموذج مباشرة
 * دون طبقة "جهة" وسيطة. الجدول باسم `roles_lookup` لتفادي التعارض مع جدول
 * `roles` الخاص بحزمة spatie/laravel-permission.
 */
class Role extends Model implements HasLabel
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory, IsLookupModel;

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

    public function isInUse(): bool
    {
        return $this->usageCount() > 0;
    }
}
