<?php

namespace Retailcore\Store_Profile;

use Illuminate\Support\ServiceProvider;

class Store_ProfileServiceProvider extends ServiceProvider
{
    Public function boot()
    {
        $this->loadRoutesFrom(__DIR__. '/routes/web.php');
        $this->loadViewsFrom(__DIR__. '/views','store_profile');
         $this->loadMigrationsFrom(__DIR__. '/database/migrations');
    }




}
