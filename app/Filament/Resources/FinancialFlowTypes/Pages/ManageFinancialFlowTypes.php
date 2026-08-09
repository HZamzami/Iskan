<?php

namespace App\Filament\Resources\FinancialFlowTypes\Pages;

use App\Filament\Resources\FinancialFlowTypes\FinancialFlowTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFinancialFlowTypes extends ManageRecords
{
    protected static string $resource = FinancialFlowTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
