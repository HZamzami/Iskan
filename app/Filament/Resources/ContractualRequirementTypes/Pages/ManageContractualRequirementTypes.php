<?php

namespace App\Filament\Resources\ContractualRequirementTypes\Pages;

use App\Filament\Resources\ContractualRequirementTypes\ContractualRequirementTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageContractualRequirementTypes extends ManageRecords
{
    protected static string $resource = ContractualRequirementTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
