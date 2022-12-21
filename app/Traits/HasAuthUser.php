<?php
namespace App\Traits;

use Illuminate\Contracts\Auth\Authenticatable;

trait HasAuthUser
{

    protected $auth_user;
    
    public function getAuthIdentifier()
    {
        return $this->auth_user->getAuthIdentifier();
    }
    
    public function getRememberToken()
    {
        return $this->auth_user->getRememberToken();
    }
    
    public function getAuthPassword()
    {
        return $this->auth_user->getAuthPassword();
    }
    
    public function getAuthMethod()
    {
        return $this->auth_user->getAuthMethod();
    }
    
    public function getRememberTokenName()
    {
        return $this->auth_user->getRememberTokenName();
    }
    
    public function setRememberToken($value)
    {
        return $this->auth_user->setRememberToken($value);
    }
    
    public function getAuthIdentifierName()
    {
        return $this->auth_user->getAuthIdentifierName();
    }

    public function getAuthUser()
    {
        return $this->auth_user;
    }
}

