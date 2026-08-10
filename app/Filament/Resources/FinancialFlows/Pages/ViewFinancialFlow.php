<?php

namespace App\Filament\Resources\FinancialFlows\Pages;

use App\Filament\Resources\FinancialFlows\FinancialFlowResource;
use App\Filament\Support\WorkflowActions;
use App\Models\FinancialFlow;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class ViewFinancialFlow extends ViewRecord
{
    protected static string $resource = FinancialFlowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('معاينة الملف')
                ->icon(Heroicon::Eye)
                ->color('gray')
                ->url(fn (FinancialFlow $record): string => Storage::disk('local')->temporaryUrl(
                    $record->file_path,
                    now()->addMinutes(5),
                ))
                ->openUrlInNewTab(),
            Action::make('download')
                ->label('تنزيل الملف')
                ->icon(Heroicon::ArrowDownTray)
                ->color('gray')
                ->action(fn (FinancialFlow $record) => Storage::disk('local')
                    ->download($record->file_path, $record->reference_number.'.'.pathinfo($record->file_path, PATHINFO_EXTENSION))),
            ...WorkflowActions::forRecord(),
            EditAction::make(),
        ];
    }
}
