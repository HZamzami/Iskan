<?php

namespace App\Filament\Resources\Entities\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EntityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم الجهة')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }
}
