<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Ldap\User;

class RetrieveLdapUser
{

    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $this->retrieveUser($request);
        
        return $next($request);
    }
    
    protected function retrieveUser(Request $request)
    {
        $auth_user = Auth::user();
        
        if(is_null($auth_user)) {
            return;
        }
        
        $user_id = $auth_user->getAuthIdentifier();
        
        $ldap_user = User::findBy('cunipersonalid',  $user_id);
        
        if(!is_null($ldap_user)) {
            $user = new \App\Models\User($auth_user, $ldap_user);
            
            Auth::guard()->login($user);
        }
    }
}
