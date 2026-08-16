<?php

namespace App\Filament\Resources\Correspondences\Pages;

use App\Filament\Resources\Correspondences\CorrespondenceResource;
use App\Filament\Support\LinksCreatedRecordToTask;
use Filament\Resources\Pages\CreateRecord;

class CreateCorrespondence extends CreateRecord
{
    use LinksCreatedRecordToTask;

    protected static string $resource = CorrespondenceResource::class;
}
