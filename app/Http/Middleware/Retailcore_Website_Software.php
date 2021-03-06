<?php

namespace App\Http\Middleware;

use Closure;

class Retailcore_Website_Software
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $acceptHeader = $request->header('Content-Type');
        $acceptAppId = $request->header('App-Id');
        $acceptAppSecret = $request->header('App-Secret');

        if ($acceptHeader != 'application/json' || $acceptAppId != config('constants.Retailcore_software_website_app_id') || $acceptAppSecret != config('constants.Retailcore_software_website_secret_id'))
        {
            return json_encode(array("Success"=>"False","Message"=>"Not A Valid Request"));
            exit;
        }

        return $next($request);
    }
}
