<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ArchiveOverviewStats;
use App\Filament\Widgets\CorrespondenceTrendChart;
use App\Filament\Widgets\ExpiringContracts;
use App\Filament\Widgets\FinancialFlowsChart;
use App\Filament\Widgets\LatestDocuments;
use App\Filament\Widgets\QuickActions;
use App\Filament\Widgets\RecentActivity;
use App\Filament\Widgets\SiteOverview;
use App\Models\Location;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\Carbon;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'لوحة المعلومات';

    protected Width|string|null $maxContentWidth = Width::Full;

    public function getHeading(): string
    {
        $name = Filament::auth()->user()?->name;

        return $name ? "مرحباً، {$name} 👋" : 'لوحة المعلومات';
    }

    public function getSubheading(): ?string
    {
        return Carbon::now()->translatedFormat('l، j F Y');
    }

    public function filtersForm(Schema $schema): Schema
    {
        /** @var User|null $user */
        $user = Filament::auth()->user();
        $sites = $user?->allowedSites() ?? Location::active()->ordered()->get()->all();

        if ($sites === []) {
            return $schema->components([]);
        }

        return $schema->components([
            Select::make('site')
                ->label('الموقع')
                ->placeholder('جميع المواقع')
                ->options(collect($sites)
                    ->mapWithKeys(fn (Location $location): array => [$location->slug => $location->name])
                    ->all())
                ->native(false),
        ]);
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'lg' => 2,
            'xl' => 4,
        ];
    }

    public function getWidgets(): array
    {
        return [
            QuickActions::class,
            ArchiveOverviewStats::class,
            SiteOverview::class,
            FinancialFlowsChart::class,
            CorrespondenceTrendChart::class,
            ExpiringContracts::class,
            LatestDocuments::class,
            RecentActivity::class,
        ];
    }
}
