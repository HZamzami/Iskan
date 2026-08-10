<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('اسم الدور')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->columnSpan(1),
                    TextInput::make('slug')
                        ->label('المعرّف')
                        ->disabled()
                        ->dehydrated(false)
                        ->visible(fn (string $operation): bool => $operation === 'edit')
                        ->helperText('يُنشأ تلقائياً من الاسم عند الإنشاء، ولا يمكن تغييره لاحقاً. يُستخدم لتحديد "مدير الأصل" في سير الاعتماد.')
                        ->columnSpan(1),
                    Toggle::make('is_active')
                        ->label('نشط')
                        ->default(true)
                        ->helperText('الأدوار غير النشطة تختفي من خيارات سير الاعتماد وحقل "الدور" عند إنشاء المستخدمين.')
                        ->inline(false)
                        ->columnSpanFull(),
                ]),
            ]);
    }
}
