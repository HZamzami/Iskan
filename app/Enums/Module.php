<?php

namespace App\Enums;

use App\Models\ContractDocument;
use App\Models\ContractualRequirement;
use App\Models\Correspondence;
use App\Models\FinancialFlow;
use App\Models\GeoDocument;
use App\Models\Minute;
use App\Models\PeriodicReport;
use Filament\Support\Contracts\HasLabel;
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

    public function permission(AccessLevel $level): string
    {
        return "{$this->value}.{$level->value}";
    }
}
