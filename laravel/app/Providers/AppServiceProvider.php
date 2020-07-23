<?php

namespace App\Providers;

use App\Colony;
use App\Observers\ColonyObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        date_default_timezone_set('Europe/Brussels');
        Colony::observe(ColonyObserver::class);
    }
}
