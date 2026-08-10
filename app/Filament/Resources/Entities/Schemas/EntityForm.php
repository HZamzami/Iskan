<?php

namespace App\Filament\Resources\Entities\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class EntityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('اسم الجهة')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->columnSpan(1),
                    Select::make('entity_type_id')
                        ->label('نوع الجهة')
                        ->relationship('entityType', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label('نوع الجهة')
                                ->required()
                                ->maxLength(255)
                                ->unique('entity_types', 'name'),
                        ])
                        ->helperText('اختياري لجهات المراسلات (وزارات، شركات مرافق)، ومطلوب فعلياً فقط للجهات التي ستُستخدم في سير الاعتماد (مقاول/استشاري/مالك...).')
                        ->columnSpan(1),
                ]),
            ]);
    }
}
