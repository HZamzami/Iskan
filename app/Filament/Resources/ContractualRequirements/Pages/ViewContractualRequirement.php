<?php

namespace App\Filament\Resources\ContractualRequirements\Pages;

use App\Filament\Resources\ContractualRequirements\ContractualRequirementResource;
use App\Filament\Support\FileActions;
use App\Filament\Support\WorkflowActions;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewContractualRequirement extends ViewRecord
{
    protected static string $resource = ContractualRequirementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            FileActions::preview(),
            FileActions::download(),
            ...WorkflowActions::forRecord(),
            EditAction::make(),
        ];
    }
}
