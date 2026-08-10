<?php

namespace App\Filament\Resources\ContractualRequirements\Pages;

use App\Filament\Resources\ContractualRequirements\ContractualRequirementResource;
use App\Filament\Support\SubmitsWorkflowOnCreate;
use Filament\Resources\Pages\CreateRecord;

class CreateContractualRequirement extends CreateRecord
{
    use SubmitsWorkflowOnCreate;

    protected static string $resource = ContractualRequirementResource::class;
}
