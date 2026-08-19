<?php

namespace App\Filament\Resources\PeriodicReports\Pages;

use App\Filament\Resources\PeriodicReports\PeriodicReportResource;
use App\Filament\Support\FileActions;
use App\Filament\Support\WorkflowActions;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPeriodicReport extends ViewRecord
{
    protected static string $resource = PeriodicReportResource::class;

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
