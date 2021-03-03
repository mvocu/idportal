<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Aacotroneo\Saml2\Saml2Auth as Saml2AuthBase;
use Adldap\AdldapInterface;
use Adldap\Laravel\Resolvers\ResolverInterface;
use App\Auth\LdapUserResolver;
use App\Auth\Saml2Auth;
use App\Auth\Passwords\PasswordBrokerManager;
use App\Auth\ExternalIdPGuard;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\ExtSourceManager;
use Illuminate\Support\Facades\Request;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
        'App\User'  => 'App\Policies\UserPolicy',
        'App\Models\Database\UserExt' => 'App\Policies\UserExtPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // replace default UserResolver with our version
        $this->app->bind(ResolverInterface::class, function() {
            $ad = $this->app->make(AdldapInterface::class);
            
            return new LdapUserResolver($ad);
        });
            
        // replace default PasswordBrokerManager with our version
        $this->app->singleton('auth.password', function ($app) {
            return new PasswordBrokerManager($app);
        });
        
        // ... and the broker
        $this->app->bind('auth.password.broker', function ($app) {
            return $app->make('auth.password')
                ->broker();
        });

        // replace Saml2Auth with our version
        $this->app->singleton(Saml2Auth::class, function($app, $name = null, $config = array()) {
            $idpName = empty($name) ? $app->request->route('idpName') : $name;
            return new Saml2Auth($app, $idpName, $config);  
        });
        
        // register all external identity providers in DB as authentication guards
        if(!$this->app->runningInConsole()) {
            $esmgr = $this->app->make(ExtSourceManager::class);
            foreach($esmgr->listAuthenticators() as $es) {
                Auth::extend($es->name, function($app, $name, $config) use ($esmgr, $es) {
                    return new ExternalIdPGuard($name, $esmgr->getAuthenticator($es->name), Request::getSession());
                });
                $this->app['config']["auth.guards.{$es->name}"] = [ 'driver' => $es->name ];
            }
        }
    }
    

    public function provides()
    {
        return ['auth.password', 'auth.password.broker'];
    }
    
}
