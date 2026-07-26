<?php

namespace App\Policies;

use App\Enums\Module;

class GeoDocumentPolicy extends ModulePolicy
{
    protected function module(): Module
    {
        return Module::GeoDocuments;
    }
}
