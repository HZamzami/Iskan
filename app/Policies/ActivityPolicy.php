<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Activitylog\Models\Activity;

/**
 * سجل النشاط حكر على مدير النظام؛ يمر عبر Gate::before، لذا تُرفض جميع
 * الصلاحيات هنا لغير المدير.
 */
class ActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Activity $activity): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Activity $activity): bool
    {
        return false;
    }

    public function delete(User $user, Activity $activity): bool
    {
        return false;
    }
}
