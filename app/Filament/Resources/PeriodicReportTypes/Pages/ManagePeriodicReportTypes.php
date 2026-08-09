<?php

namespace App\Filament\Resources\PeriodicReportTypes\Pages;

use App\Filament\Resources\PeriodicReportTypes\PeriodicReportTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePeriodicReportTypes extends ManageRecords
{
    protected static string $resource = PeriodicReportTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
