<?php

namespace App\Filament\Resources\Minutes\Pages;

use App\Enums\MinuteType;
use App\Filament\Resources\Minutes\MinuteResource;
use App\Models\Minute;
use Filament\Actions\CreateAction;
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
        ];

        foreach (MinuteType::cases() as $type) {
            $tabs[$type->value] = Tab::make($type->getLabel())
                ->badge(fn (): int => Minute::where('type', $type)->count())
                ->badgeColor($type->getColor())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', $type));
        }

        return $tabs;
    }
}
