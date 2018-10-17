<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\UserManager as UserManagerInterface;
use App\Interfaces\UserExtManager as UserExtManagerInterface;
use App\Interfaces\ExtSourceManager as ExtSourceManagerInterface;
use App\Interfaces\IdentityExtractor as IdentityExtractorInterface;
use App\Services\UserExtManager;
use App\Services\UserManager;
use App\Services\ExtSourceManager;
use App\Services\IdentityExtractor;

class AppServiceProvider extends ServiceProvider
{
    public $singletons = [
        UserManagerInterface::class => UserManager::class,
        UserExtManagerInterface::class => UserExtManager::class,
        ExtSourceManagerInterface::class => ExtSourceManager::class,
        IdentityExtractorInterface::class => IdentityExtractor::class,
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
        //
    }
}
