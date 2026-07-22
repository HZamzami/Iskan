<?php

namespace App\Filament\Resources\FinancialFlows\Pages;

use App\Enums\FinancialFlowType;
use App\Filament\Resources\FinancialFlows\FinancialFlowResource;
use App\Models\FinancialFlow;
use Filament\Actions\CreateAction;
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
        ];

        foreach (FinancialFlowType::cases() as $type) {
            $tabs[$type->value] = Tab::make($type->getLabel())
                ->badge(fn (): int => FinancialFlow::where('type', $type)->count())
                ->badgeColor($type->getColor())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', $type));
        }

        return $tabs;
    }
}
