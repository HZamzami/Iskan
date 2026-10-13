<?php

namespace App\Policies;

use App\Enums\AccessLevel;
use App\Enums\Module;
use App\Models\Concerns\HasWorkflow;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class ModulePolicy
{
    abstract protected function module(): Module;

    /**
     * من يحمل سجلاً مُرحَّلاً إليه يدخل الوحدة حتى بلا صلاحية عليها، لكن
     * HasSiteRestrictedQuery يقصر ما يراه على السجلات التي بعهدته فقط.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasModuleAccess($user) || $this->holdsRecordsInModule($user);
    }

    public function view(User $user, Model $record): bool
    {
        if ($this->isCurrentAssignee($user, $record)) {
            return true;
        }

        return $this->hasModuleAccess($user) && $this->canAccessRecordSite($user, $record);
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

    private function hasModuleAccess(User $user): bool
    {
        return $user->accessLevelFor($this->module()) !== null;
    }

    private function holdsRecordsInModule(User $user): bool
    {
        $modelClass = $this->module()->modelClass();

        return in_array(HasWorkflow::class, class_uses_recursive($modelClass), true)
            && $modelClass::query()->where('assigned_to', $user->id)->exists();
    }

    /**
     * من رُحِّل إليه السجل يجب أن يتمكن من فتحه للتصرف فيه، حتى لو لم تُمنح له
     * صلاحية الوحدة أو موقع السجل.
     */
    private function isCurrentAssignee(User $user, Model $record): bool
    {
        return $record->getAttribute('assigned_to') !== null
            && (int) $record->getAttribute('assigned_to') === $user->id;
    }

    private function canAccessRecordSite(User $user, Model $record): bool
    {
        if (! $this->module()->isSiteScoped() || blank($record->sites)) {
            return true;
        }

        return collect($record->sites)->contains(fn (string $slug): bool => $user->canAccessSite($slug));
    }
}
