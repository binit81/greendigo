<?php

namespace Retailcore\Referral_Points;

use Illuminate\Support\ServiceProvider;

class Referral_PointsServiceProvider extends ServiceProvider
{
    Public function boot()
    {
     $this->loadRoutesFrom(__DIR__. '/routes/web.php');
     $this->loadViewsFrom(__DIR__. '/views','referral_points');
	 $this->loadMigrationsFrom(__DIR__. '/database/migrations');
	 }


}
