<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Auth\Events\PasswordReset;
use App\Http\Controllers\Controller;
use App\Traits\ActivatesAccount;
use App\Interfaces\LdapConnector;

class ActivateController extends Controller
{
    use ActivatesAccount;

    /**
     * Where to redirect users after activation.
     *
     * @var string
     */
    protected $redirectTo = '/home';
    
    protected $ldap_mgr;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(LdapConnector $ldap_mgr)
    {
        $this->middleware('guest');
        $this->ldap_mgr = $ldap_mgr;
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $result = $this->ldap_mgr->changePassword($user, $password);
        if(!$result) {
            return false;
        }
        
        event(new PasswordReset($user));
        
        if($auth_user = $this->guard()->loginUsingId($user->getAuthIdentifier())) {
            $auth_user->rememberPassword($password);
        }
        
        return true;
    }
    
}

