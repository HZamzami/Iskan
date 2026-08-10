<?php

namespace App\Filament\Resources\EntityTypes\Pages;

use App\Filament\Resources\EntityTypes\EntityTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageEntityTypes extends ManageRecords
{
    protected static string $resource = EntityTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
