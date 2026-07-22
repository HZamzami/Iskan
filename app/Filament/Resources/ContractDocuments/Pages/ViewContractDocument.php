<?php

namespace App\Filament\Resources\ContractDocuments\Pages;

use App\Filament\Resources\ContractDocuments\ContractDocumentResource;
use App\Models\ContractDocument;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class ViewContractDocument extends ViewRecord
{
    protected static string $resource = ContractDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('تنزيل الملف')
                ->icon(Heroicon::ArrowDownTray)
                ->color('gray')
                ->action(fn (ContractDocument $record) => Storage::disk('local')
                    ->download($record->file_path, $record->reference_number.'.pdf')),
            EditAction::make(),
        ];
    }
}
