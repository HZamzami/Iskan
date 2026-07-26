<?php

namespace App\Filament\Resources\Users;

use App\Enums\AccessLevel;
use App\Enums\Module;
use App\Enums\Site;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Schemas\UserInfolist;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'الإدارة';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'مستخدم';

    protected static ?string $pluralModelLabel = 'المستخدمون';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    /**
     * مزامنة دور المستخدم وصلاحياته المباشرة من حقول النموذج الافتراضية
     * (is_admin, modules.*, sites).
     *
     * @param  array<string, mixed>  $data
     */
    public static function syncAccess(User $user, array $data): void
    {
        $isAdmin = (bool) ($data['is_admin'] ?? false);

        $user->syncRoles($isAdmin ? ['admin'] : []);

        $permissions = [];

        if (! $isAdmin) {
            foreach ($data['modules'] ?? [] as $module => $level) {
                if (filled($level)) {
                    $level = $level instanceof AccessLevel ? $level->value : $level;
                    $permissions[] = "{$module}.{$level}";
                }
            }

            foreach ($data['sites'] ?? [] as $site) {
                $site = $site instanceof Site ? $site->value : $site;
                $permissions[] = "site.{$site}";
            }
        }

        $user->syncPermissions($permissions);
    }

    /**
     * تحويل دور المستخدم وصلاحياته المباشرة إلى حالة حقول النموذج.
     *
     * @return array{is_admin: bool, modules: array<string, string|null>, sites: array<int, string>}
     */
    public static function accessFormState(User $user): array
    {
        $user->load('permissions');

        $modules = [];

        foreach (Module::cases() as $module) {
            $modules[$module->value] = null;

            foreach ([AccessLevel::Edit, AccessLevel::Write, AccessLevel::Read] as $level) {
                if ($user->permissions->contains('name', $module->permission($level))) {
                    $modules[$module->value] = $level->value;
                    break;
                }
            }
        }

        $sites = array_values(array_filter(
            array_map(fn (Site $site): string => $site->value, Site::cases()),
            fn (string $site): bool => $user->permissions->contains('name', "site.{$site}"),
        ));

        return [
            'is_admin' => $user->isAdmin(),
            'modules' => $modules,
            'sites' => $sites,
        ];
    }
}
