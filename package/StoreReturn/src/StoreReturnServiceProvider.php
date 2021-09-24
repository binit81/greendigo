<?php

namespace Retailcore\StoreReturn;

use Illuminate\Support\ServiceProvider;

class StoreReturnServiceProvider extends ServiceProvider
{
    Public function boot()
    {
        $this->loadRoutesFrom(__DIR__. '/routes/web.php');
        $this->loadViewsFrom(__DIR__. '/views', 'storereturn');
        $this->loadMigrationsFrom(__DIR__. '/database/migrations');



    }
    public function register()
    {

    }



}