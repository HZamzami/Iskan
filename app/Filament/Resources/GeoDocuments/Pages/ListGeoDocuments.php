<?php

namespace App\Filament\Resources\GeoDocuments\Pages;

use App\Filament\Resources\GeoDocuments\GeoDocumentResource;
use App\Models\GeoDocument;
use App\Models\GeoDocumentType;
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

        foreach (GeoDocumentType::active()->ordered()->get() as $type) {
            $tabs[$type->slug] = Tab::make($type->name)
                ->badge(fn (): int => GeoDocument::where('type', $type->slug)->count())
                ->badgeColor($type->color)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', $type->slug));
        }

        return $tabs;
    }
}
