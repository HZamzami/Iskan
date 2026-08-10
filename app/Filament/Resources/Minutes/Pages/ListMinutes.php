<?php

namespace App\Filament\Resources\Minutes\Pages;

use App\Filament\Resources\Minutes\MinuteResource;
use App\Models\Minute;
use App\Models\MinuteType;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMinutes extends ListRecords
{
    protected static string $resource = MinuteResource::class;

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
                ->badge(fn (): int => Minute::count()),
            'mine' => Tab::make('بانتظار إجرائي')
                ->badge(fn (): int => Minute::where('assigned_to', Filament::auth()->id())->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('assigned_to', Filament::auth()->id())),
        ];

        foreach (MinuteType::active()->ordered()->get() as $type) {
            $tabs[$type->slug] = Tab::make($type->name)
                ->badge(fn (): int => Minute::where('type', $type->slug)->count())
                ->badgeColor($type->color)
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', $type->slug));
        }

        return $tabs;
    }
}
