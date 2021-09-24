<?php

namespace Retailcore\Loyalty_Setup;

use Illuminate\Support\ServiceProvider;

class LoyaltySetupServiceProvider extends ServiceProvider
{
    Public function boot()
    {
        $this->loadRoutesFrom(__DIR__. '/routes/web.php');
        $this->loadViewsFrom(__DIR__. '/views','loyalty_setup');
        $this->loadMigrationsFrom(__DIR__. '/database/migrations');
    }
}
