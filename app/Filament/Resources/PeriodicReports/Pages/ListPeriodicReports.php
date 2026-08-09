<?php

namespace App\Filament\Resources\PeriodicReports\Pages;

use App\Filament\Resources\PeriodicReports\PeriodicReportResource;
use App\Models\PeriodicReport;
use App\Models\PeriodicReportType;
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

        foreach (PeriodicReportType::active()->ordered()->get() as $type) {
            $tabs[$type->slug] = Tab::make($type->name)
                ->badge(fn (): int => PeriodicReport::where('type', $type->slug)->count())
                ->badgeColor($type->color)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', $type->slug));
        }

        return $tabs;
    }
}
