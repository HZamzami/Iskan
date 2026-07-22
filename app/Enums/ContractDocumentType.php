<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ContractDocumentType: string implements HasColor, HasLabel
{
    case ConsultantContract = 'consultant_contract';
    case OperationContract = 'operation_contract';
    case InternalProjectContract = 'internal_project_contract';

    public function getLabel(): string
    {
        return match ($this) {
            self::ConsultantContract => 'عقود الصيانة والتشغيل الإستشاري',
            self::OperationContract => 'عقود الصيانة والتشغيل',
            self::InternalProjectContract => 'عقود المشاريع الداخلية',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ConsultantContract => 'info',
            self::OperationContract => 'warning',
            self::InternalProjectContract => 'gray',
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
            self::OperationContract => Site::cases(),
            default => null,
        };
    }
}
