<?php

namespace App\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use App\Interfaces\UserManager as UserManagerInterface;
use App\Interfaces\UserExtManager as UserExtManagerInterface;
use App\Interfaces\ExtSourceManager as ExtSourceManagerInterface;
use App\Interfaces\IdentityManager as IdentityManagerInterface;
use App\Interfaces\LdapConnector as LdapConnectorInterface;
use App\Interfaces\ContactManager as ContactManagerInterface;
use App\Interfaces\ConsentManager as ConsentManagerInterface;
use App\Interfaces\SynchronizationManager as SynchronizationManagerInterface;
use App\Interfaces\ActivationManager as ActivationManagerInterface;
use App\Services\ContactManager;
use App\Services\UserExtManager;
use App\Services\UserManager;
use App\Services\ExtSourceManager;
use App\Services\IdentityManager;
use App\Services\LdapConnector;
use App\Services\ConsentManager;
use App\Services\GinisConnector;
use App\Services\TritiusConnector;
use App\Services\HeliosConnector;
use App\Services\ADConnector;
use App\Services\PortalObcanaConnector;
use App\Services\SynchronizationManager;
use App\Services\ActivationManager;
use App\Utils\Names;
use GuzzleHttp\Client;

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
        SynchronizationManagerInterface::class => SynchronizationManager::class
    ];
    
    public $bindings = [
        // connectors - keys here must correspond to the type column in ext_sources table
        'Ginis' => GinisConnector::class,
        'Tritius' => TritiusConnector::class,
        'AD' => ADConnector::class,
        'Helios' => HeliosConnector::class,
        'PortalObcana' => PortalObcanaConnector::class,
        // other services
        ActivationManagerInterface::class => ActivationManager::class
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
            return empty($other) || Names::damlev($value, $other) < $limit;
        });
        Validator::extend('sameIfExists', function ($attribute, $value, $parameters, $validator) {
            $other = Arr::get($validator->getData(), $parameters[0]);
            return empty($other) || $value == $other;
        });
        Validator::extend('phone', function ($attribute, $value, $parameters, $validator) {
            $value = preg_replace("/\s+/", "", $value);
            return preg_match("/^[+]?\d{9,12}$/", $value);            
        });
        Validator::extend('recaptcha', function ($attribute, $value, $parameters, $validator) {
            $client = new Client(['base_uri' => 'https://www.google.com/recaptcha/api/']);
            $response = $client->request('POST', 'siteverify', [
                'form_params' => [
                    'secret' => (Config::get('recaptcha'))['server_secret'],
                    'response' => $value,
                    'remoteip' => $_SERVER['REMOTE_ADDR']
                ]
            ]);
            $data = json_decode($response->getBody());
            return $data->success;
        });
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
        foreach ($this->bindings as $key => $value) {
            $this->app->bind($key, $value);
        }
    }
}
