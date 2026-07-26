<?php

namespace App\Policies;

use App\Enums\Module;

class ContractualRequirementPolicy extends ModulePolicy
{
    protected function module(): Module
    {
        return Module::ContractualRequirements;
    }
}
