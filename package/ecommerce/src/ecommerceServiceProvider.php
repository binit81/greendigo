<?php

namespace Retailcore\ecommerce;

use Illuminate\Support\ServiceProvider;

class ecommerceServiceProvider extends ServiceProvider
{
    Public function boot()
    {
        $this->loadRoutesFrom(__DIR__. '/routes/web.php');
        $this->loadViewsFrom(__DIR__. '/views','ecommerce');
        $this->loadMigrationsFrom(__DIR__. '/database/migrations');
    }

}