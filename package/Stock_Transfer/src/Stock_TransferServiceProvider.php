<?php

namespace Retailcore\Stock_Transfer;

use Illuminate\Support\ServiceProvider;

class Stock_TransferServiceProvider extends ServiceProvider
{
    Public function boot()
    {
        $this->loadRoutesFrom(__DIR__. '/routes/web.php');
        $this->loadViewsFrom(__DIR__. '/views','stock_transfer');
        $this->loadMigrationsFrom(__DIR__. '/database/migrations');
    }




}
