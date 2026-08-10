<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\AccessLevel;
use App\Enums\Module;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'phone', 'role_id', 'avatar_path', 'is_active', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, LogsActivity, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * أعلى مستوى وصول ممنوح للمستخدم على الوحدة، أو null إذا لم تُمنح.
     */
    public function accessLevelFor(Module $module): ?AccessLevel
    {
        if ($this->isAdmin()) {
            return AccessLevel::Delete;
        }

        foreach ([AccessLevel::Delete, AccessLevel::Edit, AccessLevel::Write, AccessLevel::Read] as $level) {
            if ($this->permissions->contains('name', $module->permission($level))) {
                return $level;
            }
        }

        return null;
    }

    public function hasModuleAccess(Module $module, AccessLevel $minimum): bool
    {
        return $this->accessLevelFor($module)?->covers($minimum) ?? false;
    }

    /**
     * المواقع المسموح بها للمستخدم، أو null لمدير النظام (غير مقيّد).
     * المصفوفة الفارغة تعني الاطلاع على السجلات العامة فقط. الاعتماد على
     * Location::cached() (محفوظة على مستوى الطلب) يكفي لتفادي استعلامات
     * متكررة دون خطر تخزين نتيجة قديمة إذا تغيّرت صلاحيات المستخدم.
     *
     * @return array<int, Location>|null
     */
    public function allowedSites(): ?array
    {
        if ($this->isAdmin()) {
            return null;
        }

        return Location::cached()
            ->filter(fn (Location $location): bool => $this->permissions->contains('name', $location->permissionName()))
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>|null
     */
    public function allowedSiteSlugs(): ?array
    {
        $allowed = $this->allowedSites();

        return $allowed === null ? null : array_map(fn (Location $location): string => $location->slug, $allowed);
    }

    public function canAccessSite(Location|string $site): bool
    {
        $slug = $site instanceof Location ? $site->slug : $site;
        $allowed = $this->allowedSiteSlugs();

        return $allowed === null || in_array($slug, $allowed, true);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * الدور (مقاول/استشاري/مدير الأصل) الذي يشغله المستخدم في سير الاعتماد، أو null
     * إن لم يُحدَّد دوره بعد. مستقل تماماً عن Entity (جهات المراسلات) —
     * اتصال مباشر بلا طبقة وسيطة.
     */
    public function category(): ?string
    {
        return $this->role?->slug;
    }

    public function scopeOfCategory(Builder $query, string $slug): Builder
    {
        return $query->whereHas('role', fn (Builder $q): Builder => $q->where('slug', $slug));
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_path ? Storage::disk('public')->url($this->avatar_path) : null;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'phone', 'role_id', 'is_active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
