<?php

namespace App\Filament\Resources\PeriodicReports\Pages;

use App\Enums\PeriodicReportType;
use App\Filament\Resources\PeriodicReports\PeriodicReportResource;
use App\Models\PeriodicReport;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPeriodicReports extends ListRecords
{
    protected static string $resource = PeriodicReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('الكل')
                ->badge(fn (): int => PeriodicReport::count()),
        ];

        foreach (PeriodicReportType::cases() as $type) {
            $tabs[$type->value] = Tab::make($type->getLabel())
                ->badge(fn (): int => PeriodicReport::where('type', $type)->count())
                ->badgeColor($type->getColor())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', $type));
        }

        return $tabs;
    }
}
