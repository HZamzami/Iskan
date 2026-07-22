<?php

namespace App\Filament\Resources\ContractualRequirements\Pages;

use App\Enums\RequirementGroup;
use App\Filament\Resources\ContractualRequirements\ContractualRequirementResource;
use App\Models\ContractualRequirement;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListContractualRequirements extends ListRecords
{
    protected static string $resource = ContractualRequirementResource::class;

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
                ->badge(fn (): int => ContractualRequirement::count()),
        ];

        foreach (RequirementGroup::cases() as $group) {
            $types = array_column($group->types(), 'value');

            $tabs[$group->value] = Tab::make($group->getLabel())
                ->badge(fn (): int => ContractualRequirement::whereIn('type', $types)->count())
                ->badgeColor($group->getColor())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('type', $types));
        }

        return $tabs;
    }
}
