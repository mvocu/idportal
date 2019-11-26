<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Auth\AuthenticationException;

class ExternalIdPAuthenticate 
{
    protected $auth;
    
    public function __construct(Auth $auth) {
        $this->auth = $auth;
    }
    
    public function handle($request, Closure $next, $name = null) {
     
        if(empty($name)) {
            # extract the client name as the last component of URL path
            $path = explode('/', $request->getPathInfo());
            $client_name = array_pop($path);
        } else {
            $client_name = $name;
        }
        if(!empty($client_name)) {
            if(is_null($this->auth->guard($client_name)->user())) {
                if($this->auth->guard($client_name)->attempt()) {
                    # $this->auth->shouldUse($client_name);
                }
                # we do not get back here unless oidc client succeeded
            }
        } else {
            throw new AuthenticationException(
                'Unknown authentication provider.', [], $this->redirectTo($request)
                );
        }
        return $next($request);
    }
    
    protected function redirectTo($request)
    {
        return route('home');
    }
    
}

