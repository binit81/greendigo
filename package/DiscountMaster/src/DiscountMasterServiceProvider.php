<?php

namespace Retailcore\DiscountMaster;

use Illuminate\Support\ServiceProvider;

class DiscountMasterServiceProvider extends ServiceProvider
{
    Public function boot()
    {
        $this->loadRoutesFrom(__DIR__. '/routes/web.php');
        $this->loadViewsFrom(__DIR__. '/views', 'discountmaster');
        $this->loadMigrationsFrom(__DIR__. '/database/migrations');



    }
    public function register()
    {

    }



}