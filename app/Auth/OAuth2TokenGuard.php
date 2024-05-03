<?php
namespace App\Auth;

use App\Interfaces\IdentityProvider;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Auth\GuardHelpers;

class OAuth2TokenGuard implements Guard
{
    
    use GuardHelpers;
    
    protected $request;
    protected $authenticator;

    protected $inputKey = "ac_token";

    public function __construct(Request $request, IdentityProvider $authenticator)
    {
        $this->request = $request;
        $this->authenticator = $authenticator;
    }
    
    
    public function user()
    {
        if(!is_null($this->user)) {
            return $this->user;
        }
        
        $token = $this->getTokenForRequest();
        
        if (!empty($token)) {
            $user = $this->authenticator->introspect($token);
        } else {
            $user = null;
        }
        
        return $this->user = $user;
        
    }

    public function validate(array $credentials = [])
    {
        if(empty($credentials) || empty($credentials[0])) {
            return false;
        }
        
        return !empty($this->authenticator->introspect($credentials[0]));
    }
    
    /**
     * Get the token for the current request.
     *
     * @return string
     */
    public function getTokenForRequest()
    {
        $token = $this->request->query($this->inputKey);
        
        if (empty($token)) {
            $token = $this->request->input($this->inputKey);
        }
        
        if (empty($token)) {
            $token = $this->request->bearerToken();
        }
        
        if (empty($token)) {
            $token = $this->request->getPassword();
        }
        
        return $token;
    }
    
}

