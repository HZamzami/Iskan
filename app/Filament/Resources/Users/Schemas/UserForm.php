<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\AccessLevel;
use App\Enums\Module;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\Location;
use App\Models\User;
use Filament\Facades\Filament;
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
                            TextInput::make('phone')
                                ->label('رقم الهاتف')
                                ->tel()
                                ->maxLength(255)
                                ->columnSpan(1),
                            Select::make('role_id')
                                ->label('الدور')
                                ->relationship('role', 'name')
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->label('اسم الدور')
                                        ->required()
                                        ->maxLength(255)
                                        ->unique('roles_lookup', 'name'),
                                ])
                                ->helperText('يُستخدم لتحديد دور المستخدم في سير اعتماد المستندات (مقاول/استشاري/مالك)، ولا علاقة له بجهات المراسلات.')
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
                            Toggle::make('is_active')
                                ->label('نشط')
                                ->helperText('المستخدم غير النشط لا يستطيع تسجيل الدخول إلى النظام')
                                ->default(true)
                                ->disabled(fn (?User $record): bool => $record !== null && (
                                    $record->is(Filament::auth()->user()) || UsersTable::isLastAdmin($record)
                                ))
                                ->inline(false)
                                ->columnSpan(1),
                        ]),
                    ]),
                Section::make('صلاحيات الوحدات')
                    ->icon(Heroicon::LockClosed)
                    ->description('قراءة: عرض فقط • إضافة: إنشاء وعرض • تعديل: تعديل وإنشاء وعرض • حذف: كل الصلاحيات بما فيها الحذف. اترك الحقل «بدون» لإخفاء الوحدة عن المستخدم.')
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
                            ->options(fn (): array => Location::active()->ordered()->pluck('name', 'slug')->all())
                            ->default(fn (): array => Location::active()->pluck('slug')->all())
                            ->bulkToggleable()
                            ->helperText('يرى المستخدم سجلات المواقع المحددة فقط. السجلات العامة (غير المرتبطة بموقع) تظهر لجميع المستخدمين.')
                            ->columns(2),
                    ]),
            ]);
    }
}
