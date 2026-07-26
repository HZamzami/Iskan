<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AccessLevel: string implements HasLabel
{
    case Read = 'read';
    case Write = 'write';
    case Edit = 'edit';

    public function getLabel(): string
    {
        return match ($this) {
            self::Read => 'قراءة',
            self::Write => 'إضافة',
            self::Edit => 'تعديل',
        };
    }

    /**
     * ترتيب المستوى؛ كل مستوى أعلى يشمل صلاحيات المستويات الأدنى.
     */
    public function rank(): int
    {
        return match ($this) {
            self::Read => 1,
            self::Write => 2,
            self::Edit => 3,
        };
    }

    public function covers(self $level): bool
    {
        return $this->rank() >= $level->rank();
    }
}
