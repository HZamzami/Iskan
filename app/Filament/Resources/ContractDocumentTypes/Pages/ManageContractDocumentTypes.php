<?php

namespace App\Filament\Resources\ContractDocumentTypes\Pages;

use App\Filament\Resources\ContractDocumentTypes\ContractDocumentTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageContractDocumentTypes extends ManageRecords
{
    protected static string $resource = ContractDocumentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
