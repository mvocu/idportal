<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Adldap\AdldapInterface;
use Adldap\Laravel\Resolvers\ResolverInterface;
use App\Auth\LdapUserResolver;
use App\Auth\Passwords\PasswordBrokerManager;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
        'App\User'  => 'App\Policies\UserPolicy'
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
    }
    

    public function provides()
    {
        return ['auth.password', 'auth.password.broker'];
    }
    
}
