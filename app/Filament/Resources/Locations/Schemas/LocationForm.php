<?php

namespace App\Filament\Resources\Locations\Schemas;

use App\Enums\LocationIcon;
use App\Enums\PaletteColor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الموقع')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('اسم الموقع')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(1),
                            TextInput::make('slug')
                                ->label('المعرّف')
                                ->disabled()
                                ->dehydrated(false)
                                ->visible(fn (string $operation): bool => $operation === 'edit')
                                ->helperText('يُنشأ تلقائياً من الاسم عند الإنشاء، ولا يمكن تغييره لاحقاً. تُستخدم صلاحية الوصول لهذا الموقع بناءً عليه.')
                                ->columnSpan(1),
                            Select::make('color')
                                ->label('اللون')
                                ->options(PaletteColor::class)
                                ->required()
                                ->default('info')
                                ->columnSpan(1),
                            Select::make('icon')
                                ->label('الأيقونة')
                                ->options(LocationIcon::class)
                                ->required()
                                ->default('map-pin')
                                ->native(false)
                                ->columnSpan(1),
                            Toggle::make('is_active')
                                ->label('نشط')
                                ->default(true)
                                ->helperText('المواقع غير النشطة تختفي من نماذج إنشاء السجلات الجديدة ومن قائمة الوصول للمستخدمين، لكنها تبقى ظاهرة في السجلات القديمة.')
                                ->inline(false)
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('جهات الموقع')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('contractor')
                                ->label('المقاول')
                                ->maxLength(255)
                                ->columnSpan(1),
                            TextInput::make('consultant')
                                ->label('الاستشاري')
                                ->maxLength(255)
                                ->columnSpan(1),
                            TextInput::make('asset_manager')
                                ->label('مدير الأصول')
                                ->maxLength(255)
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('ملاحظات')
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('notes')
                            ->hiddenLabel()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
