<?php

namespace App\Policies;

use App\Enums\Module;

class PeriodicReportPolicy extends ModulePolicy
{
    protected function module(): Module
    {
        return Module::PeriodicReports;
    }
}
