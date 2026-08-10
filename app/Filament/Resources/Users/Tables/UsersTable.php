<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_path')
                    ->label('الصورة')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn (User $record): string => Filament::getUserAvatarUrl($record)),
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),
                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('phone')
                    ->label('رقم الهاتف')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('role.name')
                    ->label('الدور')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('account_type')
                    ->label('نوع الحساب')
                    ->badge()
                    ->state(fn (User $record): string => $record->isAdmin() ? 'مدير النظام' : 'مستخدم')
                    ->color(fn (string $state): string => $state === 'مدير النظام' ? 'danger' : 'gray'),
                ToggleColumn::make('is_active')
                    ->label('نشط')
                    ->disabled(fn (User $record): bool => $record->is(Filament::auth()->user()) || self::isLastAdmin($record)),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->date('Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('الجميع')
                    ->trueLabel('نشط فقط')
                    ->falseLabel('غير نشط فقط'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn (User $record): bool => $record->is(Filament::auth()->user()))
                    ->before(function (DeleteAction $action, User $record): void {
                        if (self::isLastAdmin($record)) {
                            self::notifyLastAdmin();
                            $action->cancel();
                        }
                    }),
            ])
            ->emptyStateHeading('لا يوجد مستخدمون')
            ->emptyStateIcon(Heroicon::OutlinedUsers);
    }

    public static function isLastAdmin(User $record): bool
    {
        if (! $record->isAdmin() || ! $record->is_active) {
            return false;
        }

        return User::whereHas('roles', fn (Builder $query) => $query->where('name', 'admin'))
            ->where('is_active', true)
            ->count() === 1;
    }

    public static function notifyLastAdmin(): void
    {
        Notification::make()
            ->danger()
            ->title('لا يمكن إتمام العملية')
            ->body('لا يمكن حذف أو تخفيض صلاحية أو إلغاء تفعيل آخر مدير للنظام.')
            ->send();
    }
}
