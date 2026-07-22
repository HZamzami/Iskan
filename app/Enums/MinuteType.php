<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MinuteType: string implements HasColor, HasLabel
{
    case WeeklyMeeting = 'weekly_meeting';
    case ProjectHandover = 'project_handover';
    case ServiceProvider = 'service_provider';
    case ServiceProviderReReceipt = 'service_provider_re_receipt';
    case DamagesExtensions = 'damages_extensions';
    case AssetRemoval = 'asset_removal';
    case AssetTagging = 'asset_tagging';
    case SparePartsHandover = 'spare_parts_handover';
    case AcSterilizationReceipt = 'ac_sterilization_receipt';

    public function getLabel(): string
    {
        return match ($this) {
            self::WeeklyMeeting => 'محاضر الاجتماعات الأسبوعية',
            self::ProjectHandover => 'محاضر تسليم واستلام المشاريع',
            self::ServiceProvider => 'محاضر شركات تقديم الخدمة',
            self::ServiceProviderReReceipt => 'محاضر إعادة استلام من شركات تقديم الخدمة',
            self::DamagesExtensions => 'محاضر التلفيات والتمديدات',
            self::AssetRemoval => 'محاضر إزالة الأصول من المواقع من قبل شركات تقديم الخدمة',
            self::AssetTagging => 'محاضر تسليم علامات ترميز الأصول',
            self::SparePartsHandover => 'محاضر تسليم واستلام قطع الغيار',
            self::AcSterilizationReceipt => 'محضر استلام أقراص تعقيم المكيفات',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::WeeklyMeeting => 'info',
            self::ProjectHandover => 'success',
            self::ServiceProvider => 'warning',
            self::ServiceProviderReReceipt => 'gray',
            self::DamagesExtensions => 'danger',
            self::AssetRemoval => 'primary',
            self::AssetTagging => 'info',
            self::SparePartsHandover => 'warning',
            self::AcSterilizationReceipt => 'success',
        };
    }

    /**
     * المواقع التي ينطبق عليها هذا النوع، أو null إذا لم يكن مرتبطاً بالمواقع.
     *
     * @return array<int, Site>|null
     */
    public function sites(): ?array
    {
        return match ($this) {
            self::WeeklyMeeting => Site::cases(),
            self::AssetTagging, self::SparePartsHandover => Site::campSites(),
            default => null,
        };
    }
}
