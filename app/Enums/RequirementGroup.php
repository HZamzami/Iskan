<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RequirementGroup: string implements HasColor, HasLabel
{
    case MonthlyCounts = 'monthly_counts';
    case OperationDocs = 'operation_docs';
    case ManagementPlans = 'management_plans';

    public function getLabel(): string
    {
        return match ($this) {
            self::MonthlyCounts => 'قوائم الحصر الشهرية',
            self::OperationDocs => 'وثائق التشغيل',
            self::ManagementPlans => 'الخطط الإدارية',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::MonthlyCounts => 'warning',
            self::OperationDocs => 'info',
            self::ManagementPlans => 'success',
        };
    }

    /**
     * أنواع المتطلبات التعاقدية التابعة لهذه المجموعة.
     *
     * @return array<int, ContractualRequirementType>
     */
    public function types(): array
    {
        return array_values(array_filter(
            ContractualRequirementType::cases(),
            fn (ContractualRequirementType $type): bool => $type->group() === $this,
        ));
    }
}
