<?php

namespace App\Providers;

use App\Interfaces\CasServer as CasServerInterface;
use App\Services\CasServerConnector;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\MfaManager as MfaManagerInterface;
use App\Services\MfaManager;

class AppServiceProvider extends ServiceProvider
{

    public $bindings = [
        // identity provider for OIDC guard
        'cas.idp' => \App\Services\OidcConnector::class,
    ];
    
    
    public $singletons = [
        CasServerInterface::class => CasServerConnector::class,
        MfaManagerInterface::class => MfaManager::class,
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
