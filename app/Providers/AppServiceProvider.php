<?php

namespace App\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use App\Interfaces\UserManager as UserManagerInterface;
use App\Interfaces\UserExtManager as UserExtManagerInterface;
use App\Interfaces\ExtSourceManager as ExtSourceManagerInterface;
use App\Interfaces\IdentityManager as IdentityManagerInterface;
use App\Interfaces\LdapConnector as LdapConnectorInterface;
use App\Interfaces\ContactManager as ContactManagerInterface;
use App\Interfaces\ConsentManager as ConsentManagerInterface;
use App\Services\ContactManager;
use App\Services\UserExtManager;
use App\Services\UserManager;
use App\Services\ExtSourceManager;
use App\Services\IdentityManager;
use App\Services\LdapConnector;
use App\Utils\Names;
use App\Services\ConsentManager;

class AppServiceProvider extends ServiceProvider
{
    public $singletons = [
        UserManagerInterface::class => UserManager::class,
        UserExtManagerInterface::class => UserExtManager::class,
        ExtSourceManagerInterface::class => ExtSourceManager::class,
        IdentityManagerInterface::class => IdentityManager::class,
        ContactManagerInterface::class => ContactManager::class,
        LdapConnectorInterface::class => LdapConnector::class,
        ConsentManagerInterface::class => ConsentManager::class,
    ];
    
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('similar', function ($attribute, $value, $parameters, $validator) {
            $other = Arr::get($validator->getData(), $parameters[0]);
            $limit = count($parameters) > 1 ? $parameters[1] : 3;
            return Names::damlev($value, $other) < $limit;
        });
        Validator::extend('sameIfExists', function ($attribute, $value, $parameters, $validator) {
            $other = Arr::get($validator->getData(), $parameters[0]);
            return empty($other) || $value == $other;
        });
        Validator::extend('phone', function ($attribute, $value, $parameters, $validator) {
            $value = preg_replace("/\s+/", "", $value);
            return preg_match("/^[+]?\d{9,12}$/", $value);            
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
