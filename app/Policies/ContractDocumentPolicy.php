<?php

namespace App\Policies;

use App\Enums\Module;

class ContractDocumentPolicy extends ModulePolicy
{
    protected function module(): Module
    {
        return Module::ContractDocuments;
    }
}
