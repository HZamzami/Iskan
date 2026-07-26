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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),
                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('role')
                    ->label('الدور')
                    ->badge()
                    ->state(fn (User $record): string => $record->isAdmin() ? 'مدير النظام' : 'مستخدم')
                    ->color(fn (string $state): string => $state === 'مدير النظام' ? 'danger' : 'gray'),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->date('Y/m/d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
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
        return $record->isAdmin() && User::role('admin')->count() === 1;
    }

    public static function notifyLastAdmin(): void
    {
        Notification::make()
            ->danger()
            ->title('لا يمكن إتمام العملية')
            ->body('لا يمكن حذف أو تخفيض صلاحية آخر مدير للنظام.')
            ->send();
    }
}
