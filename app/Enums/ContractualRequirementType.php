<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ContractualRequirementType: string implements HasLabel
{
    case LaborCount = 'labor_count';
    case EquipmentCount = 'equipment_count';
    case SparePartsCount = 'spare_parts_count';
    case ToolsCount = 'tools_count';
    case ComponentsCount = 'components_count';
    case OrgStructure = 'org_structure';
    case Sop = 'sop';
    case CompletionCertificates = 'completion_certificates';
    case MasterPlan = 'master_plan';
    case JobPlan = 'job_plan';
    case QualityPlan = 'quality_plan';
    case RiskPlan = 'risk_plan';
    case HsePlan = 'hse_plan';
    case StakeholderPlan = 'stakeholder_plan';
    case CashFlowPlan = 'cash_flow_plan';
    case ElementalCostPlan = 'elemental_cost_plan';
    case LogisticsPlan = 'logistics_plan';
    case InterfacePlan = 'interface_plan';

    public function getLabel(): string
    {
        return match ($this) {
            self::LaborCount => 'حصر العمالة الشهري',
            self::EquipmentCount => 'حصر المعدات الشهري',
            self::SparePartsCount => 'حصر قطع الغيار الشهري',
            self::ToolsCount => 'حصر الأدوات الشهري',
            self::ComponentsCount => 'حصر المكونات',
            self::OrgStructure => 'الهيكل التنظيمي للاستشاري والمقاول',
            self::Sop => 'إجراءات التشغيل الموحد (SOP)',
            self::CompletionCertificates => 'شهادات إنجاز الأعمال',
            self::MasterPlan => 'الجداول الزمنية (Master Plan)',
            self::JobPlan => 'بنود وخطوات عمل الصيانات (Job Plan)',
            self::QualityPlan => 'خطة إدارة الجودة',
            self::RiskPlan => 'خطة إدارة المخاطر',
            self::HsePlan => 'خطة إدارة السلامة والصحة المهنية',
            self::StakeholderPlan => 'خطة إدارة أصحاب المصلحة (المعنيين)',
            self::CashFlowPlan => 'خطة التدفقات النقدية',
            self::ElementalCostPlan => 'خطط التكاليف المرحلية والنوعية (Elemental Cost Plan)',
            self::LogisticsPlan => 'خطة إدارة اللوجستيات',
            self::InterfacePlan => 'خطة إدارة التداخلات (Interface Management Plan)',
        };
    }

    /**
     * المجموعة التي يتبع لها هذا النوع.
     */
    public function group(): RequirementGroup
    {
        return match ($this) {
            self::LaborCount,
            self::EquipmentCount,
            self::SparePartsCount,
            self::ToolsCount,
            self::ComponentsCount => RequirementGroup::MonthlyCounts,

            self::OrgStructure,
            self::Sop,
            self::CompletionCertificates,
            self::MasterPlan,
            self::JobPlan => RequirementGroup::OperationDocs,

            self::QualityPlan,
            self::RiskPlan,
            self::HsePlan,
            self::StakeholderPlan,
            self::CashFlowPlan,
            self::ElementalCostPlan,
            self::LogisticsPlan,
            self::InterfacePlan => RequirementGroup::ManagementPlans,
        };
    }

    /**
     * المواقع التي ينطبق عليها هذا النوع، أو null إذا لم يكن مرتبطاً بالمواقع.
     *
     * @return array<int, Site>|null
     */
    public function sites(): ?array
    {
        return match ($this->group()) {
            RequirementGroup::ManagementPlans => Site::campSites(),
            default => Site::cases(),
        };
    }

    /**
     * امتدادات الملفات المسموحة لهذا النوع.
     *
     * @return array<int, string>
     */
    public function acceptedExtensions(): array
    {
        return match ($this) {
            self::Sop => ['pdf', 'doc', 'docx'],
            self::MasterPlan, self::JobPlan => ['pdf', 'xls', 'xlsx'],
            default => ['pdf'],
        };
    }
}
