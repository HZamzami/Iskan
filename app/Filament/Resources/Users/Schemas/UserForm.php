<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\AccessLevel;
use App\Enums\Module;
use App\Enums\Site;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class UserForm
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
                            TextInput::make('name')
                                ->label('الاسم')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(1),
                            TextInput::make('email')
                                ->label('البريد الإلكتروني')
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255)
                                ->columnSpan(1),
                            TextInput::make('password')
                                ->label('كلمة المرور')
                                ->password()
                                ->revealable()
                                ->required(fn (string $operation): bool => $operation === 'create')
                                ->dehydrated(fn (?string $state): bool => filled($state))
                                ->helperText(fn (string $operation): ?string => $operation === 'edit'
                                    ? 'اتركها فارغة للإبقاء على كلمة المرور الحالية'
                                    : null)
                                ->maxLength(255)
                                ->columnSpan(1),
                            Toggle::make('is_admin')
                                ->label('مدير النظام')
                                ->helperText('مدير النظام يملك جميع الصلاحيات على كامل النظام')
                                ->live()
                                ->inline(false)
                                ->columnSpan(1),
                        ]),
                    ]),
                Section::make('صلاحيات الوحدات')
                    ->icon(Heroicon::LockClosed)
                    ->description('قراءة: عرض فقط • إضافة: إنشاء وعرض • تعديل: تعديل وحذف وإنشاء وعرض. اترك الحقل «بدون» لإخفاء الوحدة عن المستخدم.')
                    ->columnSpanFull()
                    ->visible(fn (Get $get): bool => ! $get('is_admin'))
                    ->schema([
                        Grid::make(2)->schema(
                            array_map(
                                fn (Module $module): Select => Select::make("modules.{$module->value}")
                                    ->label($module->getLabel())
                                    ->options(AccessLevel::class)
                                    ->placeholder('بدون')
                                    ->columnSpan(1),
                                Module::cases(),
                            ),
                        ),
                    ]),
                Section::make('المواقع المسموح بها')
                    ->icon(Heroicon::MapPin)
                    ->columnSpanFull()
                    ->visible(fn (Get $get): bool => ! $get('is_admin'))
                    ->schema([
                        CheckboxList::make('sites')
                            ->label('الأقسام / المواقع')
                            ->options(Site::class)
                            ->default(array_map(fn (Site $site): string => $site->value, Site::cases()))
                            ->bulkToggleable()
                            ->helperText('يرى المستخدم سجلات المواقع المحددة فقط. السجلات العامة (غير المرتبطة بموقع) تظهر لجميع المستخدمين.')
                            ->columns(2),
                    ]),
            ]);
    }
}
