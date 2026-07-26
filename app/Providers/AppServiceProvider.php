<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\ActivityPolicy;
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
    }
}
