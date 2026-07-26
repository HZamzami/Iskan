<?php

namespace App\Policies;

use App\Enums\Module;

class MinutePolicy extends ModulePolicy
{
    protected function module(): Module
    {
        return Module::Minutes;
    }
}
