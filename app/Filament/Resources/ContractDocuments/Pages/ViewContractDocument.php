<?php

namespace App\Filament\Resources\ContractDocuments\Pages;

use App\Filament\Resources\ContractDocuments\ContractDocumentResource;
use App\Filament\Support\FileActions;
use App\Filament\Support\WorkflowActions;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewContractDocument extends ViewRecord
{
    protected static string $resource = ContractDocumentResource::class;

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
