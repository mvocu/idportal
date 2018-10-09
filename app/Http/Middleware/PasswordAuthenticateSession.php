<?php

namespace App\Http\Middleware;

use Illuminate\Session\Middleware\AuthenticateSession;

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
                
            $this->storePasswordHashInSession($request);
            
            return $response;
        }
        
        if ($this->auth->viaRemember()) {
            $passwordHash = explode('|', $request->cookies->get($this->auth->getRecallerName()))[2];
            
            if ($passwordHash != $request->user()->getAuthPassword()) {
                $this->logout($request);
            }
        }
        
        if (! $request->session()->has('password_hash')) {
            $this->storePasswordHashInSession($request);
        }

        $password = $request->session()->get('password_hash');
        if (empty($password) or 
                !$this->auth->getProvider()->validateCredentials($request->user(), [ 'password' => $password ] )) {
                $this->logout($request);
        } else {
            $request->user()->rememberPassword($password);
        }
        
        return tap($next($request), function () use ($request) {
            $this->storePasswordHashInSession($request);
        });
    }
    
    
}

