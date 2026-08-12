<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Tasks\Concerns\CreatesTaskForRole;
use App\Filament\Resources\Tasks\TaskResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOwnerConsultantTask extends CreateRecord
{
    use CreatesTaskForRole;

    protected static string $resource = TaskResource::class;

    protected static function targetRoleSlug(): string
    {
        return 'consultant';
    }
}
