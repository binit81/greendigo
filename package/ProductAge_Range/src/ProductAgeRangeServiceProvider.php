<?php

namespace Retailcore\ProductAge_Range;

use Illuminate\Support\ServiceProvider;

class ProductAgeRangeServiceProvider extends ServiceProvider
{
    Public function boot()
    {
        $this->loadRoutesFrom(__DIR__. '/routes/web.php');
        $this->loadViewsFrom(__DIR__. '/views','productage_range');
        $this->loadMigrationsFrom(__DIR__. '/database/migrations');

    }
    public function register()
    {

    }



}