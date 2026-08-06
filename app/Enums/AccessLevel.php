<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AccessLevel: string implements HasLabel
{
    case Read = 'read';
    case Write = 'write';
    case Edit = 'edit';
    case Delete = 'delete';

    public function getLabel(): string
    {
        return match ($this) {
            self::Read => 'قراءة',
            self::Write => 'إضافة',
            self::Edit => 'تعديل',
            self::Delete => 'حذف',
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
            self::Delete => 4,
        };
    }

    public function covers(self $level): bool
    {
        return $this->rank() >= $level->rank();
    }
}
