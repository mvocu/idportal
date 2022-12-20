<?php
namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use App\Interfaces\AuthenticationInfo;

class OidcUser implements Authenticatable, AuthenticationInfo
{
    protected $idToken;
    protected $accessToken;
    protected $claims;
    protected $info;
    
    public function __construct($idToken, $accessToken, $claims, $info) 
    {
        $this->idToken = $idToken;
        $this->accessToken = $accessToken;
        $this->claims = $claims;
        $this->info = $info;
    }
    
    public function getAuthIdentifier()
    {
        return $this->claims[$this->getAuthIdentifierName()];    
    }

    public function getRememberToken()
    {
        return $this->idToken;
    }

    public function getAuthPassword()
    {
        return $this->accessToken;
    }

    public function getRememberTokenName()
    {
        return "id_token";
    }

    public function setRememberToken($value)
    {
        $this->idToken = $value;
    }

    public function getAuthIdentifierName()
    {
        return "sub";
    }
    
    public function getAttributes()
    {
        return $this->info;
    }
    
    public function getAuthMethod() 
    {
        return $this->claims['amr'];
    }
    
    public function __get($name)
    {
        return isset($this->info[$name]) ? $this->info[$name] : null; 
    }
}

