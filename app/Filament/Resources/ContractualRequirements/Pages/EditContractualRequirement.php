<?php

namespace App\Filament\Resources\ContractualRequirements\Pages;

use App\Filament\Resources\ContractualRequirements\ContractualRequirementResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditContractualRequirement extends EditRecord
{
    protected static string $resource = ContractualRequirementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
