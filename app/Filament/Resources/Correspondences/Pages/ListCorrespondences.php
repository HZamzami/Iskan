<?php

namespace App\Filament\Resources\Correspondences\Pages;

use App\Enums\CorrespondenceDirection;
use App\Filament\Resources\Correspondences\CorrespondenceResource;
use App\Filament\Widgets\CorrespondenceStats;
use App\Models\Correspondence;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListCorrespondences extends ListRecords
{
    protected static string $resource = CorrespondenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CorrespondenceStats::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل')
                ->badge(fn (): int => Correspondence::count()),
            'incoming' => Tab::make('وارد')
                ->icon(Heroicon::ArrowDownTray)
                ->badge(fn (): int => Correspondence::where('direction', CorrespondenceDirection::Incoming)->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('direction', CorrespondenceDirection::Incoming)),
            'outgoing' => Tab::make('صادر')
                ->icon(Heroicon::ArrowUpTray)
                ->badge(fn (): int => Correspondence::where('direction', CorrespondenceDirection::Outgoing)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('direction', CorrespondenceDirection::Outgoing)),
        ];
    }
}
