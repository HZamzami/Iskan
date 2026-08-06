<?php

namespace App\Policies;

use App\Enums\AccessLevel;
use App\Enums\Module;
use App\Enums\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class ModulePolicy
{
    abstract protected function module(): Module;

    public function viewAny(User $user): bool
    {
        return $user->accessLevelFor($this->module()) !== null;
    }

    public function view(User $user, Model $record): bool
    {
        return $this->viewAny($user) && $this->canAccessRecordSite($user, $record);
    }

    public function create(User $user): bool
    {
        return $user->hasModuleAccess($this->module(), AccessLevel::Write);
    }

    public function update(User $user, Model $record): bool
    {
        return $user->hasModuleAccess($this->module(), AccessLevel::Edit)
            && $this->canAccessRecordSite($user, $record);
    }

    public function delete(User $user, Model $record): bool
    {
        return $user->hasModuleAccess($this->module(), AccessLevel::Delete)
            && $this->canAccessRecordSite($user, $record);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasModuleAccess($this->module(), AccessLevel::Delete);
    }

    private function canAccessRecordSite(User $user, Model $record): bool
    {
        if (! $this->module()->isSiteScoped() || blank($record->sites)) {
            return true;
        }

        return collect($record->sites)->contains(fn (Site $site): bool => $user->canAccessSite($site));
    }
}
