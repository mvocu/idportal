<?php

namespace App\Http\Middleware;

use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\Log;

use Closure;

class PasswordAuthenticateSession extends AuthenticateSession
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $request->user() || ! $request->session()) {
            $response = $next($request);

            if (! $request->user() || ! $request->session()) {
                return $response;
            }

            $user = $request->user();
            if($user instanceOf \App\User && !empty($user->getAuthPassword())) {
                Log::debug("User authenticated during request processing, storing credentials for future requests", ['user'=>$request->user()->getAuthIdentifier()]);
                $this->storePasswordHashInSession($request);
            }
            
            return $response;
        }

        if ($this->auth->viaRemember()) {
            $passwordHash = explode('|', $request->cookies->get($this->auth->getRecallerName()))[2];
            
            if ($passwordHash != $request->user()->getAuthPassword()) {
                $this->logout($request);
            }
        }
       
        $user = $request->user(); 
        if ( !$request->session()->has('password_hash') && $user instanceOf \App\User && !empty($user->getAuthPassword()) ) {
            Log::debug("User authenticated, but session is missing credentials, saving", ['user'=>$user->getAuthIdentifier(), 'password' => $user->getAuthPassword()]);
            $this->storePasswordHashInSession($request);
        }

        $password = $request->session()->get('password_hash');
        if (!empty($password)) {
            Log::debug("Validating session credentials ", ['user'=>$user->getAuthIdentifier(), 'password'=> $password, 'provider' => $this->auth->getProvider() ]);
            if(!$this->auth->getProvider()->validateCredentials($user, [ 'password' => $password ] )) {
                    $this->logout($request);
            } else {
                Log::debug("Saving session credentials ", ['user'=>$user->getAuthIdentifier(), 'password'=> $password, 'provider' => $this->auth->getProvider() ]);
                $user->rememberPassword($password);
            }
        } else {
            # user has no password. what should we do now?
            Log::debug("User authenticated, but no password available", ['user'=>$user->getAuthIdentifier()]);
        }
        
        return tap($next($request), function () use ($request) {
            $user = $request->user();
            if($user instanceOf \App\User && !empty($user->getAuthPassword())) {
                $this->storePasswordHashInSession($request);
            }
        });
    }
    
    
}

