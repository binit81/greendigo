<?php

namespace Retailcore\Consignment;

use Illuminate\Support\ServiceProvider;

class ConsignmentServiceProvider extends ServiceProvider
{
    Public function boot()
    {
        $this->loadRoutesFrom(__DIR__. '/routes/web.php');
        $this->loadViewsFrom(__DIR__. '/views', 'consignment');
        $this->loadMigrationsFrom(__DIR__. '/database/migrations');



    }
    public function register()
    {

    }



}