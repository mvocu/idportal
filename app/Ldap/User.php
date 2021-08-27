<?php

namespace App\Ldap;

use LdapRecord\Models\FreeIPA\User as Model;
use STS\SocialiteAuth\Contracts\SocialiteAuthenticatable;
use Illuminate\Auth\Authenticatable;

class User extends Model implements SocialiteAuthenticatable
{
    use Authenticatable;
    
	public function getSocialiteIdentifierName()
	{
    		return 'email';
	}
 
	public function getSocialiteIdentifier()
	{
    		return $this->email;
	}

}
