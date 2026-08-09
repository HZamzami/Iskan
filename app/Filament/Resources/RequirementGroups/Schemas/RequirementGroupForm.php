<?php

namespace App\Filament\Resources\RequirementGroups\Schemas;

use App\Enums\PaletteColor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class RequirementGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('اسم المجموعة')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),
                    TextInput::make('slug')
                        ->label('المعرّف')
                        ->disabled()
                        ->dehydrated(false)
                        ->visible(fn (string $operation): bool => $operation === 'edit')
                        ->helperText('يُنشأ تلقائياً من الاسم عند الإنشاء، ولا يمكن تغييره لاحقاً.')
                        ->columnSpan(1),
                    Select::make('color')
                        ->label('اللون')
                        ->options(PaletteColor::class)
                        ->required()
                        ->default('gray')
                        ->helperText('يُستخدم كلون افتراضي لأنواع المتطلبات داخل هذه المجموعة عند عدم تحديد لون خاص بها.')
                        ->columnSpan(1),
                    Toggle::make('is_active')
                        ->label('نشطة')
                        ->default(true)
                        ->inline(false)
                        ->columnSpan(1),
                ]),
            ]);
    }
}
