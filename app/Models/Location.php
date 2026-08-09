<?php

namespace App\Models;

use App\Enums\LocationIcon;
use App\Enums\Module;
use App\Models\Concerns\IsLookupModel;
use Database\Factories\LocationFactory;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model implements HasColor, HasIcon, HasLabel
{
    /** @use HasFactory<LocationFactory> */
    use HasFactory, IsLookupModel;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'icon',
        'contractor',
        'consultant',
        'asset_manager',
        'notes',
        'is_active',
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

    public function permissionName(): string
    {
        return "site.{$this->slug}";
    }

    public function getIcon(): Heroicon
    {
        return (LocationIcon::tryFrom($this->icon) ?? LocationIcon::MapPin)->getIcon();
    }

    /**
     * عدد السجلات والأنواع المرتبطة بهذا الموقع عبر جميع الوحدات، لمنع
     * الحذف إذا كان لا يزال مستخدماً وللعرض في صفحة التفاصيل.
     *
     * @return array<string, array{records: int, types: int}>
     */
    public function usageBreakdown(): array
    {
        $breakdown = [];

        foreach (Module::cases() as $module) {
            if (! $module->isSiteScoped()) {
                continue;
            }

            $typeClass = $module->typeModelClass();

            $breakdown[$module->value] = [
                'records' => $module->modelClass()::query()->whereJsonContains('sites', $this->slug)->count(),
                'types' => $typeClass
                    ? $typeClass::query()->where('site_scope', 'custom')->whereJsonContains('sites', $this->slug)->count()
                    : 0,
            ];
        }

        return $breakdown;
    }

    public function isInUse(): bool
    {
        foreach ($this->usageBreakdown() as $counts) {
            if ($counts['records'] > 0 || $counts['types'] > 0) {
                return true;
            }
        }

        return false;
    }
}
