<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;
use App\Auth\OidcGuard;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // register OIDC guard using CAS OIDC server
        if(!$this->app->runningInConsole()) {
            Auth::extend('oidc', function($app, $name, $config) {
                return new OidcGuard($name, $this->app->makeWith($config['idp'], ['config' => $config]), Request::getSession());
            });
        }

    }
}
