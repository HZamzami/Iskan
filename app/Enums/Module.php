<?php

namespace App\Enums;

use App\Filament\Resources\ContractDocuments\ContractDocumentResource;
use App\Filament\Resources\ContractualRequirements\ContractualRequirementResource;
use App\Filament\Resources\Correspondences\CorrespondenceResource;
use App\Filament\Resources\FinancialFlows\FinancialFlowResource;
use App\Filament\Resources\GeoDocuments\GeoDocumentResource;
use App\Filament\Resources\Minutes\MinuteResource;
use App\Filament\Resources\PeriodicReports\PeriodicReportResource;
use App\Models\ContractDocument;
use App\Models\ContractDocumentType;
use App\Models\ContractualRequirement;
use App\Models\ContractualRequirementType;
use App\Models\Correspondence;
use App\Models\FinancialFlow;
use App\Models\FinancialFlowType;
use App\Models\GeoDocument;
use App\Models\GeoDocumentType;
use App\Models\Minute;
use App\Models\MinuteType;
use App\Models\PeriodicReport;
use App\Models\PeriodicReportType;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

enum Module: string implements HasLabel
{
    case Correspondences = 'correspondences';
    case ContractDocuments = 'contract_documents';
    case Minutes = 'minutes';
    case FinancialFlows = 'financial_flows';
    case ContractualRequirements = 'contractual_requirements';
    case PeriodicReports = 'periodic_reports';
    case GeoDocuments = 'geo_documents';

    public function getLabel(): string
    {
        return match ($this) {
            self::Correspondences => 'المعاملات الإدارية',
            self::ContractDocuments => 'المستندات التعاقدية',
            self::Minutes => 'المحاضر',
            self::FinancialFlows => 'التدفقات المالية',
            self::ContractualRequirements => 'المتطلبات التعاقدية',
            self::PeriodicReports => 'التقارير الدورية',
            self::GeoDocuments => 'الخرائط والرسومات الجيومكانية',
        };
    }

    /**
     * @return class-string<Model>
     */
    public function modelClass(): string
    {
        return match ($this) {
            self::Correspondences => Correspondence::class,
            self::ContractDocuments => ContractDocument::class,
            self::Minutes => Minute::class,
            self::FinancialFlows => FinancialFlow::class,
            self::ContractualRequirements => ContractualRequirement::class,
            self::PeriodicReports => PeriodicReport::class,
            self::GeoDocuments => GeoDocument::class,
        };
    }

    public function isSiteScoped(): bool
    {
        return $this !== self::Correspondences;
    }

    /**
     * @return class-string<Model>|null
     */
    public function typeModelClass(): ?string
    {
        return match ($this) {
            self::Correspondences => null,
            self::ContractDocuments => ContractDocumentType::class,
            self::Minutes => MinuteType::class,
            self::FinancialFlows => FinancialFlowType::class,
            self::ContractualRequirements => ContractualRequirementType::class,
            self::PeriodicReports => PeriodicReportType::class,
            self::GeoDocuments => GeoDocumentType::class,
        };
    }

    /**
     * @return class-string<\Filament\Resources\Resource>
     */
    public function resourceClass(): string
    {
        return match ($this) {
            self::Correspondences => CorrespondenceResource::class,
            self::ContractDocuments => ContractDocumentResource::class,
            self::Minutes => MinuteResource::class,
            self::FinancialFlows => FinancialFlowResource::class,
            self::ContractualRequirements => ContractualRequirementResource::class,
            self::PeriodicReports => PeriodicReportResource::class,
            self::GeoDocuments => GeoDocumentResource::class,
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Correspondences => Heroicon::OutlinedEnvelope,
            self::ContractDocuments => Heroicon::OutlinedDocumentDuplicate,
            self::Minutes => Heroicon::OutlinedClipboardDocumentList,
            self::FinancialFlows => Heroicon::OutlinedBanknotes,
            self::ContractualRequirements => Heroicon::OutlinedClipboardDocumentCheck,
            self::PeriodicReports => Heroicon::OutlinedChartBar,
            self::GeoDocuments => Heroicon::OutlinedMap,
        };
    }

    public function createLabel(): string
    {
        return match ($this) {
            self::Correspondences => 'معاملة جديدة',
            self::ContractDocuments => 'مستند تعاقدي جديد',
            self::Minutes => 'محضر جديد',
            self::FinancialFlows => 'تدفق مالي جديد',
            self::ContractualRequirements => 'متطلب تعاقدي جديد',
            self::PeriodicReports => 'تقرير دوري جديد',
            self::GeoDocuments => 'رسم جيومكاني جديد',
        };
    }

    public function permission(AccessLevel $level): string
    {
        return "{$this->value}.{$level->value}";
    }
}
