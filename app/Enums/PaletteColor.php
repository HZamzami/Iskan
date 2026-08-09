<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * مجموعة الألوان المتاحة لتلوين الأنواع والمواقع في الواجهة؛ تُستخدم كخيارات
 * ثابتة لحقل اللون بدلاً من إدخال نصي حر، وكمصدر وحيد لقيم اللون الست عشرية
 * اللازمة خارج مكوّنات Filament (مثل الرسوم البيانية).
 */
enum PaletteColor: string implements HasColor, HasLabel
{
    case Primary = 'primary';
    case Info = 'info';
    case Success = 'success';
    case Warning = 'warning';
    case Danger = 'danger';
    case Gray = 'gray';

    public function getLabel(): string
    {
        return match ($this) {
            self::Primary => 'كهرماني',
            self::Info => 'أزرق',
            self::Success => 'أخضر',
            self::Warning => 'برتقالي',
            self::Danger => 'أحمر',
            self::Gray => 'رمادي',
        };
    }

    public function getColor(): string
    {
        return $this->value;
    }

    public function hex(): string
    {
        return match ($this) {
            self::Primary, self::Warning => '#f59e0b',
            self::Info => '#3b82f6',
            self::Success => '#22c55e',
            self::Danger => '#ef4444',
            self::Gray => '#71717a',
        };
    }
}
