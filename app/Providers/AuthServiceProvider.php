<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Adldap\AdldapInterface;
use Adldap\Laravel\Resolvers\ResolverInterface;
use App\Auth\LdapUserResolver;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
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
    }
}
