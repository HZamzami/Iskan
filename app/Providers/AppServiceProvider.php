<?php

namespace App\Providers;

use App\Models\ContractDocumentType;
use App\Models\ContractualRequirementType;
use App\Models\Entity;
use App\Models\EntityType;
use App\Models\FinancialFlowType;
use App\Models\GeoDocumentType;
use App\Models\Location;
use App\Models\MinuteType;
use App\Models\PeriodicReportType;
use App\Models\RequirementGroup;
use App\Models\User;
use App\Observers\LocationObserver;
use App\Policies\ActivityPolicy;
use App\Policies\AdminOnlyPolicy;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentAsset::register([
            Css::make('arabic-typography', resource_path('css/filament/arabic-typography.css')),
        ]);

        Carbon::setLocale(config('app.locale'));

        Gate::before(fn (User $user, string $ability): ?bool => $user->isAdmin() ? true : null);

        Gate::policy(Activity::class, ActivityPolicy::class);

        foreach ([
            Location::class,
            RequirementGroup::class,
            MinuteType::class,
            GeoDocumentType::class,
            ContractDocumentType::class,
            FinancialFlowType::class,
            PeriodicReportType::class,
            ContractualRequirementType::class,
            EntityType::class,
            Entity::class,
        ] as $lookupModel) {
            Gate::policy($lookupModel, AdminOnlyPolicy::class);
        }

        Location::observe(LocationObserver::class);
    }
}
