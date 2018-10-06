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
            return $next($request);
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
        
        if ($request->session()->get('password_hash') !== $request->user()->getAuthPassword()) {
            $this->logout($request);
        }
        
        return tap($next($request), function () use ($request) {
            $this->storePasswordHashInSession($request);
        });
    }
    
    
}

