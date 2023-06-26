<?php

namespace App\Providers;

use App\Interfaces\CasServer as CasServerInterface;
use App\Services\CasServerConnector;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\MfaManager as MfaManagerInterface;
use App\Services\MfaManager;
use App\Interfaces\UserExtManager as UserExtManagerInterface;
use App\Services\UserExtManager;

class AppServiceProvider extends ServiceProvider
{

    public $bindings = [
        // identity provider for OIDC guard
        'cas.idp' => \App\Services\OidcConnector::class,
    ];
    
    
    public $singletons = [
        CasServerInterface::class => CasServerConnector::class,
        MfaManagerInterface::class => MfaManager::class,
        UserExtManagerInterface::class => UserExtManager::class,
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
