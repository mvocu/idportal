<?php
namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use App\Interfaces\AuthenticationInfo;

class OidcUser implements Authenticatable, AuthenticationInfo, \JsonSerializable
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
        if(!isset($this->info['attributes'])) {
             return [];
        }
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
    
    public function getLevelOfAuthority()
    { 
        return $this->claims['auth_loa'];
    }
    
    public function __get($name)
    {
        $attrs = $this->getAttributes();
        if(isset($attrs[$name])) {
            return $attrs[$name];
        }
        return isset($this->info[$name]) ? $this->info[$name] : null; 
    }

    public function jsonSerialize()
    {
        return $this->claims;
    }

    public function getRemoteClient()
    {
        #$method = $this->getAuthMethod();
        #if(is_array($method) && in_array("DelegatedClientAuthenticationHandler", $method)) {
        $attrs = $this->getAttributes();
        return isset($attrs['auth_delegated_client']) ? $attrs['auth_delegated_client'] : null;
        #}
        #return null;
    }
  
    public function getRemoteAuthenticationMethod()
    {
        $client = $this->getRemoteClient();
        if(empty($client)) {
            return null;
        }
        return $this->claims['auth_amr'];
    }
    
}

