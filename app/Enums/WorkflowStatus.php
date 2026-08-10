<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

/**
 * حالة سير الاعتماد على مستوى السجل: لا يوجد وضع "معاد"/"مُرحَّل" مستقل —
 * أي ترحيل أو إعادة يبقى ضمن حالة "قيد المراجعة" (Pending)، والتفاصيل
 * (لمن أُرسل، ومن أعاده) تُسجَّل في سجل الانتقالات (WorkflowTransition) بدلاً
 * من تضخيم هذه الحالة لتغطية كل تسلسل ممكن.
 */
enum WorkflowStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case Approved = 'approved';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'قيد المراجعة',
            self::Approved => 'معتمد نهائياً',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Pending => Heroicon::Clock,
            self::Approved => Heroicon::CheckCircle,
        };
    }
}
