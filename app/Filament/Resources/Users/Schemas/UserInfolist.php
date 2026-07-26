<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Module;
use App\Enums\Site;
use App\Models\User;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات المستخدم')
                    ->icon(Heroicon::User)
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('name')
                                ->label('الاسم')
                                ->columnSpan(1),
                            TextEntry::make('email')
                                ->label('البريد الإلكتروني')
                                ->copyable()
                                ->columnSpan(1),
                            TextEntry::make('role')
                                ->label('الدور')
                                ->badge()
                                ->state(fn (User $record): string => $record->isAdmin() ? 'مدير النظام' : 'مستخدم')
                                ->color(fn (string $state): string => $state === 'مدير النظام' ? 'danger' : 'gray')
                                ->columnSpan(1),
                            TextEntry::make('created_at')
                                ->label('تاريخ الإنشاء')
                                ->date('Y/m/d')
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('الصلاحيات')
                    ->icon(Heroicon::LockClosed)
                    ->columnSpanFull()
                    ->visible(fn (User $record): bool => ! $record->isAdmin())
                    ->schema([
                        Grid::make(2)->schema([
                            ...array_map(
                                fn (Module $module): TextEntry => TextEntry::make("module_{$module->value}")
                                    ->label($module->getLabel())
                                    ->badge()
                                    ->state(fn (User $record): string => $record->accessLevelFor($module)?->getLabel() ?? 'بدون')
                                    ->color(fn (string $state): string => match ($state) {
                                        'تعديل' => 'danger',
                                        'إضافة' => 'warning',
                                        'قراءة' => 'info',
                                        default => 'gray',
                                    })
                                    ->columnSpan(1),
                                Module::cases(),
                            ),
                            TextEntry::make('allowed_sites')
                                ->label('المواقع المسموح بها')
                                ->badge()
                                ->state(fn (User $record): array => array_map(
                                    fn (Site $site): string => $site->getLabel(),
                                    $record->allowedSites() ?? Site::cases(),
                                ))
                                ->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }
}
