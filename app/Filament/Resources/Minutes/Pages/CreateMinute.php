<?php

namespace App\Filament\Resources\Minutes\Pages;

use App\Filament\Resources\Minutes\MinuteResource;
use App\Filament\Support\SubmitsWorkflowOnCreate;
use Filament\Resources\Pages\CreateRecord;

class CreateMinute extends CreateRecord
{
    use SubmitsWorkflowOnCreate;

    protected static string $resource = MinuteResource::class;
}
