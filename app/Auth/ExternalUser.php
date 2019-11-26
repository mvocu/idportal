<?php
namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use App\Http\Resources\ExtUserResource;

class ExternalUser implements Authenticatable
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
        return $this->claims->{$this->getAuthIdentifierName()};    
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
    
    public function getResource($active = true)
    {
        return new ExtUserResource([ 
            'id' => $this->getAuthIdentifier(), 
            'parent' => null, 
            'active' => $active,
            'attributes' => $this->getAttributes()]);
    }
}

