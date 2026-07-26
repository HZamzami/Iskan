<?php

namespace App\Policies;

use App\Models\User;

/**
 * إدارة المستخدمين حكر على مدير النظام؛ يمر عبر Gate::before، لذا تُرفض جميع
 * الصلاحيات هنا لغير المدير.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, User $record): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, User $record): bool
    {
        return false;
    }

    public function delete(User $user, User $record): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
