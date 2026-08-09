<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * سياسة عامة لجداول القيم الإدارية (المواقع، المجموعات، وأنواع المستندات)؛
 * حكر على مدير النظام الذي يمر عبر Gate::before، لذا تُرفض جميع الصلاحيات
 * هنا لغير المدير.
 */
class AdminOnlyPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Model $record): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Model $record): bool
    {
        return false;
    }

    public function delete(User $user, Model $record): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
