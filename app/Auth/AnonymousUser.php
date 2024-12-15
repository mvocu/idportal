<?php
namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;

class AnonymousUser implements Authenticatable
{

    public function getAuthIdentifier()
    {}

    public function getRememberToken()
    {}

    public function getAuthPassword()
    {}

    public function getRememberTokenName()
    {}

    public function setRememberToken($value)
    {}

    public function getAuthIdentifierName()
    {}
}

