<?php

namespace App\Providers;

use App\Interfaces\CasServer as CasServerInterface;
use App\Services\CasServerConnector;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use App\Interfaces\MfaManager as MfaManagerInterface;
use App\Services\MfaManager;
use App\Interfaces\AuthenticationManager as AuthenticationManagerInterface;
use App\Services\AuthenticationManager;
use App\Interfaces\ResetManager as ResetManagerInterface;
use App\Services\ResetManager;
use App\Interfaces\IdentityManager as IdentityManagerInterface;
use App\Services\IdentityManager;
use App\Interfaces\UserManager as UserManagerInterface;
use App\Services\UserManager;
use App\Util\Names;
use App\Interfaces\ChallengeManager as ChallengeManagerInterface;
use App\Services\ChallengeManager;
use App\Interfaces\ChallengeStore;
use App\Services\SessionChallengeStore;

class AppServiceProvider extends ServiceProvider
{

    public $bindings = [
        // identity provider for OIDC guard
        'cas.idp' => \App\Services\OidcConnector::class,
        'cas.resource' => \App\Http\Resources\LdapResource::class,
        // maps CAS delegated client names to resources
        'ext.idp.NIA' => \App\Http\Resources\NIAResource::class,
        'ext.idp.svipeid' => \App\Http\Resources\SvipeIdResource::class,
        'ext.idp.eduid' => \App\Http\Resources\eduResource::class,
        'ext.idp.edugain' => \App\Http\Resources\eduResource::class,
    ];
    
    
    public $singletons = [
        CasServerInterface::class => CasServerConnector::class,
        MfaManagerInterface::class => MfaManager::class,
        UserManagerInterface::class => UserManager::class,
        AuthenticationManagerInterface::class => AuthenticationManager::class,
        ResetManagerInterface::class => ResetManager::class,
        IdentityManagerInterface::class => IdentityManager::class,
        ChallengeManagerInterface::class => ChallengeManager::class,
        ChallengeStore::class => SessionChallengeStore::class,
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
        Validator::extend('similar', function ($attribute, $value, $parameters, $validator) {
            $value = Str::lower($value);
            $other = Str::lower(Arr::get($validator->getData(), $parameters[0]));
            $limit = count($parameters) > 1 ? $parameters[1] : 3;
            return empty($other) || Names::damlev($value, $other) < $limit;
        });
        Validator::extend('sameIfExists', function ($attribute, $value, $parameters, $validator) {
            $other = Arr::get($validator->getData(), $parameters[0]);
            return empty($other) || $value == $other;
        });
        Validator::extend('sameDateIfExists', function($attribute, $value, $parameters, $validator) {
            $other = Arr::get($validator->getData(), $parameters[0]);
            if(empty($other)) return true;
            $other_date = new Carbon($other);
            if(!($value instanceof Carbon)) $value = new Carbon($value);
            return $other_date->isSameDay($value);
        });
        Validator::extend('sameAdministrativeNumber', function ($attribute, $value, $parameters, $validator) {
            $value = sha1($value);
            $other = Arr::get($validator->getData(), $parameters[0]);
            return $value == $other;
        });
        Validator::extend('sameCardNumber', function ($attribute, $value, $parameters, $validator) {
            $value = strstr($value, "@", true) ?: $value;
            $other = Arr::get($validator->getData(), $parameters[0]);
            if(is_array($other)) {
                return in_array($value, array_map(function ($it) { return strstr($it, "@", true) ?: $it; }, $other));
            } else {
                $other = strstr($other, "@", true);
                return $value == $other;
            }
            return false;
        });
        Validator::extend('phone', function ($attribute, $value, $parameters, $validator) {
            $value = preg_replace("/\s+/", "", $value);
            return preg_match("/^[+]?\d{9,12}$/", $value);
        });
        Validator::extend('intersectsWith', function ($attribute, $value, $parameters, $validator) {
            if(!is_array($value)) {
                $value = [ $value ];
            }
            $other = Arr::get($validator->getData(), $parameters[0]);
            if(!is_array($other)) {
                $other = [ $other ];
            }
            return count(array_intersect($value, $other)) > 0;
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
}
