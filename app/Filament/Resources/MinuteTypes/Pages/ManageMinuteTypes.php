<?php

namespace App\Filament\Resources\MinuteTypes\Pages;

use App\Filament\Resources\MinuteTypes\MinuteTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageMinuteTypes extends ManageRecords
{
    protected static string $resource = MinuteTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
