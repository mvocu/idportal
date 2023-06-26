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
        $this->claims = $claims instanceof \stdClass ? get_object_vars($claims) : $claims;
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
        return get_object_vars($this->info['attributes']);
    }
    
    public function getAuthMethod() 
    {
        return $this->claims['amr'];
    }
    
    public function getDisplayName()
    {
        return empty($this->name) ? $this->sub : $this->name;
    }
    
    public function __get($name)
    {
        $attrs = $this->getAttributes();
        if(isset($attrs[$name])) {
            return $attrs[$name];
        }
        return isset($this->info[$name]) ? $this->info[$name] : null; 
    }
}

