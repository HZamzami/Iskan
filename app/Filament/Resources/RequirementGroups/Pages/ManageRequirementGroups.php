<?php

namespace App\Filament\Resources\RequirementGroups\Pages;

use App\Filament\Resources\RequirementGroups\RequirementGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRequirementGroups extends ManageRecords
{
    protected static string $resource = RequirementGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
