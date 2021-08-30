<?php

namespace App\Ldap;

use LdapRecord\Models\ActiveDirectory\User as Model;
use STS\SocialiteAuth\Contracts\SocialiteAuthenticatable;
use LdapRecord\Models\Concerns\CanAuthenticate;

class User extends Model implements SocialiteAuthenticatable
{
    use CanAuthenticate;

    public function getSocialiteIdentifierName()
    {
   	return 'email';
    }
 
    public function getSocialiteIdentifier()
    {
   	return $this->email;
    }

}
