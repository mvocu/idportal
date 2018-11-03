<?php

namespace App\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
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
use App\Utils\Names;

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
        Validator::extend('similar', function ($attribute, $value, $parameters, $validator) {
            $other = Arr::get($validator->getData(), $parameters[0]);
            $limit = count($parameters) > 1 ? $parameters[1] : 3;
            return Names::damlev($value, $other) < $limit;
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
