<?php

namespace App\Filament\Resources\ContractualRequirements\Pages;

use App\Filament\Resources\ContractualRequirements\ContractualRequirementResource;
use App\Models\ContractualRequirement;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class ViewContractualRequirement extends ViewRecord
{
    protected static string $resource = ContractualRequirementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('تنزيل الملف')
                ->icon(Heroicon::ArrowDownTray)
                ->color('gray')
                ->action(fn (ContractualRequirement $record) => Storage::disk('local')
                    ->download($record->file_path, $record->reference_number.'.pdf')),
            EditAction::make(),
        ];
    }
}
