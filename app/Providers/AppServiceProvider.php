<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\UserManager as UserManagerInterface;
use App\Interfaces\UserExtManager as UserExtManagerInterface;
use App\Interfaces\ExtSourceManager as ExtSourceManagerInterface;
use App\Interfaces\IdentityManager as IdentityManagerInterface;
use App\Interfaces\LdapManager as LdapManagerInterface;
use App\Services\UserExtManager;
use App\Services\UserManager;
use App\Services\ExtSourceManager;
use App\Services\IdentityManager;
use App\Services\LdapManager;

class AppServiceProvider extends ServiceProvider
{
    public $singletons = [
        UserManagerInterface::class => UserManager::class,
        UserExtManagerInterface::class => UserExtManager::class,
        ExtSourceManagerInterface::class => ExtSourceManager::class,
        IdentityManagerInterface::class => IdentityManager::class,
        LdapManagerInterface::class => LdapManager::class,
    ];
    
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // since 5.6 this is done automatically by the app
        foreach($this->singletons as $interface => $implementation) {
            $this->app->singleton($interface, $implementation );
        }
    }
}
