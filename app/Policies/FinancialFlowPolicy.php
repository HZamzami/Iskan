<?php

namespace App\Policies;

use App\Enums\Module;

class FinancialFlowPolicy extends ModulePolicy
{
    protected function module(): Module
    {
        return Module::FinancialFlows;
    }
}
