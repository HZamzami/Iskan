<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Tasks\TaskResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create-internal')
                ->label('طلب مهمة داخلية')
                ->icon(Heroicon::Plus)
                ->color('gray')
                ->url(TaskResource::getUrl('create-internal')),
            Action::make('create-owner-consultant')
                ->label('طلب مهمة من مدير الأصل للاستشاري')
                ->icon(Heroicon::Plus)
                ->color('gray')
                ->url(TaskResource::getUrl('create-owner-consultant')),
            Action::make('create-owner-contractor')
                ->label('طلب مهمة من مدير الأصل للمقاول')
                ->icon(Heroicon::Plus)
                ->color('primary')
                ->url(TaskResource::getUrl('create-owner-contractor')),
        ];
    }
}
