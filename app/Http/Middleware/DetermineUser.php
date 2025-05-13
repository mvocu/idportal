<?php
namespace App\Http\Middleware;

use App\User;
use App\Interfaces\ExtSourceManager;
use App\Interfaces\LdapConnector;
use App\Interfaces\UserExtManager;
use App\Interfaces\UserManager;
use App\Traits\FindsExternalAccount;
use Illuminate\Contracts\Auth\Factory as Auth;
use Closure;

/*
 * Try to determine possible identity of externally authenticated user.
 * 
 */
class DetermineUser
{
    use FindsExternalAccount;

    protected $ext_source_mgr;
    protected $user_ext_mgr;
    protected $user_mgr;
    protected $auth;
    
    public function __construct(
        Auth $auth, 
        ExtSourceManager $ext_source_mgr,
        UserExtManager $user_ext_mgr,
        UserManager $user_mgr,
        LdapConnector $ldap_mgr
        ) 
    {
        $this->auth = $auth;
        $this->ext_source_mgr = $ext_source_mgr;
        $this->user_ext_mgr = $user_ext_mgr;
        $this->user_mgr = $user_mgr;
        $this->ldap_mgr = $ldap_mgr;
    }
    
    public function handle($request, Closure $next, $name = null) 
    {
        # if there already is established identity, do nothing
        if($this->auth->user() instanceof \App\User ) {
            return $next($request);
        }
        
        $idps = $this->ext_source_mgr->listAuthenticators()->pluck('name');

        # check if we have a logged in user in some of the idps
        $auth_user = null;
        $client = null;
        foreach ($idps as $idp) {
            # somewhat similar to Authenticate middleware, but does not throw exception when unauthenticated
            if($this->auth->guard($idp)->check()) {
                $client = $idp;
                $auth_user = $this->auth->guard($idp)->user();
            }
        }
        # having logged in using external identity, we have to get to the actual user differently
        $user = null;
        if($client != null) {
            #$auth_user = Auth::guard($client)->user();
            $users = $this->findUserByExtIdentity($auth_user, $client);
            if($users->count() == 1) {
                $user = $users->first();
                $ldap_user = $this->ldap_mgr->findUser($user);
                # convert to App\User (see middleware ExternalIdpAuthenticateSession)
                $appuser = new User([], $ldap_user->getQuery());
                $appuser->setRawAttributes($ldap_user->getAttributes());
                $this->auth->guard()->setUser($appuser);
            } else {
                $this->auth->shouldUse($this->auth->guard($client));
            }
        }

        return $next($request);
    }
        
}

