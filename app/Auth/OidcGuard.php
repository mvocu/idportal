<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\IdentityProvider;
use Illuminate\Auth\GuardHelpers;

class OidcGuard implements Guard
{
    use GuardHelpers;  //
    
    protected $name;
    protected $authenticator;
    protected $session;
    protected $loggedOut;
    
    public function __construct($name, IdentityProvider $authenticator, Session $session)
    {
        $this->name = $name;
        $this->authenticator = $authenticator;
        $this->session = $session;
        $this->loggedOut = false;
    }
    
    public function user()
    {
        if($this->loggedOut) {
            return;
        }
        
        if(!is_null($this->user)) {
            return $this->user;
        }
        
        $key = $this->getName();
        $id_token = $this->session->get($key . "_id");
        $ac_token = $this->session->get($key . "_ac");
        if(!is_null($id_token)) {
            $this->user = $this->authenticator->validate($id_token, $ac_token);
            if(is_null($this->user)) {
                // $this->logout();
                $this->updateSession("", "");
                $this->loggedOut = true;
            }
        }
        
        return $this->user;
    }
    
    public function validate(array $credentials = [])
    {
        $this->authenticator->authenticate($credentials);
    }
    
    public function attempt(array $credentials = [], $remember = false)
    {
        $user = $this->authenticator->authenticate($credentials);
        if($user == null) {
            return false;
        }
        
        $this->login($user, $remember);
        
        return true;
    }
    
    /**
     * Log a user into the application.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  bool  $remember
     * @return void
     */
    public function login(Authenticatable $user, $remember = false)
    {
        $this->updateSession($user->getRememberToken(), $user->getAuthPassword());
        $this->loggedOut = false;
        $this->setUser($user);
    }
    
    public function logout($redirect = null)
    {
        $key = $this->getName();
        $id_token = $this->session->get($key . "_id");
        $this->user = null;
        $this->loggedOut = true;
        $this->updateSession("", "");
        $this->authenticator->logout($id_token, $redirect);
    }
    
    /**
     * Get a unique identifier for the auth session value.
     *
     * @return string
     */
    public function getName()
    {
        return 'login_'.$this->name.'_'.sha1(static::class);
    }
    
    
    protected function updateSession($id_token, $ac_token)
    {
        $key = $this->getName();
        if(empty($id_token)) {
            $this->session->forget($key . "_id");
	    } else {
	        $this->session->put($key . "_id", $id_token);
	    }
	    if(empty($ac_token)) {
            $this->session->forget($key . "_ac");
	    } else {
        	$this->session->put($key . "_ac", $ac_token);
	    }
        $this->session->migrate(false);
    }
    
}

