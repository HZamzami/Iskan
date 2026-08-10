<?php

namespace App\Filament\Resources\FinancialFlows\Pages;

use App\Filament\Resources\FinancialFlows\FinancialFlowResource;
use App\Models\FinancialFlow;
use App\Models\FinancialFlowType;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListFinancialFlows extends ListRecords
{
    protected static string $resource = FinancialFlowResource::class;

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
                ->badge(fn (): int => FinancialFlow::count()),
            'mine' => Tab::make('بانتظار إجرائي')
                ->badge(fn (): int => FinancialFlow::where('assigned_to', Filament::auth()->id())->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('assigned_to', Filament::auth()->id())),
        ];

        foreach (FinancialFlowType::active()->ordered()->get() as $type) {
            $tabs[$type->slug] = Tab::make($type->name)
                ->badge(fn (): int => FinancialFlow::where('type', $type->slug)->count())
                ->badgeColor($type->color)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', $type->slug));
        }

        return $tabs;
    }
}
