<?php

namespace App\Filament\Resources\FinancialFlows\Pages;

use App\Filament\Resources\FinancialFlows\FinancialFlowResource;
use App\Filament\Support\SubmitsWorkflowOnCreate;
use Filament\Resources\Pages\CreateRecord;

class CreateFinancialFlow extends CreateRecord
{
    use SubmitsWorkflowOnCreate;

    protected static string $resource = FinancialFlowResource::class;
}
