<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public $bindings = [
        // identity provider for OIDC guard
        'cas.idp' => \App\Services\OidcConnector::class,
    ];
    
    
    public $singletons = [
        'cas.mgr' => \App\Services\CasServerConnector::class,
    ];
    
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
