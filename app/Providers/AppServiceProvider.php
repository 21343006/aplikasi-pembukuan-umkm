<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Capitalearly;
use App\Observers\CapitalearlyObserver;

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
        // Register observers
        Capitalearly::observe(CapitalearlyObserver::class);
    }
}
