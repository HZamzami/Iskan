<?php

namespace App\Filament\Resources\ContractDocuments\Pages;

use App\Enums\ContractDocumentType;
use App\Filament\Resources\ContractDocuments\ContractDocumentResource;
use App\Models\ContractDocument;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListContractDocuments extends ListRecords
{
    protected static string $resource = ContractDocumentResource::class;

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
                ->badge(fn (): int => ContractDocument::count()),
        ];

        foreach (ContractDocumentType::cases() as $type) {
            $tabs[$type->value] = Tab::make($type->getLabel())
                ->badge(fn (): int => ContractDocument::where('type', $type)->count())
                ->badgeColor($type->getColor())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', $type));
        }

        return $tabs;
    }
}
