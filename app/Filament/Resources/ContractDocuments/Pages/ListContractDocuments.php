<?php

namespace App\Filament\Resources\ContractDocuments\Pages;

use App\Filament\Resources\ContractDocuments\ContractDocumentResource;
use App\Models\ContractDocument;
use App\Models\ContractDocumentType;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
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
            'mine' => Tab::make('بانتظار إجرائي')
                ->badge(fn (): int => ContractDocument::where('assigned_to', Filament::auth()->id())->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('assigned_to', Filament::auth()->id())),
        ];

        foreach (ContractDocumentType::active()->ordered()->get() as $type) {
            $tabs[$type->slug] = Tab::make($type->name)
                ->badge(fn (): int => ContractDocument::where('type', $type->slug)->count())
                ->badgeColor($type->color)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', $type->slug));
        }

        return $tabs;
    }
}
