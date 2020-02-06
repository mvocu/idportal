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
        $data = $this->info;
        if(array_key_exists('address_street_address', $data) &&
            preg_match('/(.*)\s+(\d+)\/(\d+)/', $data['address_street_address'], $matches)) {
            $data['address_street'] = $matches[1];
            $data['address_org_number'] = $matches[2];
            $data['address_ev_number'] = $matches[3];
        }
        return $data;
    }
    
    public function getResource($active = true)
    {
        return new ExtUserResource([ 
            'id' => $this->getAuthIdentifier(), 
            'parent' => null, 
            'active' => $active,
            'trust_level' => (array_key_exists('mojeid_valid', $this->info) &&
                              $this->info['mojeid_valid'] == 1) ?
                                64 : 0,
            'attributes' => $this->getAttributes()]);
    }
    
    public function __get($name)
    {
        return isset($this->info[$name]) ? $this->info[$name] : null; 
    }
}

