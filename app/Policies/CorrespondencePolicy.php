<?php

namespace App\Policies;

use App\Enums\Module;

class CorrespondencePolicy extends ModulePolicy
{
    protected function module(): Module
    {
        return Module::Correspondences;
    }
}
