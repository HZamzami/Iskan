<?php

namespace App\Filament\Resources\EntityTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class EntityTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('نوع الجهة')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->columnSpan(1),
                    TextInput::make('slug')
                        ->label('المعرّف')
                        ->disabled()
                        ->dehydrated(false)
                        ->visible(fn (string $operation): bool => $operation === 'edit')
                        ->helperText('يُنشأ تلقائياً من الاسم عند الإنشاء، ولا يمكن تغييره لاحقاً. يُستخدم في تحديد من هو "المالك" في سير الاعتماد.')
                        ->columnSpan(1),
                    Toggle::make('is_active')
                        ->label('نشطة')
                        ->default(true)
                        ->helperText('الجهات غير النشطة تختفي من خيارات سير الاعتماد وحقل "الجهة" عند إنشاء المستخدمين.')
                        ->inline(false)
                        ->columnSpanFull(),
                ]),
            ]);
    }
}
