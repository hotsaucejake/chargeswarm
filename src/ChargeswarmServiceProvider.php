<?php

namespace Rennokki\Chargeswarm;

use Illuminate\Support\ServiceProvider;

class ChargeswarmServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/chargeswarm.php' => config_path('chargeswarm.php'),
            __DIR__.'/../database/migrations/2018_06_07_123211_chargeswarm.php' => database_path('migrations/2018_06_07_123211_chargeswarm.php'),
        ], 'config');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
