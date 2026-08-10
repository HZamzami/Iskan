<?php

namespace App\Filament\Resources\ContractDocuments\Pages;

use App\Filament\Resources\ContractDocuments\ContractDocumentResource;
use App\Filament\Support\SubmitsWorkflowOnCreate;
use Filament\Resources\Pages\CreateRecord;

class CreateContractDocument extends CreateRecord
{
    use SubmitsWorkflowOnCreate;

    protected static string $resource = ContractDocumentResource::class;
}
