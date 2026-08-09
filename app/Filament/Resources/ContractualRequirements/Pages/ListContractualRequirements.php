<?php

namespace App\Filament\Resources\ContractualRequirements\Pages;

use App\Filament\Resources\ContractualRequirements\ContractualRequirementResource;
use App\Models\ContractualRequirement;
use App\Models\RequirementGroup;
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

        foreach (RequirementGroup::active()->ordered()->with('types')->get() as $group) {
            $types = $group->types->pluck('slug')->all();

            $tabs[$group->slug] = Tab::make($group->name)
                ->badge(fn (): int => ContractualRequirement::whereIn('type', $types)->count())
                ->badgeColor($group->color)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('type', $types));
        }

        return $tabs;
    }
}
