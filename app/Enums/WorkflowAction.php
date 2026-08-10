<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

/**
 * نوع الانتقال المسجَّل في WorkflowTransition. لا يوجد إجراء منفصل لكل دور
 * (مثل "ترحيل للمالك")، لأن السلسلة مرنة وغير محدودة الطول — الدور المستهدف
 * يُسجَّل بشكل منفصل عبر role_id في نفس السجل.
 */
enum WorkflowAction: string implements HasColor, HasIcon, HasLabel
{
    case Submit = 'submit';
    case Forward = 'forward';
    case Return = 'return';
    case Approve = 'approve';

    public function getLabel(): string
    {
        return match ($this) {
            self::Submit => 'إرسال',
            self::Forward => 'ترحيل',
            self::Return => 'إعادة',
            self::Approve => 'اعتماد نهائي',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Submit => 'info',
            self::Forward => 'warning',
            self::Return => 'danger',
            self::Approve => 'success',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Submit => Heroicon::PaperAirplane,
            self::Forward => Heroicon::ArrowRight,
            self::Return => Heroicon::ArrowUturnLeft,
            self::Approve => Heroicon::CheckCircle,
        };
    }
}
