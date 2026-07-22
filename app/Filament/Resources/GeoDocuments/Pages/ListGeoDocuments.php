<?php

namespace App\Filament\Resources\GeoDocuments\Pages;

use App\Enums\GeoDocumentType;
use App\Filament\Resources\GeoDocuments\GeoDocumentResource;
use App\Models\GeoDocument;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListGeoDocuments extends ListRecords
{
    protected static string $resource = GeoDocumentResource::class;

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
                ->badge(fn (): int => GeoDocument::count()),
        ];

        foreach (GeoDocumentType::cases() as $type) {
            $tabs[$type->value] = Tab::make($type->getLabel())
                ->badge(fn (): int => GeoDocument::where('type', $type)->count())
                ->badgeColor($type->getColor())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', $type));
        }

        return $tabs;
    }
}
