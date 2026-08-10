<?php

namespace App\Filament\Resources\PeriodicReports\Pages;

use App\Filament\Resources\PeriodicReports\PeriodicReportResource;
use App\Filament\Support\SubmitsWorkflowOnCreate;
use Filament\Resources\Pages\CreateRecord;

class CreatePeriodicReport extends CreateRecord
{
    use SubmitsWorkflowOnCreate;

    protected static string $resource = PeriodicReportResource::class;
}
