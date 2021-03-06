<?php
namespace App\Http\Middleware;

use Illuminate\Contracts\Auth\Factory as Auth;
use Closure;
use App\Interfaces\LdapConnector;
use App\User;
use App\Traits\FindsExternalAccount;

class ExternalIdPAuthenticateSession
{
    use FindsExternalAccount;
    
    protected $auth;
    
    public function __construct(Auth $auth, LdapConnector $ldap_mgr) {
        $this->auth = $auth;
        $this->ldap_mgr = $ldap_mgr;
    }
    
    public function handle($request, Closure $next, $name = null) {
        
        if(!is_null($this->auth->guard()->user())) {
            return $next($request);    
        }
        
        if(empty($name)) {
            # extract the client name as the last component of URL path
            $path = explode('/', $request->getPathInfo());
            $client_name = array_pop($path);
        } else {
            $client_name = $name;
        }
        if(!empty($client_name)) {
            # if there is a user token stored in session, get the user
            if(!is_null($user = $this->auth->guard($client_name)->user())) {
                $auth_user = $this->findExternalAccount($user, $client_name);
                if(is_null($auth_user)) {
                    $this->auth->guard($client_name)->logout();
                } else {
                    $appuser = new User([], $auth_user->getQuery());
                    $appuser->setRawAttributes($auth_user->getAttributes());
                    $this->auth->guard()->setUser($appuser);
                }
            }
        }
        return $next($request);
    }
    
}

